<?php

namespace App\Http\Controllers;

use App\Models\Supply;
use App\Models\SupplyRequest;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupplyController extends Controller
{
    public function index(Request $request)
    {
        // Search functionality
        $search = $request->get('search');
        $lowStock = $request->get('low_stock');
        $tab = $request->get('tab', 'supplies'); // Default to supplies tab
        
        $supplies = Supply::with(['location', 'inventories.location'])
            ->when($search, function ($query, $search) {
                return $query->where(function($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                      ->orWhereHas('location', function($loc) use ($search) {
                          $loc->where('name', 'LIKE', "%{$search}%");
                      })
                      ->orWhereHas('inventories', function($inv) use ($search) {
                          $inv->whereHas('location', function($loc) use ($search) {
                              $loc->where('name', 'LIKE', "%{$search}%");
                          });
                      });
                });
            })
            ->when($lowStock, function ($query) {
                return $query->where(function($q) {
                    $q->whereColumn('quantity', '<=', 'minimum_stock_level')
                      ->orWhere(function($subQuery) {
                          $subQuery->whereNull('minimum_stock_level')
                                   ->where('quantity', '<=', 10);
                      });
                });
            })
            ->paginate(5);

        // Get all supply requests and pending requests count
        $supplyRequests = SupplyRequest::all();
        $pendingRequests = SupplyRequest::where('status', 'pending')->count();

        // Get manual receipts data for the manual receipts tab
        $manualReceipts = \App\Models\ManualReceipt::with(['supply', 'supplier', 'addedBy'])
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'receipts_page');

        // Get suppliers for the manual receipt form
        $suppliers = \App\Models\Supplier::where('status', 'active')->get();

        // Compute top 5 available supplies for manual receipts selection (active and available)
        $availableSupplies = Supply::active()->orderBy('updated_at', 'desc')->get()
            ->filter(function($s) { return $s->availableQuantity() > 0; })
            ->take(5);

        // Provide a larger searchable pool of available supplies (limit 200)
        $availableSuppliesAll = Supply::active()->orderBy('name')->get()
            ->filter(function($s) { return $s->availableQuantity() > 0; })
            ->take(200);

        // Precompute a JSON-safe array to avoid Blade parsing closures
        $availableSuppliesAllJson = $availableSuppliesAll->map(function($s) {
            return [
                'id' => $s->id,
                'name' => $s->name,
                'available' => (int) $s->availableQuantity(),
                'unit' => (string) ($s->unit ?? ''),
            ];
        })->values();

        return view('supplies.index', compact(
            'supplies',
            'supplyRequests',
            'pendingRequests',
            'search',
            'lowStock',
            'tab',
            'manualReceipts',
            'suppliers',
            'availableSupplies',
            'availableSuppliesAll',
            'availableSuppliesAllJson'
        ));
    }

    public function show(Supply $supply)
    {
        // Eager-load relations needed for borrower status/history and location hierarchy
        $supply->load(['borrowedItems.user', 'borrowedItems.department', 'location.parent']);
        return view('supplies.show', compact('supply'));
    }

    /**
     * Dean context supply details (read-only).
     */
    public function deanShow(Supply $supply)
    {
        // Eager-load relations needed for borrower status/history and location hierarchy
        $supply->load(['borrowedItems.user', 'borrowedItems.department', 'location.parent']);
        // Reuse the same view; admin-only actions are gated by policies/role checks
        return view('supplies.show', compact('supply'));
    }

    /**
     * Student supply details (read-only).
     */
    public function studentShow(Supply $supply)
    {
        $supply->load(['borrowedItems.user', 'borrowedItems.department', 'location.parent']);
        return view('supplies.show', compact('supply'));
    }

    /**
     * Adviser supply details (read-only).
     */
    public function adviserShow(Supply $supply)
    {
        $supply->load(['borrowedItems.user', 'borrowedItems.department', 'location.parent']);
        return view('supplies.show', compact('supply'));
    }

    /**
     * Show the form for creating a new supply.
     */
    public function create()
    {
        // Ensure only admins can create supplies if policies exist
        // $this->authorize('create', Supply::class);

        $categories = Category::orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();
        $locations = Location::orderBy('name')->get();

        return view('supplies.create', compact('categories', 'suppliers', 'locations'));
    }

    /**
     * Store a newly created supply in storage.
     */
    public function store(Request $request)
    {
        // $this->authorize('create', Supply::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:supplies,name',
            'description' => 'nullable|string',
            'tin' => 'nullable|string|max:255',
            'quantity' => 'required|integer|min:0',
            'unit' => 'required|string|max:255',
            'minimum_stock_level' => 'required|integer|min:0',
            'unit_price' => 'nullable|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'supply_type' => 'required|in:consumable,borrowable,grantable',
            'supplier_ids' => 'array',
            'supplier_ids.*' => 'exists:suppliers,id',
            'location_id' => 'required|exists:locations,id',
        ]);

        $supply = Supply::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'tin' => $validated['tin'] ?? null,
            'quantity' => $validated['quantity'],
            'unit' => $validated['unit'],
            'minimum_stock_level' => $validated['minimum_stock_level'],
            'unit_price' => $validated['unit_price'] ?? null,
            'status' => 'active',
            'supply_type' => $validated['supply_type'],
            'has_variants' => false,
            'location_id' => $validated['location_id'],
        ]);

        // Attach category
        $supply->categories()->sync([$validated['category_id']]);

        // Attach suppliers if any
        $supplierIds = $request->input('supplier_ids', []);
        $supply->suppliers()->sync($supplierIds);

        return redirect()->route('supplies.index')->with('success', 'Supply created successfully.');
    }

    /**
     * AJAX: Live search supplies across all pages by name and location.
     */
    public function ajaxSearch(Request $request)
    {
        $q = trim((string) $request->get('q', ''));
        $lowStock = $request->boolean('low_stock', false);

        $query = Supply::with(['location', 'inventories.location'])
            ->when($q !== '', function($builder) use ($q) {
                $builder->where(function($qbuilder) use ($q) {
                    $qbuilder->where('name', 'LIKE', "%{$q}%")
                        ->orWhereHas('location', function($loc) use ($q) {
                            $loc->where('name', 'LIKE', "%{$q}%");
                        })
                        ->orWhereHas('inventories', function($inv) use ($q) {
                            $inv->whereHas('location', function($loc) use ($q) {
                                $loc->where('name', 'LIKE', "%{$q}%");
                            });
                        });
                });
            })
            ->when($lowStock, function($builder) {
                $builder->where(function($q) {
                    $q->whereColumn('quantity', '<=', 'minimum_stock_level')
                      ->orWhere(function($subQuery) {
                          $subQuery->whereNull('minimum_stock_level')
                                   ->where('quantity', '<=', 10);
                      });
                });
            })
            ->orderBy('name');

        $supplies = $query->get();

        $user = Auth::user();
        $data = $supplies->map(function($supply) use ($user) {
            $locNames = [];
            if ($supply->location && $supply->location->name) { $locNames[] = $supply->location->name; }
            foreach ($supply->inventories as $inv) {
                if ($inv->location && $inv->location->name) { $locNames[] = $inv->location->name; }
            }
            $lowBadge = false; $warnBadge = false;
            if (!$supply->isBorrowable()) {
                if ($supply->quantity <= $supply->minimum_stock_level) { $lowBadge = true; }
                elseif ($supply->quantity <= ($supply->minimum_stock_level * 1.5)) { $warnBadge = true; }
            }
            $url = route('supplies.show', $supply);
            if ($user) {
                if ($user->hasRole('student')) { $url = route('student.supplies.show', $supply); }
                elseif ($user->hasRole('adviser')) { $url = route('adviser.supplies.show', $supply); }
                elseif ($user->hasRole('dean')) { $url = route('dean.supplies.show', $supply); }
            }
            return [
                'id' => $supply->id,
                'name' => $supply->name,
                'description' => $supply->description ?? 'No description',
                'supply_type' => (string) $supply->supply_type,
                'supply_type_label' => (string) $supply->getSupplyTypeLabel(),
                'quantity' => (int) $supply->quantity,
                'unit' => (string) $supply->unit,
                'status' => (string) $supply->status,
                'url' => $url,
                'locations' => array_values(array_unique($locNames)),
                'low_badge' => $lowBadge,
                'warn_badge' => $warnBadge,
            ];
        })->values();

        return response()->json(['results' => $data]);
    }

    /**
     * Check if a supply name already exists (AJAX).
     */
    public function checkName(Request $request)
    {
        $name = trim((string) $request->get('name', ''));
        $exists = $name !== '' ? Supply::where('name', $name)->exists() : false;
        return response()->json(['exists' => $exists]);
    }

    /**
     * Show the form for editing the specified supply.
     */
    public function edit(Supply $supply)
    {
        // $this->authorize('update', $supply);
        $categories = Category::orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();
        return view('supplies.edit', compact('supply', 'categories', 'suppliers'));
    }

    /**
     * Update the specified supply in storage.
     */
    public function update(Request $request, Supply $supply)
    {
        // $this->authorize('update', $supply);
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:supplies,name,' . $supply->id,
            'description' => 'nullable|string',
            'tin' => 'nullable|string|max:255',
            'quantity' => 'nullable|integer|min:0',
            'unit' => 'required|string|max:255',
            'minimum_stock_level' => 'required|integer|min:0',
            'unit_price' => 'nullable|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'supply_type' => 'required|in:consumable,borrowable,grantable',
            'supplier_ids' => 'array',
            'supplier_ids.*' => 'exists:suppliers,id',
        ]);

        $supply->name = $validated['name'];
        $supply->description = $validated['description'] ?? null;
        $supply->tin = $validated['tin'] ?? null;
        if (!$supply->hasVariants() && array_key_exists('quantity', $validated)) {
            $supply->quantity = $validated['quantity'];
        }
        $supply->unit = $validated['unit'];
        $supply->minimum_stock_level = $validated['minimum_stock_level'];
        $supply->unit_price = $validated['unit_price'] ?? $supply->unit_price;
        $supply->supply_type = $validated['supply_type'];
        $supply->save();

        // Sync relationships
        $supply->categories()->sync([$validated['category_id']]);
        $supplierIds = $request->input('supplier_ids', []);
        $supply->suppliers()->sync($supplierIds);

        return redirect()->route('supplies.show', $supply)->with('success', 'Supply updated successfully.');
    }

    /**
     * Enable variants for a supply and redirect appropriately.
     */
    public function enableVariants(Supply $supply)
    {
        // $this->authorize('update', $supply);
        $supply->enableVariants();

        // If no variants exist yet, guide the user to create the first one
        if ($supply->variants()->count() === 0) {
            return redirect()->route('supply-variants.create', $supply)
                ->with('success', 'Variants enabled. Create the first variant.');
        }

        return redirect()->route('supplies.show', $supply)
            ->with('success', 'Variants enabled for supply.');
    }

    public function markAsActive(Supply $supply)
    {
        $supply->markAsActive();
        return redirect()->route('supplies.show', $supply)
            ->with('success', 'Supply marked as active.');
    }

    public function markAsInactive(Supply $supply)
    {
        $supply->markAsInactive();
        return redirect()->route('supplies.show', $supply)
            ->with('success', 'Supply marked as inactive.');
    }

    public function markAsDamaged(Request $request, Supply $supply)
    {
        $validated = $request->validate([
            'severity' => 'required|in:minor,moderate,severe',
        ]);
        $supply->markAsDamaged($validated['severity']);
        return redirect()->route('supplies.show', $supply)
            ->with('success', 'Supply marked as damaged.');
    }

    public function assignLocation(Request $request, Supply $supply)
    {
        $validated = $request->validate([
            'location_id' => 'required|exists:locations,id',
        ]);
        $supply->update(['location_id' => $validated['location_id']]);
        return redirect()->route('supplies.show', $supply)
            ->with('success', 'Location assigned successfully.');
    }
}
