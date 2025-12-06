<?php

namespace App\Http\Controllers;

use App\Models\InterDepartmentLoanRequest;
use App\Models\InterDepartmentBorrowedItem;
use App\Models\InterDepartmentReturnRecord;
use App\Models\IssuedItem;
use App\Models\Department;
use App\Models\User;
use App\Models\InterDepartmentLoanApprovalLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Notifications\InterDepartmentLoanNotification;

class InterDepartmentLoanController extends Controller
{
    public function __construct()
    {
        // Basic authentication required for all actions
        $this->middleware('auth');
        
        // Restrict dean approval actions to dean users only
        $this->middleware(function ($request, $next) {
            if (in_array($request->route()->getActionMethod(), ['deanApprove'])) {
                if (!Auth::user()) {
                    abort(403, 'Unauthorized. Authentication required.');
                }
                
                $user = Auth::user();
                
                // Load role relationship to prevent null reference errors
                if (!$user->relationLoaded('role')) {
                    $user->load('role');
                }
                
                if (!$user->hasRole('dean')) {
                    abort(403, 'Unauthorized. Dean access required.');
                }
            }
            return $next($request);
        });

        // Restrict lending dean approval actions to dean users only
        $this->middleware(function ($request, $next) {
            if (in_array($request->route()->getActionMethod(), ['lendingDeanApprove'])) {
                if (!Auth::user()) {
                    abort(403, 'Unauthorized. Authentication required.');
                }
                
                $user = Auth::user();
                
                // Load role relationship to prevent null reference errors
                if (!$user->relationLoaded('role')) {
                    $user->load('role');
                }
                
                if (!$user->hasRole('dean')) {
                    abort(403, 'Unauthorized. Dean access required.');
                }
            }
            return $next($request);
        });

        // Restrict admin approval actions to admin/super admin users only
        $this->middleware(function ($request, $next) {
            if (in_array($request->route()->getActionMethod(), ['adminApprove', 'approve', 'decline', 'fulfill', 'returnItem'])) {
                if (!Auth::user()) {
                    abort(403, 'Unauthorized. Authentication required.');
                }
                
                $user = Auth::user();
                
                // Load role relationship to prevent null reference errors
                if (!$user->relationLoaded('role')) {
                    $user->load('role');
                }
                
                // For admin approval and other admin actions, require admin privileges only
                if (!$user->hasAdminPrivileges()) {
                    abort(403, 'Unauthorized. Admin access required.');
                }
            }
            return $next($request);
        });

        // Restrict lending approval to lending department users or deans
        $this->middleware(function ($request, $next) {
            if (in_array($request->route()->getActionMethod(), ['approveLending'])) {
                if (!Auth::user()) {
                    abort(403, 'Unauthorized. Authentication required.');
                }
                
                $user = Auth::user();
                
                // Load role relationship to prevent null reference errors
                if (!$user->relationLoaded('role')) {
                    $user->load('role');
                }
                
                // This will be further validated in the method itself based on department
                if (!$user->hasRole('dean') && !$user->hasAdminPrivileges()) {
                    // Allow if user is from lending department (validated in method)
                    // or has admin privileges
                }
            }
            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = InterDepartmentLoanRequest::with([
            'issuedItem.supply',
            'requestItems.issuedItem.supply',
            'lendingDepartment',
            'borrowingDepartment',
            'requestedBy',
            'lendingApprovedBy',
            'borrowingConfirmedBy',
            'adminApprovedBy'
        ]);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by department (for department users)
        $user = Auth::user();
        if ($user->role->name !== 'Super Admin') {
            if ($user->hasAdminPrivileges()) {
                // Admin users: if no explicit status filter, show requests ready for admin approval,
                // currently borrowed, and completed. Do not override an explicit status filter.
                if (!$request->filled('status')) {
                    $query->whereIn('status', ['lending_dean_approved', 'borrowed', 'completed']);
                }
            } else {
                // If user is a student or adviser, only show their own requests
                if ($user->hasRole('student') || $user->hasRole('adviser')) {
                    $query->where('requested_by', $user->id);
                } elseif ($user->hasRole('dean')) {
                    // Dean users: show requests requested by their department OR incoming to their department as lending
                    $query->where(function ($q) use ($user) {
                        $q->whereHas('requestedBy', function ($subQ) use ($user) {
                            $subQ->where('department_id', $user->department_id);
                        })
                        ->orWhere('lending_department_id', $user->department_id);
                    });
                } else {
                    // For other non-admin users, show requests related to their department
                    $query->where(function ($q) use ($user) {
                        $q->where('lending_department_id', $user->department_id)
                          ->orWhere('borrowing_department_id', $user->department_id);
                    });
                }
            }
        }
        // Super Admin users can see all requests (no filtering applied)

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('issuedItem.supply', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            })->orWhereHas('lendingDepartment', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            })->orWhereHas('borrowingDepartment', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        // Department filter
        if ($request->filled('department')) {
            $query->where(function ($q) use ($request) {
                $q->where('lending_department_id', $request->department)
                  ->orWhere('borrowing_department_id', $request->department);
            });
        }

        $loanRequests = $query->with([
            'issuedItem.supply',
            'lendingDepartment',
            'borrowingDepartment',
            'requestedBy'
        ])->orderBy('created_at', 'desc')->paginate(15);

        // Calculate statistics for the same filtered query
        $baseQuery = InterDepartmentLoanRequest::query();
        
        // Apply the same filters for statistics
        if ($user->role->name !== 'Super Admin') {
            if ($user->hasRole('student') || $user->hasRole('adviser')) {
                $baseQuery->where('requested_by', $user->id);
            } elseif ($user->hasRole('dean')) {
                // Dean users: statistics reflect requests made by their department OR incoming to their lending department
                $baseQuery->where(function ($q) use ($user) {
                    $q->whereHas('requestedBy', function ($subQ) use ($user) {
                        $subQ->where('department_id', $user->department_id);
                    })
                    ->orWhere('lending_department_id', $user->department_id);
                });
            } elseif ($user->hasAdminPrivileges()) {
                // Mirror admin view: include ready-for-admin, borrowed, and completed when no explicit status filter
                if (!$request->filled('status')) {
                    $baseQuery->whereIn('status', ['lending_dean_approved', 'borrowed', 'completed']);
                }
            } else {
                $baseQuery->where(function ($q) use ($user) {
                    $q->where('lending_department_id', $user->department_id)
                      ->orWhere('borrowing_department_id', $user->department_id);
                });
            }
        }

        // Apply search filter to statistics
        if ($request->filled('search')) {
            $search = $request->search;
            $baseQuery->whereHas('issuedItem.supply', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            })->orWhereHas('requestItems.issuedItem.supply', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            })->orWhereHas('lendingDepartment', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            })->orWhereHas('borrowingDepartment', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        // Apply department filter to statistics
        if ($request->filled('department')) {
            $baseQuery->where(function ($q) use ($request) {
                $q->where('lending_department_id', $request->department)
                  ->orWhere('borrowing_department_id', $request->department);
            });
        }

        // Calculate statistics
        $totalRequests = $baseQuery->count();
        $pendingRequests = (clone $baseQuery)->where('status', 'pending')->count();
        $approvedRequests = (clone $baseQuery)->whereIn('status', ['dean_approved', 'lending_dean_approved', 'borrowed', 'completed'])->count();
        $declinedRequests = (clone $baseQuery)->where('status', 'declined')->count();

        // Get departments for filter dropdown
        $departments = Department::where('status', 'active')->orderBy('department_name')->get();

        return view('inter_department_loans.index', compact(
            'loanRequests', 
            'totalRequests', 
            'pendingRequests', 
            'approvedRequests', 
            'declinedRequests',
            'departments'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = Auth::user();
        
        // Ensure user has a department assigned (exempt admins and super admins)
        if (!$user->department_id && !$user->hasAdminPrivileges() && ($user->role->name ?? '') !== 'Super Admin') {
            return redirect()->route('loan-requests.inter-department.index')
                ->with('error', 'You must be assigned to a department to create loan requests.');
        }
        
        // Only allow students, advisers, deans, or admins to create requests
        if (
            !$user->hasRole('student') &&
            !$user->hasRole('adviser') &&
            !$user->hasRole('dean') &&
            !$user->hasAdminPrivileges()
        ) {
            return redirect()->route('loan-requests.inter-department.index')
                ->with('error', 'Only students, advisers, deans, or admins can create loan requests.');
        }
        
        // Get available issued items
        $availableItems = IssuedItem::with(['supply', 'department'])
            ->whereHas('supply', function ($q) {
                $q->where('supply_type', 'grantable')
                  ->where('status', 'active');
            })
            ->when(!$user->hasAdminPrivileges(), function ($q) use ($user) {
                // Non-admins: only show items from other departments
                return $q->where('department_id', '!=', $user->department_id);
            })
            ->where('quantity', '>', 0)
            ->whereNull('user_id') // Exclude items that have a recipient
            ->get();

        // Departments for dropdowns
        $departments = Department::where('status', 'active')
            ->when(!$user->hasAdminPrivileges(), function ($q) use ($user) {
                // Non-admins: exclude their own department
                return $q->where('id', '!=', $user->department_id);
            })
            ->orderBy('department_name')
            ->get();

        return view('inter_department_loans.create', compact('availableItems', 'departments'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        // Ensure user has a department assigned (exempt admins and super admins)
        if (!$user->department_id && !$user->hasAdminPrivileges() && ($user->role->name ?? '') !== 'Super Admin') {
            return redirect()->route('loan-requests.index', ['tab' => 'inter'])
                ->with('error', 'You must be assigned to a department to create loan requests.');
        }
        
        // Only allow students, advisers, deans, or admins to create requests
        if (
            !$user->hasRole('student') &&
            !$user->hasRole('adviser') &&
            !$user->hasRole('dean') &&
            !$user->hasAdminPrivileges()
        ) {
            return redirect()->route('loan-requests.index', ['tab' => 'inter'])
                ->with('error', 'Only students, advisers, deans, or admins can create loan requests.');
        }

        $isAdmin = $user->hasAdminPrivileges();
        if ($isAdmin) {
            // Admins must explicitly select lending and receiving departments
            $request->validate([
                'admin_lending_department_id' => 'required|exists:departments,id',
                'admin_receiving_department_id' => 'required|exists:departments,id|different:admin_lending_department_id',
            ]);
        }

        // Decide between single-item and multi-item flow.
        // Support both cart-style items[] (preferred) and legacy items_payload JSON.
        $itemsArray = $request->input('items');
        $itemsPayloadJson = $request->input('items_payload');
        $usingMulti = (is_array($itemsArray) && count($itemsArray) > 0) || !empty($itemsPayloadJson);

        if ($usingMulti) {
            // Normalize items to array of {issued_item_id, quantity}
            if (is_array($itemsArray) && count($itemsArray) > 0) {
                // Validate cart-style items[] payload
                $request->validate([
                    'purpose' => 'required|string|max:1000',
                    'planned_start_date' => 'required|date|after_or_equal:today',
                    'expected_return_date' => 'required|date|after_or_equal:planned_start_date',
                    'notes' => 'nullable|string|max:1000',
                    'items' => 'required|array|min:1',
                    'items.*.issued_item_id' => 'required|integer|exists:issued_items,id',
                    'items.*.quantity' => 'required|integer|min:1',
                ]);
                $items = $itemsArray;
            } else {
                // Fallback to legacy JSON payload
                $items = json_decode($itemsPayloadJson, true);
                if (!is_array($items) || empty($items)) {
                    return back()->withErrors(['items_payload' => 'Invalid items selection.'])->withInput();
                }
                $request->validate([
                    'purpose' => 'required|string|max:1000',
                    'planned_start_date' => 'required|date|after_or_equal:today',
                    'expected_return_date' => 'required|date|after_or_equal:planned_start_date',
                    'notes' => 'nullable|string|max:1000',
                ]);
            }

            // Validate items against department constraints and stock
            $validatedItems = [];
            $lendingDepartmentId = null;
            $totalQty = 0;

            foreach ($items as $item) {
                if (!isset($item['issued_item_id'], $item['quantity'])) {
                    continue;
                }
                $issuedItem = IssuedItem::with('department', 'supply')->find($item['issued_item_id']);
                if (!$issuedItem) { continue; }

                $qty = (int) $item['quantity'];
                // Enforce dynamic availability that accounts for inter-department borrows
                $available = (int) $issuedItem->availableQuantity;
                if ($available < 1 || $qty < 1 || $qty > $available) { continue; }

                if ($isAdmin) {
                    // Admins: enforce all items come from selected lending department
                    if ((int)$issuedItem->department_id !== (int)$request->admin_lending_department_id) {
                        continue;
                    }
                } else {
                    // Non-admins: items must be from a different department and consistent
                    if ($issuedItem->department_id === $user->department_id) {
                        continue;
                    }
                }

                // Set or check lending department consistency
                if (is_null($lendingDepartmentId)) {
                    $lendingDepartmentId = (int) $issuedItem->department_id;
                } elseif ((int)$issuedItem->department_id !== $lendingDepartmentId) {
                    // Disallow cross-department items in a single batch
                    continue;
                }

                $validatedItems[] = [
                    'issued_item' => $issuedItem,
                    'quantity' => $qty,
                ];
                $totalQty += $qty;
            }

            if (count($validatedItems) === 0) {
                $errorKey = is_array($itemsArray) && count($itemsArray) > 0 ? 'items' : 'items_payload';
                return back()->withErrors([$errorKey => 'No valid items selected or items span multiple lending departments.']).withInput();
            }

            // Create single batch request header
            $firstIssuedItemId = optional($validatedItems[0]['issued_item'])->id;
            $loanRequest = InterDepartmentLoanRequest::create([
                // Keep first item for backward compatibility; requested items stored separately
                'issued_item_id' => $firstIssuedItemId,
                'lending_department_id' => $lendingDepartmentId,
                'borrowing_department_id' => $isAdmin ? (int)$request->admin_receiving_department_id : $user->department_id,
                'requested_by' => $user->id,
                'quantity_requested' => $totalQty,
                'purpose' => $request->purpose,
                'expected_return_date' => $request->expected_return_date,
                'planned_start_date' => $request->planned_start_date,
                'status' => 'pending',
                'notes' => $request->notes,
            ]);

            // Create request items under this header
            foreach ($validatedItems as $vi) {
                \App\Models\InterDepartmentLoanRequestItem::create([
                    'inter_department_loan_request_id' => $loanRequest->id,
                    'issued_item_id' => $vi['issued_item']->id,
                    'quantity_requested' => $vi['quantity'],
                    'notes' => null,
                ]);
            }

            if ($user->isDean()) {
                $loanRequest->autoApproveByDean($user);
            }

            $successMessage = $user->isDean()
                ? 'Inter-department batch request submitted and auto-approved by dean.'
                : 'Inter-department batch request submitted successfully.';

            return redirect()->route('loan-requests.index', ['tab' => 'inter'])
                ->with('success', $successMessage);
        } else {
            // Single-item flow (existing behavior)
            $request->validate([
                'issued_item_id' => 'required|exists:issued_items,id',
                'quantity_requested' => 'required|integer|min:1',
                'purpose' => 'required|string|max:1000',
                'planned_start_date' => 'required|date|after_or_equal:today',
                'expected_return_date' => 'required|date|after_or_equal:planned_start_date',
            ]);

            $issuedItem = IssuedItem::findOrFail($request->issued_item_id);

            if ($isAdmin) {
                // Admins: enforce selected lending department and different receiving department
                if ((int)$issuedItem->department_id !== (int)$request->admin_lending_department_id) {
                    return back()->withErrors([
                        'issued_item_id' => 'Selected item does not belong to the chosen lending department.'
                    ])->withInput();
                }
                if ((int)$issuedItem->department_id === (int)$request->admin_receiving_department_id) {
                    return back()->withErrors([
                        'admin_receiving_department_id' => 'Lending and receiving departments must be different.'
                    ])->withInput();
                }
            } else {
                // Ensure the item is from a different department for non-admins
                if ($issuedItem->department_id === $user->department_id) {
                    return back()->withErrors([
                        'issued_item_id' => 'You cannot request items from your own department.'
                    ])->withInput();
                }
            }

            // Validate quantity against dynamic availability (includes inter-department borrows)
            $available = (int) $issuedItem->availableQuantity;
            if ($available < 1) {
                return back()->withErrors([
                    'issued_item_id' => 'Selected item is fully borrowed and unavailable.'
                ])->withInput();
            }
            if ($request->quantity_requested > $available) {
                return back()->withErrors([
                    'quantity_requested' => 'Requested quantity exceeds available quantity.'
                ])->withInput();
            }

            $loanRequest = InterDepartmentLoanRequest::create([
                'issued_item_id' => $request->issued_item_id,
                'lending_department_id' => $issuedItem->department_id,
                'borrowing_department_id' => $isAdmin ? (int)$request->admin_receiving_department_id : $user->department_id,
                'requested_by' => $user->id,
                'quantity_requested' => $request->quantity_requested,
                'purpose' => $request->purpose,
                'expected_return_date' => $request->expected_return_date,
                'planned_start_date' => $request->planned_start_date,
                'status' => 'pending',
                'notes' => $request->notes,
            ]);

            // Auto-approve if request is created by a dean
            if ($user->isDean()) {
                $loanRequest->autoApproveByDean($user);
                $successMessage = 'Inter-department loan request submitted and automatically approved by dean. Forwarded to lending department dean for review.';
            } else {
                $successMessage = 'Inter-department loan request submitted successfully.';
            }

            return redirect()->route('loan-requests.index', ['tab' => 'inter'])
                ->with('success', $successMessage);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(InterDepartmentLoanRequest $interDepartmentLoan)
    {
        $user = Auth::user();
        
        // Check if user has permission to view this request
        if ($user->role->name !== 'Super Admin' && !$user->hasAdminPrivileges()) {
            $canView = false;
            
            // Students and advisers can only view their own requests
            if ($user->hasRole('student') || $user->hasRole('adviser')) {
                $canView = $interDepartmentLoan->requested_by === $user->id;
            } elseif ($user->hasRole('dean')) {
                // Dean can view if:
                // 1. Requester is from their department
                // 2. Their department is the lending department AND request is dean_approved or later (including return_pending)
                $canView = ($interDepartmentLoan->requestedBy->department_id === $user->department_id) ||
                          ($interDepartmentLoan->lending_department_id === $user->department_id && 
                           in_array($interDepartmentLoan->status, ['dean_approved', 'lending_dean_approved', 'borrowed', 'return_pending', 'completed']));
            } else {
                // Other non-admin users can view if their department is involved
                $canView = ($interDepartmentLoan->lending_department_id === $user->department_id) ||
                          ($interDepartmentLoan->borrowing_department_id === $user->department_id);
            }
            
            if (!$canView) {
                abort(403, 'You do not have permission to view this request.');
            }
        }
        // Super Admin and Admin users can view all requests (no authorization check needed)
        
        $interDepartmentLoan->load([
            'issuedItem.supply',
            'requestItems.issuedItem.supply',
            'lendingDepartment',
            'borrowingDepartment',
            'requestedBy',
            'lendingApprovedBy',
            'borrowingConfirmedBy',
            'adminApprovedBy',
            'declinedBy',
            'interDepartmentBorrowedItems'
        ]);

        return view('inter_department_loans.show', compact('interDepartmentLoan'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(InterDepartmentLoanRequest $interDepartmentLoan)
    {
        // Only allow editing if request is still pending
        if (!$interDepartmentLoan->isPending()) {
            return redirect()->route('inter-department-loans.show', $interDepartmentLoan)
                ->with('error', 'Cannot edit request that has already been processed.');
        }

        $user = Auth::user();
        
        // Only allow the requester to edit
        if ($interDepartmentLoan->requested_by !== $user->id) {
            return redirect()->route('inter-department-loans.show', $interDepartmentLoan)
                ->with('error', 'You can only edit your own requests.');
        }

        $availableItems = IssuedItem::with(['supply', 'department'])
            ->whereHas('supply', function ($q) {
                $q->where('supply_type', 'grantable')
                  ->where('status', 'active');
            })
            ->where('department_id', '!=', $user->department_id)
            ->where('quantity', '>', 0)
            ->get();

        return view('inter_department_loans.edit', compact('interDepartmentLoan', 'availableItems'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, InterDepartmentLoanRequest $interDepartmentLoan)
    {
        if (!$interDepartmentLoan->isPending()) {
            return redirect()->route('inter-department-loans.show', $interDepartmentLoan)
                ->with('error', 'Cannot update request that has already been processed.');
        }

        $user = Auth::user();
        if ($interDepartmentLoan->requested_by !== $user->id) {
            return redirect()->route('inter-department-loans.show', $interDepartmentLoan)
                ->with('error', 'You can only update your own requests.');
        }

        $request->validate([
            'quantity_requested' => 'required|integer|min:1',
            'purpose' => 'required|string|max:1000',
            'expected_return_date' => 'required|date|after:today',
        ]);

        // Validate quantity against available quantity of the issued item
        if ($request->quantity_requested > $interDepartmentLoan->issuedItem->availableQuantity) {
            return back()->withErrors([
                'quantity_requested' => 'Requested quantity exceeds available quantity.'
            ])->withInput();
        }

        $interDepartmentLoan->update([
            'quantity_requested' => $request->quantity_requested,
            'purpose' => $request->purpose,
            'expected_return_date' => $request->expected_return_date,
            'notes' => $request->notes,
        ]);

            return redirect()->route('inter-department-loans.show', $interDepartmentLoan)
            ->with('success', 'Loan request updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InterDepartmentLoanRequest $interDepartmentLoan)
    {
        if (!$interDepartmentLoan->isPending()) {
            return redirect()->route('inter-department-loans.index')
                ->with('error', 'Cannot delete request that has already been processed.');
        }

        $user = Auth::user();
        if ($interDepartmentLoan->requested_by !== $user->id && $user->role->name !== 'Super Admin') {
            return redirect()->route('inter-department-loans.index')
                ->with('error', 'You can only delete your own requests.');
        }

        $interDepartmentLoan->delete();

        return redirect()->route('loan-requests.index', ['tab' => 'inter'])
            ->with('success', 'Loan request deleted successfully.');
    }

    /**
     * Lending approval (Step 2 of 3-step approval)
     */
    public function approveLending(Request $request, InterDepartmentLoanRequest $interDepartmentLoan)
    {
        $user = Auth::user();
        
        // Check if user is from lending department or has dean privileges
        if ($user->department_id !== $interDepartmentLoan->lending_department_id && !$user->hasRole('dean')) {
            return redirect()->back()->with('error', 'Only lending department or dean can approve this request.');
        }

        // Lending approval can happen after dean approval
        if (!$interDepartmentLoan->isDeanApproved()) {
            return redirect()->back()->with('error', 'Request must be approved by dean first.');
        }

        // Check if dean approval is needed and present
        if (!$interDepartmentLoan->canProceedToReview()) {
            return redirect()->back()->with('error', 'Request must be approved by dean first (if not initiated by dean).');
        }

        $request->validate([
            'lending_approval_notes' => 'nullable|string|max:1000',
        ]);

        $interDepartmentLoan->approveLending($user, $request->lending_approval_notes);
        // Record approval history
        InterDepartmentLoanApprovalLog::create([
            'inter_department_loan_request_id' => $interDepartmentLoan->id,
            'approver_id' => $user->id,
            'approver_role' => optional($user->role)->name,
            'action' => 'lending_approved',
            'notes' => $request->lending_approval_notes,
        ]);

        return redirect()->route('loan-requests.index', ['tab' => 'inter'])
            ->with('success', 'Request approved by lending department. Waiting for admin approval.');
    }



    /**
     * Dean approval (Step 1 of 3-step approval)
     */
    public function deanApprove(Request $request, InterDepartmentLoanRequest $interDepartmentLoan)
    {
        $user = Auth::user();
        
        // Check if user is dean
        if (!$user->hasRole('dean')) {
            return redirect()->back()->with('error', 'Only dean can perform dean approval.');
        }

        // Check if dean is from the borrowing department (requester's department)
        if (!$user->isDeanOf($interDepartmentLoan->borrowingDepartment)) {
            return redirect()->back()->with('error', 'You can only approve requests from your own department.');
        }

        // Dean approval: allow when request is pending OR borrowing has been confirmed
        if (!$interDepartmentLoan->isPending() && !$interDepartmentLoan->isBorrowingConfirmed()) {
            return redirect()->back()->with('error', 'Request is not eligible for dean approval.');
        }

        if ($interDepartmentLoan->isDeanApproved()) {
            return redirect()->back()->with('error', 'Request has already been approved by dean.');
        }

        $request->validate([
            'dean_approval_notes' => 'nullable|string|max:1000',
        ]);

        $interDepartmentLoan->deanApprove($user, $request->dean_approval_notes);
        // Record approval history
        InterDepartmentLoanApprovalLog::create([
            'inter_department_loan_request_id' => $interDepartmentLoan->id,
            'approver_id' => $user->id,
            'approver_role' => optional($user->role)->name,
            'action' => 'dean_approved',
            'notes' => $request->dean_approval_notes,
        ]);

        // Redirect back to dashboard for a smoother UX from the dean panel
        return redirect()->route('dashboard')
            ->with('success', 'Inter-department request approved by dean successfully.');
    }

    /**
     * Lending Department Dean approval (Step 2 of 3-step approval)
     */
    public function lendingDeanApprove(Request $request, InterDepartmentLoanRequest $interDepartmentLoan)
    {
        $user = Auth::user();
        
        // Check if user is dean of the lending department
        if (!$user->isDeanOf($interDepartmentLoan->lendingDepartment)) {
            return redirect()->back()->with('error', 'Only the dean of the lending department can approve this request.');
        }

        // Check if request is ready for lending dean approval
        if (!$interDepartmentLoan->isDeanApproved()) {
            return redirect()->back()->with('error', 'Request must be approved by dean first.');
        }

        if ($interDepartmentLoan->isLendingDeanApproved()) {
            return redirect()->back()->with('error', 'Request has already been approved by lending department dean.');
        }

        $request->validate([
            'lending_approval_notes' => 'nullable|string|max:1000',
        ]);

        $interDepartmentLoan->lendingDeanApprove($user, $request->lending_approval_notes);
        // Record approval history
        InterDepartmentLoanApprovalLog::create([
            'inter_department_loan_request_id' => $interDepartmentLoan->id,
            'approver_id' => $user->id,
            'approver_role' => optional($user->role)->name,
            'action' => 'lending_dean_approved',
            'notes' => $request->lending_approval_notes,
        ]);

        return redirect()->route('loan-requests.index', ['tab' => 'inter'])
            ->with('success', 'Request approved by lending department dean successfully. Ready for admin approval.');
    }

    /**
     * Admin approval (Step 3 of 3-step approval)
     */
    public function adminApprove(Request $request, InterDepartmentLoanRequest $interDepartmentLoan)
    {
        $user = Auth::user();
        
        // Check if user has admin privileges (dean, admin, or super admin)
        if (!$user->hasRole('dean') && !$user->hasAdminPrivileges()) {
            return redirect()->back()->with('error', 'Only dean, admin, or super admin can perform final approval.');
        }

        // Admin can approve if dean has approved and lending department has approved
        if (!$interDepartmentLoan->isDeanApproved()) {
            return redirect()->back()->with('error', 'Request must be approved by dean first.');
        }

        if (!$interDepartmentLoan->isLendingDeanApproved()) {
            return redirect()->back()->with('error', 'Request must be approved by lending department dean first.');
        }

        $request->validate([
            'admin_approval_notes' => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($interDepartmentLoan, $user, $request) {
            // Approve the request (sets header status to borrowed)
            $interDepartmentLoan->adminApprove($user, $request->admin_approval_notes);

            // Record approval history
            InterDepartmentLoanApprovalLog::create([
                'inter_department_loan_request_id' => $interDepartmentLoan->id,
                'approver_id' => $user->id,
                'approver_role' => optional($user->role)->name,
                'action' => 'admin_approved',
                'notes' => $request->admin_approval_notes,
            ]);

            // Create borrowed item records: one per request item (fallback to header for legacy single-item)
            $requestItems = $interDepartmentLoan->requestItems()->with('issuedItem')->get();

            if ($requestItems->isEmpty()) {
                // Legacy single-item request
                InterDepartmentBorrowedItem::create([
                    'inter_department_loan_request_id' => $interDepartmentLoan->id,
                    'issued_item_id' => $interDepartmentLoan->issued_item_id,
                    'lending_department_id' => $interDepartmentLoan->lending_department_id,
                    'borrowing_department_id' => $interDepartmentLoan->borrowing_department_id,
                    'quantity_borrowed' => $interDepartmentLoan->quantity_requested,
                    'borrowed_date' => now()->toDateString(),
                    'expected_return_date' => $interDepartmentLoan->expected_return_date,
                    'status' => 'active',
                    'borrowed_by' => $interDepartmentLoan->requested_by,
                ]);
            } else {
                foreach ($requestItems as $ri) {
                    InterDepartmentBorrowedItem::create([
                        'inter_department_loan_request_id' => $interDepartmentLoan->id,
                        'issued_item_id' => $ri->issued_item_id,
                        'lending_department_id' => $interDepartmentLoan->lending_department_id,
                        'borrowing_department_id' => $interDepartmentLoan->borrowing_department_id,
                        'quantity_borrowed' => $ri->quantity_requested,
                        'borrowed_date' => now()->toDateString(),
                        'expected_return_date' => $interDepartmentLoan->expected_return_date,
                        'status' => 'active',
                        'borrowed_by' => $interDepartmentLoan->requested_by,
                    ]);
                }
            }

            // Do not update total issued item quantity; availability is computed dynamically
            // Completion handled when all borrowed items are returned
        });

        return redirect()->route('loan-requests.index', ['tab' => 'inter'])
            ->with('success', 'Request approved successfully. Items issued and status set to Borrowed.');
    }

    /**
     * Decline request (can be done at any stage)
     */
    public function decline(Request $request, InterDepartmentLoanRequest $interDepartmentLoan)
    {
        $user = Auth::user();
        
        // Check permissions based on current status
        $canDecline = false;
        if ($interDepartmentLoan->isPending() && ($user->hasRole('dean') || $user->department_id === $interDepartmentLoan->lending_department_id)) {
            $canDecline = true;
        } elseif ($interDepartmentLoan->isDeanApproved() && $user->department_id === $interDepartmentLoan->lending_department_id) {
            $canDecline = true;
        } elseif ($interDepartmentLoan->isLendingApproved() && $user->hasAdminPrivileges()) {
            $canDecline = true;
        }

        if (!$canDecline) {
            return redirect()->back()->with('error', 'You do not have permission to decline this request at this stage.');
        }

        $request->validate([
            'decline_reason' => 'required|string|max:1000',
        ]);

        $interDepartmentLoan->decline($user, $request->decline_reason);
        // Record decline in approval history
        InterDepartmentLoanApprovalLog::create([
            'inter_department_loan_request_id' => $interDepartmentLoan->id,
            'approver_id' => $user->id,
            'approver_role' => optional($user->role)->name,
            'action' => 'declined',
            'notes' => $request->decline_reason,
        ]);

        return redirect()->route('loan-requests.index', ['tab' => 'inter'])
            ->with('success', 'Request declined successfully.');
    }
 
    /**
     * Initiate a return for an inter-department borrowed item (sets status to return_pending).
     * Optional photo upload is supported.
     */
    public function initiateReturn(Request $request, InterDepartmentLoanRequest $interDepartmentLoan)
    {
        $user = Auth::user();

        // Authorization: only borrower or admin can initiate return
        if ($user->id !== $interDepartmentLoan->requested_by && !$user->hasAdminPrivileges()) {
            return redirect()->back()->with('error', 'You are not authorized to initiate this return.');
        }

        // Multi-item payload support; fallback to legacy single-item fields
        $isMulti = is_array($request->input('items')) && count($request->input('items')) > 0;

        if ($isMulti) {
            $request->validate([
                'return_notes' => 'nullable|string|max:1000',
                'return_photo' => 'nullable|image|max:5120',
                'items' => 'required|array|min:1',
                'items.*.borrowed_item_id' => 'required|integer|exists:inter_department_borrowed_items,id',
                'items.*.quantity_returned' => 'required|integer|min:1',
                'items.*.is_damaged' => 'nullable|boolean',
                'items.*.damaged_count' => 'nullable|integer|min:0',
                'items.*.missing_count' => 'nullable|integer|min:0',
                'items.*.damage_severity' => 'nullable|string|in:minor,moderate,severe,total_loss',
                'items.*.damage_description' => 'nullable|string|max:2000',
            ]);

            $photoPath = null;
            if ($request->hasFile('return_photo')) {
                $photoPath = $request->file('return_photo')->store('returns', 'public');
            }

            $borrowedItems = $interDepartmentLoan->interDepartmentBorrowedItems()->get()->keyBy('id');

            \DB::transaction(function () use ($request, $user, $photoPath, $borrowedItems, $interDepartmentLoan) {
                foreach ($request->items as $item) {
                    $bid = intval($item['borrowed_item_id']);
                    if (!$borrowedItems->has($bid)) {
                        throw new \Illuminate\Validation\ValidationException(validator([], []),
                            redirect()->back()->withErrors(['items' => 'Invalid item selected for return.'])->withInput());
                    }
                    $bi = $borrowedItems->get($bid);
                    if (!in_array($bi->status, ['active', 'overdue', 'return_pending'], true)) {
                        throw new \Illuminate\Validation\ValidationException(validator([], []),
                            redirect()->back()->withErrors(['items' => 'Item is not eligible for return.'])->withInput());
                    }

                    $qty = intval($item['quantity_returned']);
                    if ($qty < 1 || $qty > $bi->quantity_borrowed) {
                        throw new \Illuminate\Validation\ValidationException(validator([], []),
                            redirect()->back()->withErrors(['items' => 'Invalid return quantity for selected item.'])->withInput());
                    }

                    $damagedCount = isset($item['damaged_count']) ? intval($item['damaged_count']) : 0;
                    $isDamaged = isset($item['is_damaged']) ? (bool)$item['is_damaged'] : ($damagedCount > 0);
                    $severity = $item['damage_severity'] ?? null;
                    if (!$isDamaged || $damagedCount === 0) {
                        $severity = null;
                    }

                    // Set item to return_pending
                    $bi->update([
                        'status' => 'return_pending',
                        'return_notes' => $request->return_notes,
                    ]);

                    InterDepartmentReturnRecord::create([
                        'inter_department_borrowed_item_id' => $bi->id,
                        'quantity_returned' => $qty,
                        'is_damaged' => $isDamaged,
                        'initiated_by' => $user->id,
                        'notes' => $request->return_notes,
                        'photo_path' => $photoPath,
                        'missing_count' => $item['missing_count'] ?? null,
                        'damaged_count' => $damagedCount ?: null,
                        'damage_severity' => $severity,
                        'damage_description' => $item['damage_description'] ?? null,
                    ]);
                }

                $interDepartmentLoan->update(['status' => 'return_pending']);
            });
        } else {
            // Legacy single-item flow
            $borrowedItem = $interDepartmentLoan->interDepartmentBorrowedItems()
                ->whereIn('status', ['active', 'overdue'])
                ->first();

            if (!$borrowedItem) {
                return redirect()->back()->with('error', 'No active borrowed item found to return.');
            }

            $request->validate([
                'return_notes' => 'nullable|string|max:1000',
                'return_photo' => 'nullable|image|max:5120', // up to 5MB
                'missing_count' => 'nullable|integer|min:0',
                'damaged_count' => 'nullable|integer|min:0',
                'damage_severity' => 'nullable|string|in:minor,moderate,severe,total_loss',
            ]);

            $borrowedItem->initiateReturn($user, $request->return_notes);

            $photoPath = null;
            if ($request->hasFile('return_photo')) {
                $photoPath = $request->file('return_photo')->store('returns', 'public');
            }

            $severity = $request->damage_severity;
            $damaged = $request->input('damaged_count');
            if (empty($damaged) || intval($damaged) === 0) {
                $severity = null;
            }

            InterDepartmentReturnRecord::create([
                'inter_department_borrowed_item_id' => $borrowedItem->id,
                'quantity_returned' => $borrowedItem->quantity_borrowed,
                'is_damaged' => !empty($damaged) && intval($damaged) > 0,
                'initiated_by' => $user->id,
                'notes' => $request->return_notes,
                'photo_path' => $photoPath,
                'missing_count' => $request->input('missing_count'),
                'damaged_count' => $damaged,
                'damage_severity' => $severity,
            ]);

            $interDepartmentLoan->update(['status' => 'return_pending']);
        }

        // Notify lending department dean(s) and admins that a return was initiated
        try {
            // Notify lending department deans
            $lendingDeans = User::whereHas('role', function($query) {
                $query->where('name', 'dean');
            })->where('department_id', $interDepartmentLoan->lending_department_id)->get();

            foreach ($lendingDeans as $dean) {
                $dean->notify(new InterDepartmentLoanNotification($interDepartmentLoan, 'return_initiated'));
            }

            // Notify admins and super admins with full return details
            $adminUsers = User::whereHas('role', function($query) {
                $query->whereIn('name', ['admin', 'super_admin']);
            })->get();

            foreach ($adminUsers as $admin) {
                // Notification still works; email template reads latest record details
                $admin->notify(new \App\Notifications\InterDepartmentReturnNotification($interDepartmentLoan, InterDepartmentReturnRecord::whereIn('inter_department_borrowed_item_id', $interDepartmentLoan->interDepartmentBorrowedItems->pluck('id'))->latest()->first()));
            }
        } catch (\Throwable $e) {
            // Silently continue; notification failures shouldn't block return initiation
            \Log::warning('Failed to dispatch return initiation notifications: '.$e->getMessage());
        }

        return redirect()->route('loan-requests.index', ['tab' => 'inter'])
            ->with('success', 'Return initiated. Status set to Return Pending for verification.');
    }

    /**
     * Verify a pending return (lending department or admin finalizes to returned).
     */
    public function verifyReturn(Request $request, InterDepartmentLoanRequest $interDepartmentLoan)
    {
        $user = Auth::user();

        // Authorization: lending department dean or admin
        if (!$user->isDeanOf($interDepartmentLoan->lendingDepartment) && !$user->hasAdminPrivileges()) {
            return redirect()->back()->with('error', 'Only lending department dean or admin can verify returns.');
        }
        // Collect all items pending return under this loan
        $pendingItems = $interDepartmentLoan->interDepartmentBorrowedItems()
            ->where('status', 'return_pending')
            ->get();

        if ($pendingItems->isEmpty()) {
            return redirect()->back()->with('error', 'No return pending items found to verify.');
        }

        $request->validate([
            'verification_notes' => 'nullable|string|max:1000',
        ]);

        foreach ($pendingItems as $borrowedItem) {
            // Fetch latest unverified return record for the item
            $lastRecord = InterDepartmentReturnRecord::where('inter_department_borrowed_item_id', $borrowedItem->id)
                ->orderBy('id', 'desc')
                ->first();

            // Decide final status per item based on record
            if ($lastRecord && ($lastRecord->is_damaged || ($lastRecord->damaged_count ?? 0) > 0)) {
                $borrowedItem->markAsDamaged($user, $request->verification_notes);
            } else {
                $borrowedItem->markAsReturned($user, $request->verification_notes);
            }

            if ($lastRecord) {
                $lastRecord->update([
                    'verified_by' => $user->id,
                    'verified_at' => now(),
                ]);
            }
        }

        // Do not modify total issued item quantity on return; availability is computed dynamically

        // If all items are returned (no active/overdue/return_pending), mark loan as completed
        $remaining = $interDepartmentLoan->interDepartmentBorrowedItems()
            ->whereIn('status', ['active', 'overdue', 'return_pending'])
            ->exists();
        if (!$remaining) {
            $interDepartmentLoan->complete();
        }

        return redirect()->route('loan-requests.index', ['tab' => 'inter'])
            ->with('success', 'Return verified and item marked as returned.');
    }

    /**
     * Show dedicated multi-item return form page.
     */
    public function returnForm(InterDepartmentLoanRequest $interDepartmentLoan)
    {
        $user = Auth::user();
        if ($user->id !== $interDepartmentLoan->requested_by && !$user->hasAdminPrivileges()) {
            return redirect()->route('loan-requests.index', ['tab' => 'inter'])
                ->with('error', 'You are not authorized to initiate this return.');
        }

        $activeItems = $interDepartmentLoan->interDepartmentBorrowedItems()->whereIn('status', ['active','overdue'])->get();
        return view('inter_department_loans.return_form', compact('interDepartmentLoan', 'activeItems'));
    }

    /**
     * Update a pending return record (allow borrower/admin to edit details before verification).
     */
    public function updateReturn(Request $request, InterDepartmentLoanRequest $interDepartmentLoan, \App\Models\InterDepartmentReturnRecord $returnRecord)
    {
        $user = Auth::user();

        // Authorization: only borrower or admin can edit their pending return
        if ($user->id !== $interDepartmentLoan->requested_by && !$user->hasAdminPrivileges()) {
            return redirect()->back()->with('error', 'You are not authorized to edit this return request.');
        }

        // Ensure the loan is in a state that allows editing of the return
        if (!$interDepartmentLoan->isReturnPending() && !$interDepartmentLoan->isBorrowed()) {
            return redirect()->back()->with('error', 'Return can only be edited while borrowed or pending verification.');
        }

        // Ensure the record belongs to this loan and is not yet verified
        $borrowedItemIds = $interDepartmentLoan->interDepartmentBorrowedItems()->pluck('id')->toArray();
        if (!in_array($returnRecord->inter_department_borrowed_item_id, $borrowedItemIds, true)) {
            return redirect()->back()->with('error', 'Return record does not belong to this loan request.');
        }

        if ($returnRecord->verified_at) {
            return redirect()->back()->with('error', 'Verified returns cannot be edited.');
        }

        $request->validate([
            'return_notes' => 'nullable|string|max:1000',
            'return_photo' => 'nullable|image|max:5120',
            'missing_count' => 'nullable|integer|min:0',
            'damaged_count' => 'nullable|integer|min:0',
            'damage_severity' => 'nullable|string|in:minor,moderate,severe,total_loss',
        ]);

        // Handle optional photo update
        $photoPath = $returnRecord->photo_path;
        if ($request->hasFile('return_photo')) {
            $photoPath = $request->file('return_photo')->store('returns', 'public');
        }

        // Normalize severity: only store when there are damaged items
        $damaged = $request->input('damaged_count');
        $severity = $request->input('damage_severity');
        if (empty($damaged) || intval($damaged) === 0) {
            $severity = null;
        }

        $returnRecord->update([
            'notes' => $request->input('return_notes'),
            'photo_path' => $photoPath,
            'missing_count' => $request->input('missing_count'),
            'damaged_count' => $damaged,
            'damage_severity' => $severity,
        ]);

        return redirect()->route('loan-requests.index', ['tab' => 'inter'])
            ->with('success', 'Return request updated successfully.');
    }
}
