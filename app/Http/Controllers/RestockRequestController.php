<?php

namespace App\Http\Controllers;

use App\Models\RestockRequest;
use App\Models\Department;
use App\Models\Supply;
use App\Models\Supplier;
use App\Notifications\RestockRequestNotification;
use App\Notifications\SupplierOrderNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Auth;

class RestockRequestController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        
        // Restrict ordering and delivery actions to admin users only
        $this->middleware(function ($request, $next) {
            if (in_array($request->route()->getActionMethod(), ['order', 'markAsDelivered'])) {
                if (!Auth::user()) {
                    abort(403, 'Unauthorized. Authentication required.');
                }
                
                $user = Auth::user();
                
                // Load role relationship to prevent null reference errors
                if (!$user->relationLoaded('role')) {
                    $user->load('role');
                }
                
                if (!$user->hasAdminPrivileges()) {
                    abort(403, 'Unauthorized. Admin access required.');
                }
            }
            return $next($request);
        });
    }
    public function index(Request $request)
    {
        // Authorize viewing restock requests per policy
        $this->authorize('viewAny', RestockRequest::class);
        $query = RestockRequest::with(['supply', 'supplier', 'requestedDepartment']);
        
        // Handle search functionality
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->whereHas('supply', function($subQuery) use ($searchTerm) {
                    $subQuery->where('name', 'LIKE', "%{$searchTerm}%")
                             ->orWhere('description', 'LIKE', "%{$searchTerm}%");
                })
                ->orWhereHas('supplier', function($subQuery) use ($searchTerm) {
                    $subQuery->where('name', 'LIKE', "%{$searchTerm}%")
                             ->orWhere('email', 'LIKE', "%{$searchTerm}%");
                })
                ->orWhere('status', 'LIKE', "%{$searchTerm}%")
                ->orWhere('id', 'LIKE', "%{$searchTerm}%");
            });
        }
        
        // Get all requests for search results
        $restockRequests = $query->orderBy('created_at', 'desc')->get();
        
        // Get newest requests by status (limit to 5 each for better performance)
        $newestPending = RestockRequest::with(['supply', 'supplier', 'requestedDepartment'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
            
        $newestOrdered = RestockRequest::with(['supply', 'supplier', 'requestedDepartment'])
            ->where('status', 'ordered')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
            
        $newestDelivered = RestockRequest::with(['supply', 'supplier', 'requestedDepartment'])
            ->where('status', 'delivered')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        // Departments for filter and cart navigation
        $departments = Department::active()->orderBy('department_name')->get();

        return view('restock_requests.index', compact(
            'restockRequests', 
            'newestPending', 
            'newestOrdered', 
            'newestDelivered',
            'departments'
        ));
    }

    public function create()
    {
        // Direct creation disabled in favor of cart-driven workflow
        $this->authorize('create', RestockRequest::class);
        return redirect()->route('restock-requests.index')
            ->withErrors(['error' => 'Direct restocking is disabled. Please add items via the Department Cart and finalize the order.']);
    }

    public function toOrderIndex(Request $request)
    {
        // List all supplies that should be ordered (low stock)
        $this->authorize('viewAny', RestockRequest::class);
    
        $search = trim($request->get('search', ''));
        $filter = $request->get('filter', 'not_ordered');
        if (!in_array($filter, ['not_ordered','ordered','all'], true)) {
            $filter = 'not_ordered';
        }
    
        // Compute ordered supplies and maps
        $orderedRequests = RestockRequest::with('requestedDepartment')
            ->where('status', 'ordered')
            ->orderBy('created_at', 'desc')
            ->get(['id','supply_id','items_json','requested_department_id']);
    
        $orderedSupplyIds = [];
        $supplyToRequestId = [];
        $orderedByMap = [];
    
        foreach ($orderedRequests as $rr) {
            if (!empty($rr->supply_id)) {
                $sid = (int) $rr->supply_id;
                $orderedSupplyIds[] = $sid;
                if (!isset($supplyToRequestId[$sid])) {
                    $supplyToRequestId[$sid] = (int) $rr->id;
                    $orderedByMap[$sid] = $rr->requestedDepartment?->department_name;
                }
            }
            if (!empty($rr->items_json)) {
                $data = json_decode($rr->items_json, true);
                foreach (($data['items'] ?? []) as $it) {
                    if (isset($it['supply_id'])) {
                        $sid = (int) $it['supply_id'];
                        $orderedSupplyIds[] = $sid;
                        if (!isset($supplyToRequestId[$sid])) {
                            $supplyToRequestId[$sid] = (int) $rr->id;
                            $orderedByMap[$sid] = $rr->requestedDepartment?->department_name;
                        }
                    }
                }
            }
        }
        $orderedSupplyIds = array_values(array_unique($orderedSupplyIds));
    
        $suppliesQuery = Supply::with(['categories', 'suppliers'])
            ->where('supply_type', '!=', Supply::TYPE_BORROWABLE);
    
        // Exclude supplies already added to the order list
        $excludeIds = session()->get('to_order_list', []);
        if (!is_array($excludeIds)) {
            $excludeIds = [];
        }
        $excludeIds = array_values(array_unique(array_map('intval', $excludeIds)));
        if (count($excludeIds) > 0) {
            $suppliesQuery->whereNotIn('id', $excludeIds);
        }
    
        if ($search !== '') {
            $suppliesQuery->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }
    
        $supplies = $suppliesQuery->orderBy('name')->get();
    
        $toOrderItems = $supplies->map(function($supply) {
            $available = (int) $supply->availableQuantity();
            $minLevel = (int) ($supply->minimum_stock_level ?? 0);
            $suggested = $minLevel > $available ? ($minLevel - $available) : 0;
            $isToOrder = $minLevel > 0 && $available <= $minLevel;
            return [
                'supply' => $supply,
                'available' => $available,
                'minLevel' => $minLevel,
                'suggested' => $suggested,
                'toOrder' => $isToOrder,
            ];
        })->filter(function($item) {
            return $item['toOrder'];
        });
    
        // Apply filter for ordered/not ordered/all
        if ($filter === 'ordered') {
            $toOrderItems = $toOrderItems->filter(function($item) use ($orderedSupplyIds) {
                return in_array($item['supply']->id, $orderedSupplyIds, true);
            });
        } elseif ($filter === 'not_ordered') {
            $toOrderItems = $toOrderItems->filter(function($item) use ($orderedSupplyIds) {
                return !in_array($item['supply']->id, $orderedSupplyIds, true);
            });
        }
    
        return view('restock_requests.to_order_index', [
            'toOrderItems' => $toOrderItems,
            'search' => $search,
            'totalCount' => $toOrderItems->count(),
            'orderedSupplyIds' => $orderedSupplyIds,
            'orderedByMap' => $orderedByMap,
            'supplyToRequestId' => $supplyToRequestId,
            'filter' => $filter,
        ]);
    }

    public function toOrderAdd(Request $request)
    {
        // Add a supply to the session-based order list
        $this->authorize('viewAny', RestockRequest::class);

        $supplyId = (int) $request->get('supply_id');
        $supply = $supplyId ? Supply::find($supplyId) : null;
        if (!$supply) {
            return redirect()->route('to-order.index')
                ->withErrors(['error' => 'Supply not found.']);
        }

        $list = session()->get('to_order_list', []);
        if (!is_array($list)) {
            $list = [];
        }
        $list[] = $supply->id;
        $list = array_values(array_unique(array_map('intval', $list)));
        session()->put('to_order_list', $list);

        return redirect()->route('to-order.index')->with('status', 'Added to order list.');
    }

    public function toOrderOrderList(Request $request)
    {
        // Show all items added to the order list, grouped by supplier
        $this->authorize('viewAny', RestockRequest::class);

        $ids = session()->get('to_order_list', []);
        if (!is_array($ids)) {
            $ids = [];
        }

        // Selection filter inputs
        $selectionScope = $request->get('selection_scope'); // 'all' | 'selected'
        $supplierFilterId = (int) $request->get('supplier_id'); // group key (id)
        $selectedIds = collect($request->get('selected', []))
            ->map(fn($id) => (int) $id)
            ->filter()
            ->values()
            ->all();

        if (count($ids) === 0) {
            return view('restock_requests.to_order_order_list', [
                'grouped' => [],
                'unsupplied' => collect(),
                'totalCount' => 0,
            ]);
        }

        $supplies = Supply::with(['categories', 'suppliers'])
            ->whereIn('id', $ids)
            ->get();

        $grouped = [];
        $unsupplied = collect();

        foreach ($supplies as $supply) {
            $available = (int) $supply->availableQuantity();
            $minLevel = (int) ($supply->minimum_stock_level ?? 0);
            $suggested = $minLevel > $available ? ($minLevel - $available) : 0;

            $item = [
                'supply' => $supply,
                'available' => $available,
                'minLevel' => $minLevel,
                'suggested' => $suggested,
            ];

            if ($supply->suppliers->isEmpty()) {
                $unsupplied->push($item);
            } else {
                foreach ($supply->suppliers as $supplier) {
                    $sid = (int) $supplier->id;
                    if (!array_key_exists($sid, $grouped)) {
                        $grouped[$sid] = [
                            'name' => (string) $supplier->name,
                            'items' => collect(),
                        ];
                    }
                    $grouped[$sid]['items']->push($item);
                }
            }
        }

        // Apply selected-only filtering if requested
        if ($selectionScope === 'selected' && count($selectedIds) > 0) {
            foreach ($grouped as $sid => $data) {
                $filtered = $data['items']->filter(function($item) use ($selectedIds) {
                    return in_array($item['supply']->id, $selectedIds, true);
                });
                if ($filtered->isEmpty()) {
                    unset($grouped[$sid]);
                } else {
                    $grouped[$sid]['items'] = $filtered;
                }
            }

            $unsupplied = $unsupplied->filter(function($item) use ($selectedIds) {
                return in_array($item['supply']->id, $selectedIds, true);
            });
        }

        // Narrow groups to a specific supplier, if provided
        if ($supplierFilterId) {
            $grouped = array_key_exists($supplierFilterId, $grouped)
                ? [$supplierFilterId => $grouped[$supplierFilterId]]
                : [];
        }

        // Sort groups by supplier name
        uasort($grouped, function($a, $b) {
            return strcmp($a['name'], $b['name']);
        });

        return view('restock_requests.to_order_order_list', [
            'grouped' => $grouped,
            'unsupplied' => $unsupplied,
            'totalCount' => count($ids),
        ]);
    }

    public function toOrderSubmit(Request $request)
    {
        // Create restock requests for selected items and notify supplier
        $this->authorize('create', RestockRequest::class);

        $selectionScope = $request->get('selection_scope'); // 'all' | 'selected'
        $supplierId = (int) $request->get('supplier_id'); // group key (id)
        $selectedIds = collect($request->get('selected', []))
            ->map(fn($id) => (int) $id)
            ->filter()
            ->values()
            ->all();

        $ids = session()->get('to_order_list', []);
        if (!is_array($ids)) {
            $ids = [];
        }
        if (count($ids) === 0) {
            return redirect()->route('to-order.order-list')
                ->withErrors(['error' => 'Your order list is empty.']);
        }

        $supplies = Supply::with(['suppliers'])->whereIn('id', $ids)->get();

        // Build the supplier-specific collection to submit
        $supplierGroupItems = collect();
        foreach ($supplies as $supply) {
            // Determine if this supply belongs to the selected supplier group
            $inGroup = $supplierId
                ? $supply->suppliers->contains(fn($s) => (int) $s->id === $supplierId)
                : false;

            if (!$inGroup) {
                continue;
            }

            // Apply selected-only filter if requested
            if ($selectionScope === 'selected' && count($selectedIds) > 0 && !in_array($supply->id, $selectedIds, true)) {
                continue;
            }

            $available = (int) $supply->availableQuantity();
            $minLevel = (int) ($supply->minimum_stock_level ?? 0);
            $suggested = $minLevel > $available ? ($minLevel - $available) : 0;

            $supplierGroupItems->push([
                'supply' => $supply,
                'suggested' => $suggested,
            ]);
        }

        if ($supplierGroupItems->isEmpty()) {
            return redirect()->route('to-order.order-list')
                ->withErrors(['error' => 'No items selected for the supplier.']);
        }

        // Determine the supplier object directly by ID
        $targetSupplier = Supplier::find($supplierId);
        if (!$targetSupplier) {
            return redirect()->route('to-order.order-list')
                ->withErrors(['error' => 'Supplier not found.']);
        }

        \DB::beginTransaction();
        try {
            $createdIds = [];
            $noticeItems = [];

            // Build item list and total quantity for a single composite request
            $itemsList = [];
            $totalQty = 0;
            $firstSupplyId = null;

            foreach ($supplierGroupItems as $item) {
                $supply = $item['supply'];
                $qty = max(1, (int) $item['suggested']);

                if ($firstSupplyId === null) {
                    $firstSupplyId = $supply->id;
                }

                $itemsList[] = [
                    'supply_id' => $supply->id,
                    'supply_name' => $supply->name,
                    'quantity' => $qty,
                    'unit' => (string) ($supply->unit ?? ''),
                ];
                $noticeItems[] = [
                    'supply_name' => $supply->name,
                    'quantity' => $qty,
                    'unit' => (string) ($supply->unit ?? ''),
                ];
                $totalQty += $qty;
            }

            // Create a single consolidated RestockRequest
            $payload = [
                'supply_id' => $firstSupplyId ?? $supplierGroupItems->first()['supply']->id,
                'quantity' => $totalQty,
                'status' => 'ordered',
                'supplier_id' => $targetSupplier->id,
                'items_json' => json_encode(['items' => $itemsList, 'total_quantity' => $totalQty]),
            ];

            // Optional forwarding of department if provided
            if ($request->has('requested_department_id')) {
                $payload['requested_department_id'] = (int) $request->get('requested_department_id');
            }

            $rr = RestockRequest::create($payload);
            $createdIds[] = $rr->id;

            // Remove ordered supplies from the session list
            $removeIds = $supplierGroupItems->map(fn($item) => $item['supply']->id)->all();
            $newSessionIds = collect($ids)->diff($removeIds)->values()->all();
            session()->put('to_order_list', $newSessionIds);

            \DB::commit();
        } catch (\Throwable $e) {
            \DB::rollBack();
            return redirect()->route('to-order.order-list')
                ->withErrors(['error' => 'Submission failed: ' . $e->getMessage()]);
        }

        // Notify supplier according to preferred contact method
        $user = Auth::user();
        $preferred = (string) ($targetSupplier->preferred_contact_method ?? 'email');
        $supplierEmail = (string) ($targetSupplier->email ?? '');

        $contactMsg = '';
        if ($preferred === 'email' && $supplierEmail !== '') {
            Notification::route('mail', $supplierEmail)
                ->notify(new SupplierOrderNotification($targetSupplier, $noticeItems, $user));
            $contactMsg = 'Order emailed to ' . $targetSupplier->name . '.';
        } else {
            // Fallback: email the current user with the order details and contact instructions
            if ($user && $user->email) {
                Notification::route('mail', $user->email)
                    ->notify(new SupplierOrderNotification($targetSupplier, $noticeItems, $user));
            }
            $contactMsg = 'Order prepared for ' . $targetSupplier->name . '. Preferred contact: ' . $preferred . '.';
            if ($preferred === 'phone' && !empty($targetSupplier->phone1)) {
                $contactMsg .= ' Please call ' . $targetSupplier->phone1 . '.';
            } elseif ($preferred === 'facebook_messenger' && !empty($targetSupplier->facebook_messenger)) {
                $contactMsg .= ' Contact via Messenger: ' . $targetSupplier->facebook_messenger . '.';
            }
        }

        return redirect()->route('restock-requests.show', $rr)
            ->with('success', 'Created 1 consolidated restock request. ' . $contactMsg);
    }

    public function toOrderCreate(Request $request)
    {
        // Simple view to stage ordering for a specific supply
        $this->authorize('viewAny', RestockRequest::class);

        $supplyId = $request->get('supply_id');
        $supply = $supplyId ? Supply::with(['categories', 'suppliers'])->find($supplyId) : null;
        if (!$supply) {
            return redirect()->route('supplies.index', ['low_stock' => 1])
                ->withErrors(['error' => 'Supply not found for ordering.']);
        }

        $available = (int) $supply->availableQuantity();
        $minLevel = (int) ($supply->minimum_stock_level ?? 0);
        $suggested = $minLevel > $available ? ($minLevel - $available) : 0;

        return view('restock_requests.to_order', compact('supply', 'available', 'minLevel', 'suggested'));
    }

    public function store(Request $request)
    {
        // Direct creation disabled in favor of cart-driven workflow
        $this->authorize('create', RestockRequest::class);
        return redirect()->route('restock-requests.index')
            ->withErrors(['error' => 'Direct restocking is disabled. Please use the Department Cart to add and finalize restocks.']);
    }

    public function show(RestockRequest $restockRequest)
    {
        // Authorize viewing a restock request per policy
        $this->authorize('view', $restockRequest);
        $restockRequest->load(['supply', 'supplier', 'requestedDepartment']);
        return view('restock_requests.show', compact('restockRequest'));
    }

    public function edit(RestockRequest $restockRequest)
    {
        // Authorize updating restock requests per policy
        $this->authorize('update', $restockRequest);
        $supplies = Supply::all();
        return view('restock_requests.edit', compact('restockRequest', 'supplies'));
    }

    public function update(Request $request, RestockRequest $restockRequest)
    {
        // Authorize updating restock requests per policy
        $this->authorize('update', $restockRequest);
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $restockRequest->update($request->all());
        return redirect()->route('restock-requests.index')->with('success', 'Restock request updated successfully.');
    }

    public function showOrderPage(RestockRequest $restockRequest)
    {
        // Ordering via direct request disabled; use cart flow
        $this->authorize('update', $restockRequest);
        return redirect()->route('restock-requests.index')
            ->withErrors(['error' => 'Ordering is now cart-driven. Please finalize orders from the Department Cart.']);
    }

    public function order(Request $request, RestockRequest $restockRequest)
    {
        // Ordering via direct request disabled; use cart flow
        $this->authorize('update', $restockRequest);
        return redirect()->route('restock-requests.index')
            ->withErrors(['error' => 'Direct ordering is disabled. Please finalize orders through the Department Cart.']);
    }

    public function markAsDelivered(RestockRequest $restockRequest)
    {
        // Authorize delivery marking per policy (treated as update)
        $this->authorize('update', $restockRequest);
        $restockRequest->update(['status' => 'delivered']);

        $supply = $restockRequest->supply;
        $supply->quantity += $restockRequest->quantity;
        $supply->save();

        // Get supplier email
        $supplier = Supplier::find($restockRequest->supplier_id);

        // Check if supplier exists
        if (!$supplier) {
            return redirect()->route('restock-requests.index')->withErrors(['supplier' => 'Supplier not found.']);
        }

        // Trigger Notification
        $user = Auth::user();
        Notification::route('mail', [$user->email, $supplier->email])
                    ->notify(new RestockRequestNotification($restockRequest, 'delivered'));

        return redirect()->route('restock-requests.index')->with('success', 'Restock request marked as delivered and supply quantity updated successfully.');
    }
}
