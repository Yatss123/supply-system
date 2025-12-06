<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\LoanRequest;
use App\Models\InterDepartmentLoanRequest;
use App\Models\LoanRequestBatch;
use App\Models\Supply;
use App\Models\Department;
use App\Models\BorrowedItem;
use App\Models\BorrowedItemLog;
use App\Http\Requests\StoreLoanRequestRequest;
use App\Http\Requests\UpdateLoanRequestRequest;
use App\Http\Requests\ApproveLoanRequestRequest;
use App\Http\Requests\DeclineLoanRequestRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class LoanRequestController extends Controller
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
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'standard');

        $user = Auth::user();
        if (!$user->relationLoaded('role')) {
            $user->load('role');
        }

        $loanRequests = null;
        $interDeptRequests = null;

        if ($tab === 'standard') {
            $query = LoanRequest::with(['supply', 'department', 'requestedBy', 'approvedBy', 'batch']);

            if ($request->has('status') && $request->status !== '') {
                $query->where('status', $request->status);
            }

            if (!$user->hasAdminPrivileges()) {
                $query->where('department_id', $user->department_id);
                if ($user->hasRole('dean')) {
                    $query->whereHas('requestedBy', function ($uq) use ($user) {
                        $uq->where('department_id', $user->department_id);
                    });
                }
            }

            if ($user->hasRole('student') || $user->hasRole('adviser')) {
                $query->where('requested_by', $user->id);
            }

            if ($user->hasAdminPrivileges()) {
                $query->where(function ($q) {
                    $q->where('status', '!=', 'pending')
                      ->orWhereNotNull('dean_approved_at')
                      ->orWhereHas('requestedBy', function ($uq) {
                          $uq->whereHas('role', function ($rq) {
                              $rq->where('name', 'dean');
                          });
                      });
                });
            }

            if ($request->has('search') && $request->search !== '') {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->whereHas('supply', function($sq) use ($search) {
                        $sq->where('name', 'LIKE', "%{$search}%");
                    })->orWhereHas('department', function($dq) use ($search) {
                        $dq->where('department_name', 'LIKE', "%{$search}%");
                    })->orWhere('purpose', 'LIKE', "%{$search}%");
                });
            }

            $loanRequests = $query->orderBy('created_at', 'desc')->paginate(10);
        } elseif ($tab === 'inter') {
            $interQ = InterDepartmentLoanRequest::with([
                'issuedItem.supply',
                'requestItems.issuedItem.supply',
                'lendingDepartment',
                'borrowingDepartment',
                'requestedBy'
            ]);

            if ($request->has('status') && $request->status !== '') {
                $interQ->where('status', $request->status);
            }

            if ($request->has('search') && $request->search !== '') {
                $search = $request->search;
                $interQ->where(function ($q) use ($search) {
                    $q->whereHas('issuedItem.supply', function ($sq) use ($search) {
                        $sq->where('name', 'LIKE', "%{$search}%");
                    })->orWhereHas('requestItems.issuedItem.supply', function ($sq) use ($search) {
                        $sq->where('name', 'LIKE', "%{$search}%");
                    })->orWhereHas('lendingDepartment', function ($dq) use ($search) {
                        $dq->where(function ($nameQ) use ($search) {
                            $nameQ->where('department_name', 'LIKE', "%{$search}%")
                                  ->orWhere('name', 'LIKE', "%{$search}%");
                        });
                    })->orWhereHas('borrowingDepartment', function ($dq) use ($search) {
                        $dq->where(function ($nameQ) use ($search) {
                            $nameQ->where('department_name', 'LIKE', "%{$search}%")
                                  ->orWhere('name', 'LIKE', "%{$search}%");
                        });
                    })->orWhereHas('requestedBy', function ($rq) use ($search) {
                        $rq->where('name', 'LIKE', "%{$search}%");
                    });
                });
            }

            if ($user->role && $user->role->name !== 'Super Admin') {
                if ($user->hasAdminPrivileges()) {
                    // Admin: no extra restrictions
                } else {
                    if ($user->hasRole('student') || $user->hasRole('adviser')) {
                        $interQ->where('requested_by', $user->id);
                    } elseif ($user->hasRole('dean')) {
                        $interQ->where(function ($q) use ($user) {
                            $q->whereHas('requestedBy', function ($subQ) use ($user) {
                                $subQ->where('department_id', $user->department_id);
                            })
                            ->orWhere('lending_department_id', $user->department_id);
                        });
                    } else {
                        $interQ->where(function ($q) use ($user) {
                            $q->where('lending_department_id', $user->department_id)
                              ->orWhere('borrowing_department_id', $user->department_id);
                        });
                    }
                }
            }

            $interDeptRequests = $interQ->orderBy('created_at', 'desc')->paginate(10, ['*'], 'inter_page');
        }

        return view('loan_requests.index', compact('loanRequests', 'interDeptRequests', 'tab'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $supplies = Supply::active()->borrowable()->get();
        $departments = Department::all();
        $currentUser = Auth::user();
        
        return view('loan_requests.create', compact('supplies', 'departments', 'currentUser'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Delegate to inter-department flow when discriminator is provided
        if ($request->input('request_type') === 'inter_department') {
            return app(\App\Http\Controllers\InterDepartmentLoanController::class)->store($request);
        }

        // Preserve existing validation for standard loan requests using FormRequest rules/messages
        $form = app(\App\Http\Requests\StoreLoanRequestRequest::class);
        $request->validate($form->rules(), $form->messages(), $form->attributes());

        $createdCount = 0;
        $firstCreated = null;

        // Unified JSON payload 'request' with per-item supply_id and quantity
        $payloadJson = $request->input('request');
        $items = [];
        try {
            $items = json_decode($payloadJson, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            return back()->withErrors(['request' => 'Invalid request payload.'])->withInput();
        }

        if (!is_array($items) || empty($items)) {
            return back()->withErrors(['request' => 'Request must include at least one item.'])->withInput();
        }

        // Create batch and items atomically to maintain integrity
        DB::beginTransaction();
        try {
            $batch = LoanRequestBatch::create([
                'user_id' => Auth::id(),
                'department_id' => $request->department_id,
                'purpose' => $request->purpose,
                'needed_from_date' => $request->needed_from_date,
                'expected_return_date' => $request->expected_return_date,
                'status' => 'pending',
            ]);

            foreach ($items as $item) {
                $lr = LoanRequest::create([
                    'supply_id' => $item['supply_id'],
                    'supply_variant_id' => isset($item['variant_id']) ? (int) $item['variant_id'] : null,
                    'department_id' => $request->department_id,
                    'requested_by' => Auth::id(),
                    'batch_id' => $batch->id,
                    'quantity_requested' => (int) $item['quantity'],
                    'needed_from_date' => $request->needed_from_date,
                    'purpose' => $request->purpose,
                    'expected_return_date' => $request->expected_return_date,
                    'status' => 'pending'
                ]);
                if (!$firstCreated) { $firstCreated = $lr; }
                $createdCount++;
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['request' => 'Failed to submit loan requests. Please try again.'])->withInput();
        }
        // Redirect to consolidated details via the first created item
        if ($firstCreated) {
            return redirect()->route('loan-requests.show', $firstCreated)
                ->with('success', 'Submitted ' . $createdCount . ' loan request item(s) in a single request.');
        }
        return redirect()->route('loan-requests.index', ['tab' => 'standard'])
            ->with('success', 'Submitted ' . $createdCount . ' loan request item(s).');
    }

    /**
     * Display the specified resource.
     */
    public function show(LoanRequest $loanRequest)
    {
        $loanRequest->load([
            'supply',
            'variant',
            'department',
            'requestedBy',
            'approvedBy',
            'borrowedItem',
            // Eager-load both alias relations for batch items to ensure variant visibility
            'batch.items.supply',
            'batch.items.variant',
            'batch.items.department',
            'batch.items.requestedBy',
            'batch.loanRequests.supply',
            'batch.loanRequests.variant',
        ]);

        // Prevent admin users from viewing requests that have not passed dean approval
        if (Auth::user()->hasAdminPrivileges()) {
            if ($loanRequest->isPending() && $loanRequest->needsDeanApproval() && !$loanRequest->isDeanApproved()) {
                return redirect()->route('loan-requests.index', ['tab' => 'standard'])
                    ->with('error', 'This loan request is waiting for dean approval and is not available for admin review.');
            }
        }
        
        return view('loan_requests.show', compact('loanRequest'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LoanRequest $loanRequest)
    {
        // Only allow editing if request is pending and user is the requester or admin
        if (!$loanRequest->isPending() || 
            (!Auth::user()->hasAdminPrivileges() && $loanRequest->requested_by !== Auth::id())) {
            return redirect()->route('loan-requests.index', ['tab' => 'standard'])
                ->with('error', 'You cannot edit this loan request.');
        }
        
        $supplies = Supply::active()->borrowable()->get();
        $departments = Department::all();
        
        return view('loan_requests.edit', compact('loanRequest', 'supplies', 'departments'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateLoanRequestRequest $request, LoanRequest $loanRequest)
    {
        // Only allow updating if request is pending
        if (!$loanRequest->isPending()) {
            return redirect()->route('loan-requests.index', ['tab' => 'standard'])
                ->with('error', 'Cannot update a processed loan request.');
        }

        $loanRequest->update([
            'supply_id' => $request->supply_id,
            'department_id' => $request->department_id,
            'quantity_requested' => $request->quantity_requested,
            'purpose' => $request->purpose,
            'expected_return_date' => $request->expected_return_date
        ]);

        return redirect()->route('loan-requests.show', $loanRequest)
            ->with('success', 'Loan request updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LoanRequest $loanRequest)
    {
        // Only allow deletion if request is pending or declined
        if (!in_array($loanRequest->status, ['pending', 'declined'])) {
            return redirect()->route('loan-requests.index')
                ->with('error', 'Cannot delete a processed loan request.');
        }

        $loanRequest->delete();

        return redirect()->route('loan-requests.index', ['tab' => 'standard'])
            ->with('success', 'Loan request deleted successfully.');
    }

    /**
     * Dean approve a loan request
     */
    public function deanApprove(Request $request, LoanRequest $loanRequest)
    {
        $request->validate([
            'dean_approval_notes' => 'nullable|string|max:1000',
        ]);

        if (!$loanRequest->needsDeanApproval()) {
            return redirect()->route('loan-requests.show', $loanRequest)
                ->with('error', 'This loan request does not need dean approval.');
        }

        if ($loanRequest->isDeanApproved()) {
            return redirect()->route('loan-requests.show', $loanRequest)
                ->with('error', 'This loan request has already been approved by the dean.');
        }

        $loanRequest->deanApprove(Auth::user(), $request->dean_approval_notes);

        // Redirect back to consolidated index for aligned flow
        return redirect()->route('loan-requests.index', ['tab' => 'standard'])
            ->with('success', 'Loan request approved by dean successfully.');
    }

    /**
     * Approve a loan request
     */
    public function approve(ApproveLoanRequestRequest $request, LoanRequest $loanRequest)
    {
        // Create borrowed item record
        $borrowedItem = BorrowedItem::create([
            'supply_id' => $loanRequest->supply_id,
            'department_id' => $loanRequest->department_id,
            'user_id' => $loanRequest->requested_by,
            'quantity' => $loanRequest->quantity_requested,
            'borrowed_at' => now()
        ]);

        // Do not update total supply quantity; availability is computed dynamically

        // Approve the loan request and link the borrowed item (status remains 'approved')
        $loanRequest->approve(Auth::id(), $request->approval_notes);
        $loanRequest->update([
            'borrowed_item_id' => $borrowedItem->id,
        ]);

        return redirect()->route('loan-requests.index', ['tab' => 'standard'])
            ->with('success', 'Loan request approved and items issued successfully.');
    }

    /**
     * Issue items for an already approved loan request that lacks a BorrowedItem.
     */
    public function issue(LoanRequest $loanRequest)
    {
        // Only allow issuing if request is approved and not yet linked to a borrowed item
        if (!$loanRequest->isApproved()) {
            return redirect()->route('loan-requests.index', ['tab' => 'standard'])
                ->with('error', 'Items can only be issued after admin approval.');
        }

        if ($loanRequest->borrowed_item_id) {
            return redirect()->route('loan-requests.index', ['tab' => 'standard'])
                ->with('info', 'Items have already been issued for this request.');
        }

        // Create borrowed item record
        $borrowedItem = BorrowedItem::create([
            'supply_id' => $loanRequest->supply_id,
            'department_id' => $loanRequest->department_id,
            'user_id' => $loanRequest->requested_by,
            'quantity' => $loanRequest->quantity_requested,
            'borrowed_at' => now()
        ]);

        // Link the borrowed item to the loan request (status remains 'approved')
        $loanRequest->update([
            'borrowed_item_id' => $borrowedItem->id,
        ]);

        return redirect()->route('loan-requests.index', ['tab' => 'standard'])
            ->with('success', 'Items issued successfully.');
    }

    /**
     * Decline a loan request
     */
    public function decline(DeclineLoanRequestRequest $request, LoanRequest $loanRequest)
    {
        $loanRequest->decline($request->decline_reason);

        return redirect()->route('loan-requests.index', ['tab' => 'standard'])
            ->with('success', 'Loan request declined.');
    }

    /**
     * Get available quantity for a supply (AJAX)
     */
    public function getAvailableQuantity($supplyId)
    {
        $supply = Supply::find($supplyId);
        if ($supply) {
            return response()->json(['quantity' => $supply->availableQuantity()]);
        }
        return response()->json(['error' => 'Supply not found'], 404);
    }

    /**
     * Store a new resource in storage, scoped to a specific supply.
     * Accepts the same consolidated JSON payload as store(), but enforces all items
     * reference the given {supply} path parameter.
     */
    public function storeForSupply(Request $request, Supply $supply)
    {
        // Delegate to inter-department flow when discriminator is provided
        if ($request->input('request_type') === 'inter_department') {
            return app(\App\Http\Controllers\InterDepartmentLoanController::class)->store($request);
        }

        // Validate using the standard form request
        $form = app(\App\Http\Requests\StoreLoanRequestRequest::class);
        $request->validate($form->rules(), $form->messages(), $form->attributes());

        // Parse items
        $payloadJson = $request->input('request');
        $items = [];
        try {
            $items = json_decode($payloadJson, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            return back()->withErrors(['request' => 'Invalid request payload.'])->withInput();
        }

        if (!is_array($items) || empty($items)) {
            return back()->withErrors(['request' => 'Request must include at least one item.']).withInput();
        }

        // Ensure all items reference the same supply as the route parameter
        foreach ($items as $idx => $item) {
            if ((int) data_get($item, 'supply_id') !== (int) $supply->id) {
                return back()->withErrors(['request' => 'Item #' . ($idx + 1) . ' does not reference the selected supply.']).withInput();
            }
        }

        $createdCount = 0;
        $firstCreated = null;
        DB::beginTransaction();
        try {
            $batch = LoanRequestBatch::create([
                'user_id' => Auth::id(),
                'department_id' => $request->department_id,
                'purpose' => $request->purpose,
                'needed_from_date' => $request->needed_from_date,
                'expected_return_date' => $request->expected_return_date,
                'status' => 'pending',
            ]);

            foreach ($items as $item) {
                $lr = LoanRequest::create([
                    'supply_id' => $supply->id,
                    'department_id' => $request->department_id,
                    'requested_by' => Auth::id(),
                    'batch_id' => $batch->id,
                    'quantity_requested' => (int) $item['quantity'],
                    'needed_from_date' => $request->needed_from_date,
                    'purpose' => $request->purpose,
                    'expected_return_date' => $request->expected_return_date,
                    'status' => 'pending'
                ]);
                if (!$firstCreated) { $firstCreated = $lr; }
                $createdCount++;
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['request' => 'Failed to submit loan requests for the selected supply. Please try again.'])->withInput();
        }
        // Redirect to consolidated details via the first created item
        if ($firstCreated) {
            return redirect()->route('loan-requests.show', $firstCreated)
                ->with('success', 'Submitted ' . $createdCount . ' item(s) for supply: ' . ($supply->name ?? ('ID ' . $supply->id)) . '.');
        }
        return redirect()->route('loan-requests.index', ['tab' => 'standard'])
            ->with('success', 'Submitted ' . $createdCount . ' item(s) for supply: ' . ($supply->name ?? ('ID ' . $supply->id)) . '.');
    }

    /**
     * Show the bulk return form for standard loan requests (batch-aware).
     */
    public function returnForm(LoanRequest $loanRequest)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('loan-requests.index', ['tab' => 'standard'])
                ->with('error', 'Authentication required.');
        }

        // Authorization: borrower or admin
        if ($user->id !== ($loanRequest->requested_by ?? null) && !$user->hasAdminPrivileges()) {
            return redirect()->route('loan-requests.show', $loanRequest)
                ->with('error', 'You are not authorized to initiate returns for this request.');
        }

        // Collect active borrowed items under the same batch (or just this item if no batch)
        $activeItems = collect();
        if ($loanRequest->batch) {
            $borrowedIds = $loanRequest->batch->loanRequests()
                ->whereNotNull('borrowed_item_id')
                ->pluck('borrowed_item_id')
                ->filter()
                ->all();
            if (!empty($borrowedIds)) {
                $activeItems = BorrowedItem::with(['supply', 'loanRequest.variant'])
                    ->whereIn('id', $borrowedIds)
                    ->whereNull('returned_at')
                    ->whereNull('return_pending_at')
                    ->get();
            }
        } else {
            if ($loanRequest->borrowed_item_id) {
                $bi = BorrowedItem::with(['supply', 'loanRequest.variant'])->find($loanRequest->borrowed_item_id);
                if ($bi && is_null($bi->returned_at) && is_null($bi->return_pending_at)) {
                    $activeItems = collect([$bi]);
                }
            }
        }

        return view('loan_requests.return_form', [
            'loanRequest' => $loanRequest,
            'activeItems' => $activeItems,
        ]);
    }

    /**
     * Initiate a bulk return for standard borrowed items (sets return_pending for each selected item).
     */
    public function initiateReturn(Request $request, LoanRequest $loanRequest)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('loan-requests.index', ['tab' => 'standard'])
                ->with('error', 'Authentication required.');
        }

        // Authorization: borrower or admin
        if ($user->id !== ($loanRequest->requested_by ?? null) && !$user->hasAdminPrivileges()) {
            return redirect()->route('loan-requests.show', $loanRequest)
                ->with('error', 'You are not authorized to initiate returns for this request.');
        }

        $isMulti = is_array($request->input('items')) && count($request->input('items')) > 0;
        if (!$isMulti) {
            return redirect()->back()->withErrors(['items' => 'Please provide at least one item to return.']);
        }

        $request->validate([
            'return_notes' => 'nullable|string|max:1000',
            'return_photo' => 'nullable|image|max:5120',
            'items' => 'required|array|min:1',
            'items.*.borrowed_item_id' => 'required|integer|exists:borrowed_items,id',
            'items.*.quantity_returned' => 'required|integer|min:1',
            'items.*.is_damaged' => 'nullable|boolean',
            'items.*.missing_count' => 'nullable|integer|min:0',
            'items.*.damaged_count' => 'nullable|integer|min:0',
            'items.*.damage_severity' => 'nullable|string|in:minor,moderate,severe,total_loss',
            'items.*.damage_description' => 'nullable|string|max:2000',
        ]);

        $photoPath = null;
        if ($request->hasFile('return_photo')) {
            $photoPath = $request->file('return_photo')->store('returns', 'public');
        }

        // Build set of eligible borrowed items under the same batch
        $eligible = collect();
        if ($loanRequest->batch) {
            $borrowedIds = $loanRequest->batch->loanRequests()
                ->whereNotNull('borrowed_item_id')
                ->pluck('borrowed_item_id')
                ->filter()
                ->all();
            if (!empty($borrowedIds)) {
                $eligible = BorrowedItem::with(['supply'])
                    ->whereIn('id', $borrowedIds)
                    ->whereNull('returned_at')
                    ->whereNull('return_pending_at')
                    ->get()
                    ->keyBy('id');
            }
        } else {
            if ($loanRequest->borrowed_item_id) {
                $bi = BorrowedItem::with(['supply'])->find($loanRequest->borrowed_item_id);
                if ($bi && is_null($bi->returned_at) && is_null($bi->return_pending_at)) {
                    $eligible = collect([$bi])->keyBy('id');
                }
            }
        }

        if ($eligible->isEmpty()) {
            return redirect()->back()->with('error', 'No active borrowed items found to return.');
        }

        \DB::beginTransaction();
        try {
            foreach ($request->items as $item) {
                $bid = intval($item['borrowed_item_id']);
                if (!$eligible->has($bid)) {
                    throw new \Illuminate\Validation\ValidationException(validator([], []),
                        redirect()->back()->withErrors(['items' => 'Invalid item selected for return.'])->withInput());
                }
                /** @var BorrowedItem $bi */
                $bi = $eligible->get($bid);
                $qty = intval($item['quantity_returned']);
                if ($qty < 1 || $qty > ($bi->quantity ?? 0)) {
                    throw new \Illuminate\Validation\ValidationException(validator([], []),
                        redirect()->back()->withErrors(['items' => 'Invalid quantity returned for item ID ' . $bid . '.'])->withInput());
                }

                $missing = intval($item['missing_count'] ?? 0);
                $damaged = intval($item['damaged_count'] ?? 0);
                $severity = $item['damage_severity'] ?? null;
                $desc = $item['damage_description'] ?? null;

                // Compose a per-item note summarizing the provided details
                $details = [];
                $details[] = 'Returned: ' . $qty;
                if ($missing > 0) { $details[] = 'Missing: ' . $missing; }
                if ($damaged > 0) { $details[] = 'Damaged: ' . $damaged; }
                if ($severity) { $details[] = 'Severity: ' . $severity; }
                if ($desc) { $details[] = 'Desc: ' . trim($desc); }
                $combinedNote = trim(($request->return_notes ?? '') . (count($details) ? (' | ' . implode(', ', $details)) : ''));

                // Flag return pending on the borrowed item
                $bi->returnItem($combinedNote, $photoPath);

                // Log return initiation (retain returned quantity for clarity)
                BorrowedItemLog::create([
                    'borrowed_item_id' => $bi->id,
                    'user_id' => $user->id,
                    'action' => 'return_pending',
                    'quantity' => $qty,
                    'notes' => $combinedNote,
                    'photo_path' => $photoPath,
                ]);
            }
            \DB::commit();
        } catch (\Throwable $e) {
            \DB::rollBack();
            return redirect()->back()->withErrors(['items' => 'Failed to submit returns: ' . $e->getMessage()])->withInput();
        }

        return redirect()->route('loan-requests.show', $loanRequest)
            ->with('success', 'Return submitted for admin verification for selected item(s).');
    }

    /**
     * Verify all pending returns for the given loan request's batch (admin-only).
     * This marks each pending BorrowedItem as returned and logs the verification.
     */
    public function verifyReturnBulk(Request $request, LoanRequest $loanRequest)
    {
        $user = Auth::user();
        if (!$user || !$user->hasAdminPrivileges()) {
            return redirect()->back()->with('error', 'Only admin can verify returns.');
        }

        $request->validate([
            'verification_notes' => 'nullable|string|max:1000',
        ]);

        // Collect borrowed items under this request or its batch that are pending verification
        $pendingItems = collect();
        if ($loanRequest->batch) {
            $borrowedIds = $loanRequest->batch->loanRequests()
                ->whereNotNull('borrowed_item_id')
                ->pluck('borrowed_item_id')
                ->filter()
                ->all();
            if (!empty($borrowedIds)) {
                $pendingItems = BorrowedItem::whereIn('id', $borrowedIds)
                    ->whereNull('returned_at')
                    ->whereNotNull('return_pending_at')
                    ->get();
            }
        } else {
            if ($loanRequest->borrowed_item_id) {
                $bi = BorrowedItem::find($loanRequest->borrowed_item_id);
                if ($bi && is_null($bi->returned_at) && !is_null($bi->return_pending_at)) {
                    $pendingItems = collect([$bi]);
                }
            }
        }

        if ($pendingItems->isEmpty()) {
            return redirect()->back()->with('error', 'No pending return items found to verify.');
        }

        DB::beginTransaction();
        try {
            foreach ($pendingItems as $borrowedItem) {
                // Mark item as returned and attach verification notes
                $borrowedItem->verifyReturnWithStatus($user, 'returned', null, null, $request->verification_notes);

                // Log verified return
                BorrowedItemLog::create([
                    'borrowed_item_id' => $borrowedItem->id,
                    'user_id' => $user->id,
                    'action' => 'verified_return',
                    'quantity' => $borrowedItem->quantity,
                    'notes' => $request->verification_notes,
                ]);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to verify returns: ' . $e->getMessage());
        }

        return redirect()->route('loan-requests.show', $loanRequest)
            ->with('success', 'Verified return for all pending item(s).');
    }

    /**
     * Verify selected pending returns with per-item statuses (admin-only).
     */
    public function verifyReturnSelected(Request $request, LoanRequest $loanRequest)
    {
        $user = Auth::user();
        if (!$user || !$user->hasAdminPrivileges()) {
            return redirect()->back()->with('error', 'Only admin can verify returns.');
        }

        $request->validate([
            'verification_notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.borrowed_item_id' => 'required|integer|exists:borrowed_items,id',
            'items.*.selected' => 'nullable|boolean',
            'items.*.status' => 'required|string|in:returned,returned_with_missing,returned_with_damage',
            'items.*.missing_count' => 'nullable|integer|min:0',
            'items.*.damaged_count' => 'nullable|integer|min:0',
        ]);

        // Build set of pending items under this request or its batch
        $pendingItems = collect();
        if ($loanRequest->batch) {
            $borrowedIds = $loanRequest->batch->loanRequests()
                ->whereNotNull('borrowed_item_id')
                ->pluck('borrowed_item_id')
                ->filter()
                ->all();
            if (!empty($borrowedIds)) {
                $pendingItems = BorrowedItem::whereIn('id', $borrowedIds)
                    ->whereNull('returned_at')
                    ->whereNotNull('return_pending_at')
                    ->get()
                    ->keyBy('id');
            }
        } else {
            if ($loanRequest->borrowed_item_id) {
                $bi = BorrowedItem::find($loanRequest->borrowed_item_id);
                if ($bi && is_null($bi->returned_at) && !is_null($bi->return_pending_at)) {
                    $pendingItems = collect([$bi])->keyBy('id');
                }
            }
        }

        if ($pendingItems->isEmpty()) {
            return redirect()->back()->with('error', 'No pending return items found to verify.');
        }

        DB::beginTransaction();
        try {
            foreach ($request->items as $item) {
                $selected = filter_var($item['selected'] ?? false, FILTER_VALIDATE_BOOLEAN);
                if (!$selected) { continue; }

                $bid = intval($item['borrowed_item_id']);
                if (!$pendingItems->has($bid)) { continue; }

                /** @var BorrowedItem $borrowedItem */
                $borrowedItem = $pendingItems->get($bid);
                $status = $item['status'];
                $missing = isset($item['missing_count']) ? intval($item['missing_count']) : null;
                $damaged = isset($item['damaged_count']) ? intval($item['damaged_count']) : null;

                $borrowedItem->verifyReturnWithStatus($user, $status, $missing, $damaged, $request->verification_notes);

                // Log verified return with status detail
                $action = $status === 'returned' ? 'verified_return'
                    : ($status === 'returned_with_missing' ? 'verified_return_missing' : 'verified_return_damaged');

                BorrowedItemLog::create([
                    'borrowed_item_id' => $borrowedItem->id,
                    'user_id' => $user->id,
                    'action' => $action,
                    'quantity' => $borrowedItem->quantity,
                    'notes' => $request->verification_notes,
                ]);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to verify selected returns: ' . $e->getMessage());
        }

        return redirect()->route('loan-requests.show', $loanRequest)
            ->with('success', 'Verified selected item(s) with appropriate status.');
    }
}
