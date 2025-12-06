<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\SupplyRequest;
use App\Models\SupplyRequestBatch;
use App\Models\Department;
use App\Models\RestockRequest;
use App\Models\Supply;
use App\Models\Category;
use App\Models\DepartmentCart;
use App\Models\DepartmentCartItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupplyRequestController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        
        // Restrict approval/decline actions to admin users only
        $this->middleware(function ($request, $next) {
            if (in_array($request->route()->getActionMethod(), ['approve', 'decline'])) {
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
        $search = $request->get('search');
        $perPage = 10;
        // Extract a numeric id from the search to match Request # (batch id)
        $searchNumericId = null;
        if ($search && preg_match('/\d+/', $search, $m)) {
            $searchNumericId = (int) $m[0];
        }
        
        // Base query with relationships
        $baseQuery = SupplyRequest::with(['department', 'supply', 'user']);
        
        // Filter by department if user is not admin (advisers and students see only their department's requests)
        if (!Auth::user()->hasAdminPrivileges()) {
            $baseQuery->where('department_id', Auth::user()->department_id);
        }
        
        // Filter by user if user is an adviser (advisers can only see their own requests)
        if (Auth::user()->hasRole('adviser')) {
            $baseQuery->where('user_id', Auth::user()->id);
        }
        
        // Dean users can see all requests from their department (no additional user filtering)
        
        // Apply search filter if provided
        if ($search) {
            $baseQuery->where(function($q) use ($search, $searchNumericId) {
                $q->where('item_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('status', 'like', "%{$search}%")
                  ->orWhereHas('department', function($dept) use ($search) {
                      $dept->where('department_name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('supply', function($supply) use ($search) {
                      $supply->where('name', 'like', "%{$search}%")
                             ->orWhere('description', 'like', "%{$search}%");
                  })
                  // Include Request # (batch id) and batch description in search scope
                  ->orWhereHas('batch', function($batch) use ($search, $searchNumericId) {
                      $batch->where('description', 'like', "%{$search}%");
                      if ($searchNumericId) {
                          $batch->orWhere('id', $searchNumericId);
                      }
                  });

                // Match direct batch_id when a numeric Request # is provided
                if ($searchNumericId) {
                    $q->orWhere('batch_id', $searchNumericId);
                }
            });
        }
        
        // Get paginated results for all requests
        $supplyRequests = $baseQuery->orderBy('created_at', 'desc')->paginate($perPage);
        
        // Create separate query instances for each tab
        $existingQuery = SupplyRequest::with(['department', 'supply', 'user'])->whereNotNull('supply_id');
        // New Item Requests should show consolidated batches instead of per-item rows
        $batchQuery = SupplyRequestBatch::with(['department', 'user']);
        
        // Apply department filtering to tab queries if user is not admin
        if (!Auth::user()->hasAdminPrivileges()) {
            $existingQuery->where('department_id', Auth::user()->department_id);
            $batchQuery->where('department_id', Auth::user()->department_id);
        }
        
        // Apply user filtering to tab queries if user is an adviser
        if (Auth::user()->hasRole('adviser')) {
            $existingQuery->where('user_id', Auth::user()->id);
            $batchQuery->where('user_id', Auth::user()->id);
        }
        
        // Dean users can see all requests from their department (no additional user filtering for tabs)
        
        // Apply search filter to each query separately if provided
        if ($search) {
            $existingQuery->where(function($q) use ($search, $searchNumericId) {
                $q->where('item_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('status', 'like', "%{$search}%")
                  ->orWhereHas('department', function($dept) use ($search) {
                      $dept->where('department_name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('supply', function($supply) use ($search) {
                      $supply->where('name', 'like', "%{$search}%")
                             ->orWhere('description', 'like', "%{$search}%");
                  })
                  // Include Request # (batch id) and batch description in Existing tab search
                  ->orWhereHas('batch', function($batch) use ($search, $searchNumericId) {
                      $batch->where('description', 'like', "%{$search}%");
                      if ($searchNumericId) {
                          $batch->orWhere('id', $searchNumericId);
                      }
                  });

                if ($searchNumericId) {
                    $q->orWhere('batch_id', $searchNumericId);
                }
            });

            // Batch-level search: by description/status/department/user, and items' names
            $batchQuery->where(function($q) use ($search, $searchNumericId) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('status', 'like', "%{$search}%")
                  ->orWhereHas('department', function($dept) use ($search) {
                      $dept->where('department_name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('user', function($user) use ($search) {
                      $user->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('items', function($items) use ($search) {
                      $items->where('item_name', 'like', "%{$search}%");
                  });

                // Include Request # (batch id) in New tab search
                if ($searchNumericId) {
                    $q->orWhere('id', $searchNumericId);
                }
            });
        }
        
        // Get paginated results for each tab
        $existingRequests = $existingQuery->orderBy('created_at', 'desc')->paginate($perPage, ['*'], 'existing_page');
        $newBatches = $batchQuery->orderBy('created_at', 'desc')->paginate($perPage, ['*'], 'new_page');

        return view('supply_requests.index', compact('supplyRequests', 'existingRequests', 'newBatches', 'search'));
    }

    public function create()
    {
        $user = Auth::user();

        // Determine departments list based on role
        // Admin users can select any department; others are locked to their own
        $departments = $user->hasAdminPrivileges() ? Department::all() : Department::where('id', $user->department_id)->get();

        // Show only consumable supplies issued to the user's department
        $issuedSupplyIds = \App\Models\IssuedItem::where('department_id', $user->department_id)
            ->whereHas('supply', function ($q) {
                $q->where('supply_type', 'consumable')
                  ->where('status', 'active');
            })
            ->pluck('supply_id')
            ->unique();

        $supplies = Supply::whereIn('id', $issuedSupplyIds)->orderBy('name')->get();
        $categories = Category::orderBy('name')->get();

        return view('supply_requests.create', compact('departments', 'supplies', 'user', 'categories'));
    }

    /**
     * Get consumable, active supplies issued to a specific department (AJAX).
     */
    public function departmentSupplies(Department $department): \Illuminate\Http\JsonResponse
    {
        // Find supplies issued to the given department that are consumable and active
        $issuedSupplyIds = \App\Models\IssuedItem::where('department_id', $department->id)
            ->whereHas('supply', function ($q) {
                $q->where('supply_type', 'consumable')
                  ->where('status', 'active');
            })
            ->pluck('supply_id')
            ->unique();

        $supplies = Supply::whereIn('id', $issuedSupplyIds)
            ->orderBy('name')
            ->get();

        $payload = $supplies->map(function ($s) {
            return [
                'id' => $s->id,
                'name' => $s->name,
                'unit' => $s->unit,
                'has_variants' => $s->hasVariants(),
                'available_quantity' => $s->hasVariants() ? $s->getTotalVariantQuantity() : $s->quantity,
            ];
        });

        return response()->json(['supplies' => $payload]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        // Enforce department lock for non-admins
        $departmentId = $user->hasAdminPrivileges() ? $request->input('department_id') : $user->department_id;

        // Validate department is present; prevent null department from causing DB constraint errors
        if (!$departmentId) {
            return redirect()->back()
                ->withErrors(['department_id' => 'Department is required. Please select a department or contact an administrator to assign your profile.'])
                ->withInput();
        }

        // Create consolidated batch header
        \DB::beginTransaction();
        try {
            $batch = new \App\Models\SupplyRequestBatch([
                'user_id' => $user->id,
                'department_id' => $departmentId,
                'description' => $request->input('description'),
                'status' => 'pending',
            ]);
            $batch->save();

            // Validate based on request type
            if ($request->input('request_type') === 'existing') {
                // Support cart-style items array with per-item quantities
                $items = $request->input('items');
                if (is_array($items) && count($items) > 0) {
                    // Validate each item
                    foreach ($items as $idx => $item) {
                        $request->validate([
                            "items.$idx.supply_id" => 'required|exists:supplies,id',
                            "items.$idx.quantity" => 'required|integer|min:1',
                            "items.$idx.supply_variant_id" => 'nullable|exists:supply_variants,id',
                        ]);
                    }

                    foreach ($items as $item) {
                        $supply = Supply::find($item['supply_id']);
                        SupplyRequest::create([
                            'batch_id' => $batch->id,
                            'supply_id' => $supply->id,
                            'item_name' => $supply->name,
                            'unit' => $supply->unit,
                            'quantity' => (int) $item['quantity'],
                            'department_id' => $departmentId,
                            'description' => $request->input('description'),
                            'user_id' => $user->id,
                            'supply_variant_id' => $item['supply_variant_id'] ?? null,
                            'status' => 'pending',
                        ]);
                    }
                } else {
                    // Backward compatibility: single supply selection
                    $request->validate([
                        'supply_id' => 'required|exists:supplies,id',
                        'quantity' => 'required|integer|min:1',
                        'supply_variant_id' => 'nullable|exists:supply_variants,id',
                    ]);

                    $supply = Supply::find($request->supply_id);
                    SupplyRequest::create([
                        'batch_id' => $batch->id,
                        'supply_id' => $supply->id,
                        'item_name' => $supply->name,
                        'unit' => $supply->unit,
                        'quantity' => (int) $request->quantity,
                        'department_id' => $departmentId,
                        'description' => $request->input('description'),
                        'user_id' => $user->id,
                        'supply_variant_id' => $request->input('supply_variant_id'),
                        'status' => 'pending',
                    ]);
                }
            } else {
                // Requesting new item(s)
                $items = $request->input('items');
                if (is_array($items) && count($items) > 0) {
                    // Validate each item in the array
                    foreach ($items as $idx => $item) {
                        $request->validate([
                            "items.$idx.item_name" => 'required|string|max:255',
                            "items.$idx.quantity" => 'required|integer|min:1',
                            "items.$idx.unit" => 'nullable|string|max:255',
                        ]);
                    }

                    foreach ($items as $item) {
                        SupplyRequest::create([
                            'batch_id' => $batch->id,
                            'item_name' => $item['item_name'],
                            'unit' => $item['unit'] ?? '',
                            'quantity' => (int) $item['quantity'],
                            'department_id' => $departmentId,
                            'description' => $request->input('description'),
                            'status' => 'pending',
                        ]);
                    }
                } else {
                    // Backward compatibility: single new item
                    $request->validate([
                        'item_name' => 'required|string|max:255',
                        'quantity' => 'required|integer|min:1',
                        'unit' => 'nullable|string|max:255',
                    ]);

                    SupplyRequest::create([
                        'batch_id' => $batch->id,
                        'item_name' => $request->input('item_name'),
                        'unit' => $request->input('unit', ''),
                        'quantity' => (int) $request->input('quantity'),
                        'department_id' => $departmentId,
                        'description' => $request->input('description'),
                        'status' => 'pending',
                    ]);
                }
            }

            \DB::commit();
        } catch (\Throwable $e) {
            \DB::rollBack();
            return redirect()->route('supply-requests.create')->withErrors(['error' => 'Failed to create supply request: ' . $e->getMessage()]);
        }

        // Redirect to consolidated request details
        return redirect()->route('supply-request-batches.show', $batch->id)->with('success', 'Supply request created successfully.');
    }

    public function show(SupplyRequest $supplyRequest)
    {
        // Attempt to resolve the related supply for this request
        $supply = null;
        if ($supplyRequest->supply_id) {
            $supply = \App\Models\Supply::find($supplyRequest->supply_id);
        } else {
            // For new-item requests, a Supply record is created upon approval using the item_name
            $supply = \App\Models\Supply::whereRaw('LOWER(name) = ?', [strtolower($supplyRequest->item_name)])
                ->first();
        }

        // Fetch restock requests associated with the resolved supply (if any)
        $restockRequests = collect();
        if ($supply) {
            $restockRequests = \App\Models\RestockRequest::with('supplier')
                ->where('supply_id', $supply->id)
                ->orderBy('created_at', 'asc')
                ->get();
        }

        return view('supply_requests.show', compact('supplyRequest', 'supply', 'restockRequests'));
    }

    public function edit(SupplyRequest $supplyRequest)
    {
        $departments = Department::all();
        return view('supply_requests.edit', compact('supplyRequest', 'departments'));
    }

    public function update(Request $request, SupplyRequest $supplyRequest)
    {
        $request->validate([
            'item_name' => 'required',
            'quantity' => 'required|integer',
            'unit' => 'nullable',
            'department_id' => 'required|exists:departments,id'
        ]);

        $supplyRequest->update($request->all());
        return redirect()->route('dashboard')->with('success', 'Supply request updated successfully.');
    }

    public function approve(SupplyRequest $supplyRequest)
    {
        $this->authorize('approve', $supplyRequest);

        // Optional review note
        $note = request()->input('admin_note');

        \DB::beginTransaction();
        try {
            $supplyRequest->update([
                'status' => 'approved',
                'admin_note' => $note,
            ]);

            // Add approved item to the department's active cart (no restock request yet)
            $cart = DepartmentCart::forDepartment((int) $supplyRequest->department_id);

            // Resolve item type from existing supply or default to consumable
            $resolvedSupply = null;
            $itemType = DepartmentCartItem::TYPE_CONSUMABLE;
            if ($supplyRequest->supply_id) {
                $resolvedSupply = Supply::find($supplyRequest->supply_id);
            } else {
                $resolvedSupply = Supply::whereRaw('LOWER(name) = ?', [strtolower($supplyRequest->item_name)])
                    ->first();
            }
            if ($resolvedSupply && in_array($resolvedSupply->supply_type, [Supply::TYPE_CONSUMABLE, Supply::TYPE_GRANTABLE])) {
                $itemType = $resolvedSupply->supply_type;
            }

            DepartmentCartItem::create([
                'cart_id' => $cart->id,
                'supply_request_id' => $supplyRequest->id,
                'supply_id' => $resolvedSupply?->id,
                'item_name' => $supplyRequest->item_name,
                'unit' => $supplyRequest->unit,
                'quantity' => (int) $supplyRequest->quantity,
                'item_type' => $itemType,
                'attributes' => [
                    'requested_description' => $supplyRequest->description,
                    'requested_by' => $supplyRequest->user_id,
                ],
                'status' => 'pending',
            ]);

            // Audit log
            \App\Models\SupplyRequestAudit::create([
                'supply_request_id' => $supplyRequest->id,
                'user_id' => Auth::id(),
                'action' => 'approved',
                'note' => $note,
            ]);

            // Update batch aggregate status if applicable
            if ($supplyRequest->batch_id) {
                $batch = $supplyRequest->batch()->with('items')->first();
                $statuses = $batch->items->pluck('status');
                $newStatus = 'pending';
                if ($statuses->every(fn($s) => $s === 'approved')) {
                    $newStatus = 'approved';
                } elseif ($statuses->every(fn($s) => in_array($s, ['rejected', 'declined']))) {
                    $newStatus = 'rejected';
                } elseif ($statuses->contains('approved') || $statuses->contains('rejected') || $statuses->contains('declined')) {
                    $newStatus = 'partial';
                }
                $batch->update(['status' => $newStatus]);
            }

            \DB::commit();
        } catch (\Throwable $e) {
            \DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'Approval failed: ' . $e->getMessage()]);
        }

        // Notify request owner
        if ($supplyRequest->user) {
            $supplyRequest->user->notify(new \App\Notifications\SupplyRequestStatusNotification($supplyRequest, 'approved'));
        }

        return redirect()->route('supply-requests.show', $supplyRequest)->with('success', 'Supply request approved and added to the department cart.');
    }

    public function decline(SupplyRequest $supplyRequest)
    {
        $this->authorize('decline', $supplyRequest);

        $reason = request()->input('rejection_reason');
        $note = request()->input('admin_note');

        // Require reason for decline
        request()->validate([
            'rejection_reason' => 'required|string|min:3',
        ]);

        \DB::beginTransaction();
        try {
            $supplyRequest->update([
                'status' => 'rejected',
                'rejection_reason' => $reason,
                'admin_note' => $note,
            ]);

            // Audit log
            \App\Models\SupplyRequestAudit::create([
                'supply_request_id' => $supplyRequest->id,
                'user_id' => Auth::id(),
                'action' => 'declined',
                'note' => $note,
                'reason' => $reason,
            ]);

            // Update batch aggregate status if applicable
            if ($supplyRequest->batch_id) {
                $batch = $supplyRequest->batch()->with('items')->first();
                $statuses = $batch->items->pluck('status');
                $newStatus = 'pending';
                if ($statuses->every(fn($s) => $s === 'approved')) {
                    $newStatus = 'approved';
                } elseif ($statuses->every(fn($s) => in_array($s, ['rejected', 'declined']))) {
                    $newStatus = 'rejected';
                } elseif ($statuses->contains('approved') || $statuses->contains('rejected') || $statuses->contains('declined')) {
                    $newStatus = 'partial';
                }
                $batch->update(['status' => $newStatus]);
            }

            \DB::commit();
        } catch (\Throwable $e) {
            \DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'Decline failed: ' . $e->getMessage()]);
        }

        // Notify request owner
        if ($supplyRequest->user) {
            $supplyRequest->user->notify(new \App\Notifications\SupplyRequestStatusNotification($supplyRequest, 'declined'));
        }

        return redirect()->route('supply-requests.show', $supplyRequest)->with('success', 'Supply request item declined.');
    }
}
