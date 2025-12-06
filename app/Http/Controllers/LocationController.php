<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\Supply;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function index()
    {
        $locations = Location::whereNull('parent_id')->orderBy('name')->paginate(15);
        return view('locations.index', compact('locations'));
    }

    public function create()
    {
        $parents = Location::orderBy('name')->get(['id','name']);
        return view('locations.create', compact('parents'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:locations,name',
            'description' => 'nullable|string|max:1000',
            'parent_id' => 'nullable|exists:locations,id',
        ]);

        Location::create($validated);

        return redirect()
            ->route('locations.create')
            ->with('success', 'Location added successfully.');
    }

    public function createChild(Location $location)
    {
        $parents = Location::orderBy('name')->get(['id','name']);
        $preselectedParent = $location->id;
        return view('locations.create', compact('parents', 'preselectedParent'));
    }

    public function show(Location $location)
    {
        $location->load(['supplies', 'inventories.supply', 'parent', 'children']);

        $descendantIds = [];
        $levelIds = $location->children->pluck('id')->all();
        while (!empty($levelIds)) {
            $descendantIds = array_merge($descendantIds, $levelIds);
            $levelIds = Location::whereIn('parent_id', $levelIds)->pluck('id')->all();
        }

        $childSupplies = collect();
        if (!empty($descendantIds)) {
            $childSupplies = Supply::whereIn('location_id', $descendantIds)->orderBy('name')->get();
        }

        $suppliesAtAndBelow = $location->supplies->concat($childSupplies)->sortBy('name')->values();

        return view('locations.show', compact('location', 'suppliesAtAndBelow'));
    }

    public function search(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $parentsOnly = $request->boolean('parents_only', false);
        if ($q === '') {
            return response()->json(['results' => []]);
        }

        $results = Location::query()
            ->where(function ($query) use ($q) {
                $query->where('name', 'like', "%$q%")
                      ->orWhere('description', 'like', "%$q%");
            })
            ->when($parentsOnly, function($query){
                $query->whereNull('parent_id');
            })
            ->orderBy('name')
            ->limit(50)
            ->get(['id', 'name', 'description']);

        return response()->json(['results' => $results]);
    }

    public function parents()
    {
        $results = Location::whereNull('parent_id')->orderBy('name')->get(['id','name','description']);
        return response()->json(['results' => $results]);
    }

    public function children(Location $location)
    {
        $results = $location->children()->orderBy('name')->get(['id','name','description']);
        return response()->json(['results' => $results]);
    }
}
