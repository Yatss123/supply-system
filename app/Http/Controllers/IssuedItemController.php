<?php

namespace App\Http\Controllers;

use App\Models\IssuedItem;
use App\Models\IssuedItemBatch;
use App\Models\Supply;
use App\Models\SupplyVariant;
use App\Models\Department;
use App\Models\User;
use App\Models\Category;
use App\Models\RestockRequest;
use App\Models\DepartmentCart;
use App\Models\DepartmentCartItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class IssuedItemController extends Controller
{
    /**
     * Display a listing of issued items with filtering and search
     */
    public function index(Request $request)
    {
        $query = IssuedItem::with(['supply', 'supplyVariant', 'department', 'issuedBy', 'user']);
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('supply', function($subQ) use ($search) {
                    $subQ->where('name', 'like', "%{$search}%")
                         ->orWhere('description', 'like', "%{$search}%");
                })
                ->orWhereHas('department', function($subQ) use ($search) {
                    $subQ->where('department_name', 'like', "%{$search}%");
                })
                ->orWhereHas('issuedBy', function($subQ) use ($search) {
                    $subQ->where('name', 'like', "%{$search}%");
                })
                ->orWhere('notes', 'like', "%{$search}%");
            });
        }
        
        // Filter by department
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }
        
        // Filter by category
        if ($request->filled('category_id')) {
            $query->whereHas('supply.categories', function($q) use ($request) {
                $q->where('categories.id', $request->category_id);
            });
        }
        
        // Filter by supply type
        if ($request->filled('supply_type')) {
            $query->whereHas('supply', function($q) use ($request) {
                $q->where('supply_type', $request->supply_type);
            });
        }
        
        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('issued_on', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('issued_on', '<=', $request->date_to);
        }
        
        // Sort by most recent first
        $query->orderBy('issued_on', 'desc')->orderBy('created_at', 'desc');
        
        $issuedItems = $query->paginate(15)->withQueryString();
        
        // Get filter options
        $departments = Department::active()->orderBy('department_name')->get();
        $categories = Category::orderBy('name')->get();
        
        // Calculate statistics
        $stats = [
            'total_issued' => IssuedItem::count(),
            'issued_today' => IssuedItem::whereDate('issued_on', today())->count(),
            'issued_this_month' => IssuedItem::whereMonth('issued_on', now()->month)
                                           ->whereYear('issued_on', now()->year)->count(),
            'total_value' => IssuedItem::with('supply')->get()->sum('total_value')
        ];
        
        return view('issued_items.index', compact('issuedItems', 'departments', 'categories', 'stats'));
    }

    /**
     * Show the form for creating a new issued item
     */
    public function create()
    {
        // Get supplies that can be issued (consumable and grantable with stock > 0)
        $supplies = Supply::where(function($query) {
            $query->where('supply_type', 'consumable')
                  ->orWhere('supply_type', 'grantable');
        })
        ->where('status', 'active')
        ->where('quantity', '>', 0)
        ->with('categories')
        ->orderBy('name')
        ->get();
        
        $departments = Department::orderBy('department_name')->get();
        
        // Get users with specific roles: student, dean, and adviser
        $users = User::whereHas('role', function($query) {
            $query->whereIn('name', ['student', 'dean', 'adviser']);
        })
        ->with(['role', 'department'])
        ->orderBy('name')
        ->get();
        
        // Get all categories for the category dropdown
        $categories = Category::orderBy('name')->get();
        
        return view('issued_items.create', compact('supplies', 'departments', 'users', 'categories'));
    }

    /**
     * Store a newly created issued item
     */
    public function store(Request $request)
    {
        if ($request->has('items')) {
            $request->validate([
                'items' => 'required|array|min:1',
                'items.*.supply_id' => 'required|exists:supplies,id',
                'items.*.supply_variant_id' => 'nullable|exists:supply_variants,id',
                'items.*.quantity' => 'required|integer|min:1|max:999999',
                'department_id' => 'required|exists:departments,id',
                'user_id' => 'nullable|exists:users,id',
                'issued_on' => 'required|date|before_or_equal:today',
                'notes' => 'nullable|string|max:1000',
            ], [
                'items.required' => 'Please add at least one item to issue.',
                'department_id.required' => 'Please select a department.',
                'issued_on.before_or_equal' => 'Issue date cannot be in the future.',
            ]);
        } else {
            $request->validate([
                'supply_id' => 'required|exists:supplies,id',
                'supply_variant_id' => 'nullable|exists:supply_variants,id',
                'department_id' => 'required|exists:departments,id',
                'user_id' => 'nullable|exists:users,id',
                'quantity' => 'required|integer|min:1|max:999999',
                'issued_on' => 'required|date|before_or_equal:today',
                'notes' => 'nullable|string|max:1000',
            ], [
                'supply_id.required' => 'Please select a supply item to issue.',
                'department_id.required' => 'Please select a department.',
                'quantity.required' => 'Please enter the quantity to issue.',
                'quantity.min' => 'Quantity must be at least 1.',
                'quantity.max' => 'Quantity cannot exceed 999,999.',
                'issued_on.before_or_equal' => 'Issue date cannot be in the future.',
                'notes.max' => 'Notes cannot exceed 1000 characters.',
            ]);
        }

        // Enforce that selected recipient belongs to the chosen department
        if ($request->filled('user_id')) {
            $recipient = User::find($request->user_id);
            if (!$recipient) {
                return redirect()->back()
                    ->withErrors(['user_id' => 'Selected recipient does not exist.'])
                    ->withInput();
            }
            if ((string) $recipient->department_id !== (string) $request->department_id) {
                return redirect()->back()
                    ->withErrors(['user_id' => 'Selected recipient does not belong to the chosen department.'])
                    ->withInput();
            }
        }

        // If multiple items provided, process batch issuance and return
        if ($request->has('items')) {
            try {
                DB::beginTransaction();

                $batch = IssuedItemBatch::create([
                    'department_id' => $request->department_id,
                    'user_id' => $request->user_id,
                    'issued_by' => Auth::id(),
                    'issued_on' => $request->issued_on,
                    'notes' => $request->notes,
                ]);

                $createdItems = [];
                foreach ($request->items as $item) {
                    $supply = Supply::lockForUpdate()->find($item['supply_id']);
                    if (!$supply) {
                        throw new \Exception('Supply item not found.');
                    }
                    if ($supply->status !== 'active') {
                        throw new \Exception('One of the selected supplies is inactive and cannot be issued.');
                    }
                    if (!in_array($supply->supply_type, ['consumable', 'grantable'])) {
                        throw new \Exception('Only consumable and grantable items can be issued.');
                    }

                    $variant = null;
                    $stockSource = $supply;
                    if (!empty($item['supply_variant_id'])) {
                        $variant = $supply->variants()->lockForUpdate()->find($item['supply_variant_id']);
                        if (!$variant) {
                            throw new \Exception('Selected variant is invalid.');
                        }
                        if ($variant->status !== 'active') {
                            throw new \Exception('Selected variant is disabled and cannot be issued.');
                        }
                        $stockSource = $variant;
                    }

                    $qty = (int) $item['quantity'];
                    if ($stockSource->quantity < $qty) {
                        $itemName = $variant ? "{$supply->name} - {$variant->name}" : $supply->name;
                        $unit = $stockSource->unit ?? $supply->unit;
                        throw new \Exception("Insufficient stock for {$itemName}. Available: {$stockSource->quantity} {$unit}");
                    }

                    $stockSource->quantity -= $qty;
                    $stockSource->save();

                    $issuedItem = IssuedItem::create([
                        'batch_id' => $batch->id,
                        'supply_id' => $supply->id,
                        'supply_variant_id' => $variant ? $variant->id : null,
                        'department_id' => $request->department_id,
                        'user_id' => $request->user_id,
                        'quantity' => $qty,
                        'issued_on' => $request->issued_on,
                        'notes' => $request->notes,
                        'issued_by' => Auth::id(),
                    ]);

                    $this->checkAndCreateRestockRequest($supply, $variant, $stockSource, $qty, $issuedItem);
                    $createdItems[] = $issuedItem;
                }

                DB::commit();

                $count = count($createdItems);
                return redirect()->route('issued-items.show', $createdItems[0])
                    ->with('success', "Successfully issued {$count} item(s) in a single transaction.");
            } catch (\Exception $e) {
                DB::rollBack();
                return redirect()->back()
                    ->withErrors(['error' => 'An error occurred while issuing items: ' . $e->getMessage()])
                    ->withInput();
            }
        }

        try {
            DB::beginTransaction();
            
            // Get and lock the supply for update
            $supply = Supply::lockForUpdate()->find($request->supply_id);
            
            if (!$supply) {
                throw new \Exception('Supply item not found.');
            }
            
            // Validate supply can be issued
            if ($supply->status !== 'active') {
                return redirect()->back()
                    ->withErrors(['supply_id' => 'This supply item is not active and cannot be issued.'])
                    ->withInput();
            }
            
            if (!in_array($supply->supply_type, ['consumable', 'grantable'])) {
                return redirect()->back()
                    ->withErrors(['supply_id' => 'Only consumable and grantable items can be issued.'])
                    ->withInput();
            }
            
            // Handle variant selection and stock validation
            $variant = null;
            $stockSource = $supply;
            
            if ($request->filled('supply_variant_id')) {
                $variant = $supply->variants()->lockForUpdate()->find($request->supply_variant_id);
                
                if (!$variant) {
                    return redirect()->back()
                        ->withErrors(['supply_variant_id' => 'Selected variant is invalid.'])
                        ->withInput();
                }
                if ($variant->status !== 'active') {
                    return redirect()->back()
                        ->withErrors(['supply_variant_id' => 'Selected variant is disabled and cannot be issued.'])
                        ->withInput();
                }
                
                $stockSource = $variant;
            }
            
            // Check stock availability
            if ($stockSource->quantity < $request->quantity) {
                $itemName = $variant ? "{$supply->name} - {$variant->name}" : $supply->name;
                $unit = $stockSource->unit ?? $supply->unit;
                return redirect()->back()
                    ->withErrors(['quantity' => "Insufficient stock for {$itemName}. Available: {$stockSource->quantity} {$unit}"])
                    ->withInput();
            }
            
            // Update stock
            $stockSource->quantity -= $request->quantity;
            $stockSource->save();
            
            // Create batch for this issuance (single-item requests also get a unique Issue ID)
            $batch = IssuedItemBatch::create([
                'department_id' => $request->department_id,
                'user_id' => $request->user_id,
                'issued_by' => Auth::id(),
                'issued_on' => $request->issued_on,
                'notes' => $request->notes,
            ]);

            // Create issued item
            $issuedItemData = $request->only([
                'supply_id', 'supply_variant_id', 'department_id', 
                'user_id', 'quantity', 'issued_on', 'notes'
            ]);
            $issuedItemData['issued_by'] = Auth::id();
            $issuedItemData['batch_id'] = $batch->id;
            
            $issuedItem = IssuedItem::create($issuedItemData);
            
            // Check for low stock and create restock request if needed
            $this->checkAndCreateRestockRequest($supply, $variant, $stockSource, $request->quantity, $issuedItem);
            
            DB::commit();
            
            $itemName = $variant ? "{$supply->name} - {$variant->name}" : $supply->name;
            $unit = $stockSource->unit ?? $supply->unit;
            
            return redirect()->route('issued-items.index')
                ->with('success', "Successfully issued {$request->quantity} {$unit} of '{$itemName}' to {$issuedItem->department->department_name}.");
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->withErrors(['error' => 'An error occurred while issuing the item: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Display the specified issued item
     */
    public function show(IssuedItem $issuedItem)
    {
        $issuedItem->load([
            'supply', 'supplyVariant', 'department', 'issuedBy',
            'batch.items.supply', 'batch.items.supplyVariant'
        ]);
        return view('issued_items.show', compact('issuedItem'));
    }

    /**
     * Show the form for editing the specified issued item
     */
    public function edit(IssuedItem $issuedItem)
    {
        $issuedItem->load(['supply', 'supplyVariant', 'department']);
        
        // Get supplies that can be issued
        $supplies = Supply::where(function($query) {
            $query->where('supply_type', 'consumable')
                  ->orWhere('supply_type', 'grantable');
        })
        ->where('status', 'active')
        ->with('categories')
        ->orderBy('name')
        ->get();
        
        $departments = Department::orderBy('department_name')->get();
        
        // Get users with specific roles: student, dean, and adviser
        $users = User::whereHas('role', function($query) {
            $query->whereIn('name', ['student', 'dean', 'adviser']);
        })
        ->with(['role', 'department'])
        ->orderBy('name')
        ->get();
        
        // Get all categories for the category dropdown
        $categories = Category::orderBy('name')->get();
        
        // Get variants for the current supply
        $variants = $issuedItem->supply->variants()->where('status', 'active')->get() ?? collect();
        
        return view('issued_items.edit', compact('issuedItem', 'supplies', 'departments', 'users', 'categories', 'variants'));
    }

    /**
     * Update the specified issued item
     */
    public function update(Request $request, IssuedItem $issuedItem)
    {
        $request->validate([
            'supply_id' => 'required|exists:supplies,id',
            'supply_variant_id' => 'nullable|exists:supply_variants,id',
            'department_id' => 'required|exists:departments,id',
            'user_id' => 'nullable|exists:users,id',
            'quantity' => 'required|integer|min:1|max:999999',
            'issued_on' => 'required|date|before_or_equal:today',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();
            
            // Store original values for stock adjustment
            $originalSupply = $issuedItem->supply;
            $originalVariant = $issuedItem->supplyVariant;
            $originalQuantity = $issuedItem->quantity;
            
            // Get new supply and variant
            $newSupply = Supply::lockForUpdate()->find($request->supply_id);
            $newVariant = null;
            
            if ($request->filled('supply_variant_id')) {
                $newVariant = SupplyVariant::lockForUpdate()->find($request->supply_variant_id);
                if ($newVariant && $newVariant->status !== 'active') {
                    throw new \Exception('Selected variant is disabled and cannot be issued.');
                }
            }
            
            // Return stock to original source
            if ($originalVariant) {
                $originalVariant->quantity += $originalQuantity;
                $originalVariant->save();
            } else {
                $originalSupply->quantity += $originalQuantity;
                $originalSupply->save();
            }
            
            // Validate new supply
            if ($newSupply->status !== 'active') {
                throw new \Exception('Selected supply is not active.');
            }
            
            if (!in_array($newSupply->supply_type, ['consumable', 'grantable'])) {
                throw new \Exception('Only consumable and grantable items can be issued.');
            }
            
            // Determine new stock source
            $newStockSource = $newVariant ?? $newSupply;
            
            // Check new stock availability
            if ($newStockSource->quantity < $request->quantity) {
                throw new \Exception('Insufficient stock for the updated quantity.');
            }
            
            // Deduct from new stock source
            $newStockSource->quantity -= $request->quantity;
            $newStockSource->save();
            
            // Update the issued item
            $issuedItem->update([
                'supply_id' => $request->supply_id,
                'supply_variant_id' => $request->supply_variant_id,
                'department_id' => $request->department_id,
                'user_id' => $request->user_id,
                'quantity' => $request->quantity,
                'issued_on' => $request->issued_on,
                'notes' => $request->notes,
            ]);
            
            DB::commit();
            
            return redirect()->route('issued-items.show', $issuedItem)
                ->with('success', 'Issued item updated successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->withErrors(['error' => 'An error occurred while updating: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Remove the specified issued item and return stock
     */
    public function destroy(IssuedItem $issuedItem)
    {
        try {
            DB::beginTransaction();
            
            $issuedItem->load(['supply', 'supplyVariant', 'department']);
            
            // Store info for success message
            $supplyName = $issuedItem->supply->name;
            $variantName = $issuedItem->supplyVariant ? $issuedItem->supplyVariant->name : null;
            $quantity = $issuedItem->quantity;
            $unit = $issuedItem->supplyVariant->unit ?? $issuedItem->supply->unit;
            $departmentName = $issuedItem->department->department_name;
            
            // Return stock to appropriate source
            if ($issuedItem->supplyVariant) {
                $variant = SupplyVariant::lockForUpdate()->find($issuedItem->supply_variant_id);
                if ($variant) {
                    $variant->quantity += $quantity;
                    $variant->save();
                }
            } else {
                $supply = Supply::lockForUpdate()->find($issuedItem->supply_id);
                if ($supply) {
                    $supply->quantity += $quantity;
                    $supply->save();
                }
            }
            
            $issuedItem->delete();
            
            DB::commit();
            
            $itemName = $variantName ? "{$supplyName} - {$variantName}" : $supplyName;
            
            return redirect()->route('issued-items.index')
                ->with('success', "Successfully deleted issuance record and returned {$quantity} {$unit} of '{$itemName}' to stock.");
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->route('issued-items.index')
                ->withErrors(['error' => 'An error occurred while deleting: ' . $e->getMessage()]);
        }
    }

    /**
     * Get supply information for AJAX requests
     */
    public function getSupplyInfo(Supply $supply)
    {
        $supply = Supply::with(['variants' => function($q) { $q->where('status', 'active'); }])->find($supply->id);
        
        if (!$supply) {
            return response()->json(['error' => 'Supply not found'], 404);
        }
        
        return response()->json([
            'success' => true,
            'supply' => [
                'id' => $supply->id,
                'name' => $supply->name,
                'quantity' => $supply->quantity,
                'current_stock' => $supply->quantity, // For backward compatibility
                'unit' => $supply->unit,
                'supply_type' => $supply->supply_type,
                'minimum_stock_level' => $supply->minimum_stock_level,
                'description' => $supply->description,
                'price' => $supply->unit_price ?? 0,
                'unit_price' => $supply->unit_price ?? 0,
                'can_issue' => in_array($supply->supply_type, ['consumable', 'grantable']),
                'is_active' => $supply->status === 'active',
                'has_variants' => $supply->variants->isNotEmpty(),
            ],
            'variants' => $supply->variants->map(function($variant) {
                return [
                    'id' => $variant->id,
                    'name' => $variant->name,
                    'quantity' => $variant->quantity,
                    'current_stock' => $variant->quantity, // For backward compatibility
                    'unit' => $variant->unit,
                    'price' => $variant->price ?? 0,
                    'description' => $variant->description,
                ];
            })
        ]);
    }

    /**
     * Get variants for a specific supply
     */
    public function getSupplyVariants(Supply $supply)
    {
        $supply = Supply::with(['variants' => function($q) { $q->where('status', 'active'); }])->find($supply->id);
        
        if (!$supply) {
            return response()->json(['error' => 'Supply not found'], 404);
        }
        
        return response()->json([
            'success' => true,
            'variants' => $supply->variants->map(function($variant) use ($supply) {
                return [
                    'id' => $variant->id,
                    'name' => $variant->variant_name,
                    'quantity' => $variant->quantity,
                    'current_stock' => $variant->quantity, // For JavaScript compatibility
                    'unit' => $supply->unit, // Use supply's unit
                    'price' => $variant->price ?? 0,
                    'description' => $variant->description ?? '',
                ];
            })
        ]);
    }

    /**
     * Export issued items to CSV
     */
    public function export(Request $request)
    {
        $query = IssuedItem::with(['supply', 'supplyVariant', 'department', 'issuedBy']);
        
        // Apply same filters as index
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('supply', function($subQ) use ($search) {
                    $subQ->where('name', 'like', "%{$search}%");
                })
                ->orWhereHas('department', function($subQ) use ($search) {
                    $subQ->where('department_name', 'like', "%{$search}%");
                });
            });
        }
        
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }
        
        if ($request->filled('date_from')) {
            $query->whereDate('issued_on', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('issued_on', '<=', $request->date_to);
        }
        
        $issuedItems = $query->orderBy('issued_on', 'desc')->get();
        
        $filename = 'issued_items_' . now()->format('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];
        
        $callback = function() use ($issuedItems) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, [
                'Issue Date',
                'Supply Item',
                'Variant',
                'Quantity',
                'Unit',
                'Department',
                'Recipient',
                'Supply Type',
                'Unit Price',
                'Total Value',
                'Issued By',
                'Notes',
                'Created At'
            ]);
            
            foreach ($issuedItems as $item) {
                fputcsv($file, [
                    $item->issued_on->format('Y-m-d'),
                    $item->supply->name ?? 'N/A',
                    $item->supplyVariant->name ?? 'N/A',
                    $item->quantity,
                    $item->supplyVariant->unit ?? $item->supply->unit ?? 'N/A',
                    $item->department->department_name ?? 'N/A',
                    $item->user->name ?? 'N/A',
                    ucfirst($item->supply->supply_type ?? 'N/A'),
                    $item->supply->unit_price ?? 0,
                    $item->total_value,
                    $item->issuedBy->name ?? 'N/A',
                    $item->notes ?? '',
                    $item->created_at->format('Y-m-d H:i:s')
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    /**
     * Check stock levels and create restock request if needed
     */
    private function checkAndCreateRestockRequest($supply, $variant, $stockSource, $issuedQuantity, $issuedItem)
    {
        // Borrowable supplies should not trigger restock requests or low stock alerts
        if ($supply && method_exists($supply, 'isBorrowable') && $supply->isBorrowable()) {
            return;
        }

        $minimumLevel = $variant ? ($variant->minimum_stock_level ?? 0) : $supply->minimum_stock_level;
        $currentStock = $stockSource->quantity;
        $itemName = $variant ? "{$supply->name} - {$variant->name}" : $supply->name;
        
        if ($minimumLevel > 0 && $currentStock <= $minimumLevel) {
            // Calculate restock quantity
            $restockQuantity = max(
                $minimumLevel * 2,
                $minimumLevel + $issuedQuantity
            );

            $unit = $stockSource->unit ?? $supply->unit;
            $reason = $variant 
                ? "Auto-generated: Variant '{$variant->name}' stock below minimum level after issuing {$issuedQuantity} {$unit} to {$issuedItem->department->department_name}"
                : "Auto-generated: Stock below minimum level after issuing {$issuedQuantity} {$unit} to {$issuedItem->department->department_name}";

            // Route restocking to department cart
            $departmentId = $issuedItem->department_id;
            if ($departmentId) {
                $cart = DepartmentCart::forDepartment((int) $departmentId);
                // Prevent duplicate pending cart item for the same supply
                $existingCartItem = DepartmentCartItem::where('cart_id', $cart->id)
                    ->where('supply_id', $supply->id)
                    ->where('status', 'pending')
                    ->first();

                if (!$existingCartItem) {
                    $itemType = in_array($supply->supply_type, [Supply::TYPE_CONSUMABLE, Supply::TYPE_GRANTABLE])
                        ? $supply->supply_type
                        : DepartmentCartItem::TYPE_CONSUMABLE;

                    DepartmentCartItem::create([
                        'cart_id' => $cart->id,
                        'supply_request_id' => null,
                        'supply_id' => $supply->id,
                        'item_name' => $itemName,
                        'unit' => $unit,
                        'quantity' => (int) $restockQuantity,
                        'item_type' => $itemType,
                        'attributes' => [
                            'reason' => $reason,
                            'source' => 'auto_restock_issuance',
                            'issued_item_id' => $issuedItem->id,
                            'supply_variant_id' => $variant->id ?? null,
                            'requested_by' => Auth::id(),
                        ],
                        'status' => 'pending',
                    ]);

                    session()->flash('warning', "Stock level for '{$itemName}' is now below the minimum level ({$minimumLevel} {$unit}). Added a restock item to the department cart.");
                } else {
                    session()->flash('info', "Stock level for '{$itemName}' is below minimum level. A pending cart item already exists for restocking.");
                }
            } else {
                // Fallback: notify without creating direct restock requests
                session()->flash('warning', "Stock level for '{$itemName}' is now below the minimum level ({$minimumLevel} {$unit}). Please add a restock item via the appropriate department cart.");
            }
        }
    }
}
