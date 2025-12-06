<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Supply;

class UnitsController extends Controller
{
    /**
     * Display a listing of distinct units used in supplies.
     */
    public function index(Request $request)
    {
        $units = Supply::selectRaw('unit, COUNT(*) as item_count')
            ->whereNotNull('unit')
            ->where('unit', '!=', '')
            ->groupBy('unit')
            ->orderBy('unit')
            ->get();

        return view('units.index', compact('units'));
    }

    /**
     * Display supplies associated with a specific unit.
     */
    public function show(string $unit)
    {
        // Fetch all supplies matching the unit, eager loading categories and suppliers for display
        $supplies = Supply::with(['categories', 'suppliers'])
            ->where('unit', $unit)
            ->orderBy('name')
            ->get();

        return view('units.show', compact('unit', 'supplies'));
    }
}