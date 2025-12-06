<?php

namespace App\Http\Controllers;

use App\Models\Supply;
use App\Models\SupplyVariant;
use App\Models\IssuedItem;
use App\Models\LoanRequest;
use App\Models\InterDepartmentLoanRequest;
use App\Models\BorrowedItem;
use App\Models\InterDepartmentBorrowedItem;
use App\Models\StatusChangeRequest;
use App\Models\Department;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class QRActionController extends Controller
{
    /**
     * Display QR action menu for a supply
     */
    public function index(Supply $supply)
    {
        $user = Auth::user();
        
        // Check if user is authenticated
        if (!$user) {
        return redirect('/')->with('error', 'Please login to access QR actions.');
        }

        $supply->load(['variants']);
        
        // Check stock level and add warning if needed (skip for borrowable supplies)
        $stockWarning = null;
        if (!$supply->isBorrowable()) {
            if ($supply->quantity <= 0) {
                $stockWarning = 'This item is currently out of stock. Some actions may not be available.';
            } elseif ($supply->quantity <= $supply->minimum_stock_level) {
                $stockWarning = 'This item is running low on stock (Current: ' . $supply->quantity . ', Minimum: ' . $supply->minimum_stock_level . ').';
            }
        }
        
        // Determine available actions based on user role
        $availableActions = $this->getAvailableActions($user, $supply);
        
        return view('qr_actions.index', compact('supply', 'availableActions', 'stockWarning'));
    }

    /**
     * Quick Issue Items - Pre-fill issue form with supply
     */
    public function quickIssue(Supply $supply)
    {
        $user = Auth::user();
        
        // Only Admin and Super Admin can issue items
        if (!$user->hasRole('admin') && !$user->hasRole('super_admin')) {
            return redirect()->route('qr.actions', $supply)->with('error', 'Only Admins and Super Admins can issue items.');
        }

        // Check if supply is issuable (consumable or grantable)
        if (!in_array($supply->supply_type, ['consumable', 'grantable'])) {
            return redirect()->route('qr.actions', $supply)->with('error', 'Only consumable and grantable supplies can be issued.');
        }

        if ($supply->status !== 'active') {
            return redirect()->route('qr.actions', $supply)->with('error', 'This supply is not active and cannot be issued.');
        }

        if ($supply->quantity <= 0) {
            return redirect()->route('qr.actions', $supply)->with('error', 'This supply is out of stock.');
        }
        // Redirect to Issued Items create form with pre-selected supply
        return redirect()->route('issued-items.create', ['supply_id' => $supply->id]);
    }

    /**
     * Process Quick Issue Items
     */
    public function processQuickIssue(Request $request, Supply $supply)
    {
        $user = Auth::user();
        
        // Only Admin and Super Admin can issue items
        if (!$user->hasRole('admin') && !$user->hasRole('super_admin')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Validate inputs with rules aligned to IssuedItem requirements
        $rules = [
            'recipient_type' => 'required|in:department,user',
            'department_id' => 'required_if:recipient_type,department|exists:departments,id',
            'user_id' => 'required_if:recipient_type,user|exists:users,id',
            'quantity' => 'required|integer|min:1',
            'issued_on' => 'required|date|before_or_equal:today',
            'notes' => 'nullable|string|max:1000',
        ];

        // If supply has variants, require a valid variant belonging to this supply
        if ($supply->variants && $supply->variants->count() > 0) {
            $rules['supply_variant_id'] = [
                'required',
                Rule::exists('supply_variants', 'id')->where(function($q) use ($supply) {
                    return $q->where('supply_id', $supply->id);
                })
            ];
        } else {
            $rules['supply_variant_id'] = ['nullable', Rule::exists('supply_variants', 'id')];
        }

        $validated = $request->validate($rules);

        // Additional safety checks (status and type)
        if ($supply->status !== 'active') {
            return $request->expectsJson()
                ? response()->json(['error' => 'This supply is not active and cannot be issued.'], 422)
                : redirect()->back()->with('error', 'This supply is not active and cannot be issued.');
        }
        if (!in_array($supply->supply_type, ['consumable', 'grantable'])) {
            return $request->expectsJson()
                ? response()->json(['error' => 'Only consumable and grantable supplies can be issued.'], 422)
                : redirect()->back()->with('error', 'Only consumable and grantable supplies can be issued.');
        }

        // Determine recipient details
        $departmentId = $validated['recipient_type'] === 'department'
            ? $validated['department_id']
            : optional(User::find($validated['user_id']))->department_id;
        $userId = $validated['recipient_type'] === 'user' ? $validated['user_id'] : null;

        // Fallback support for legacy 'purpose' field if present
        $notes = $validated['notes'] ?? $request->input('purpose');

        try {
            DB::beginTransaction();

            // Lock supply for update during stock check
            $lockedSupply = Supply::whereKey($supply->id)->lockForUpdate()->first();
            if (!$lockedSupply) {
                throw new \Exception('Supply not found.');
            }

            // Determine stock source (variant if provided)
            $variant = null;
            if (!empty($validated['supply_variant_id'])) {
                $variant = $lockedSupply->variants()->lockForUpdate()->find($validated['supply_variant_id']);
                if (!$variant) {
                    throw new \Exception('Selected variant is invalid.');
                }
                if ($variant->status !== 'active') {
                    throw new \Exception('Selected variant is disabled and cannot be issued.');
                }
            }

            $stockSource = $variant ?: $lockedSupply;
            
            // Check stock availability
            if ((int)$stockSource->quantity < (int)$validated['quantity']) {
                $itemName = $variant ? ($lockedSupply->name . ' - ' . ($variant->name ?? $variant->variant_name)) : $lockedSupply->name;
                $unit = $stockSource->unit ?? $lockedSupply->unit;
                throw new \Exception("Insufficient stock for {$itemName}. Available: {$stockSource->quantity} {$unit}");
            }

            // Deduct stock from the correct source
            if ($variant) {
                $variant->decrement('quantity', (int)$validated['quantity']);
            } else {
                $lockedSupply->decrement('quantity', (int)$validated['quantity']);
            }

            // Create issued item
            IssuedItem::create([
                'supply_id' => $lockedSupply->id,
                'supply_variant_id' => $variant ? $variant->id : null,
                'department_id' => $departmentId,
                'user_id' => $userId,
                'quantity' => (int)$validated['quantity'],
                'issued_by' => $user->id,
                'notes' => $notes,
                'issued_on' => $validated['issued_on'],
            ]);

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->expectsJson()) {
                return response()->json(['error' => $e->getMessage()], 422);
            }
            return redirect()->back()->withErrors(['error' => $e->getMessage()])->withInput();
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => 'Item issued successfully']);
        }

        return redirect()->route('qr.actions', $supply)->with('success', 'Item issued successfully!');
    }

    /**
     * Quick Status Change
     */
    public function quickStatusChange(Supply $supply)
    {
        $user = Auth::user();
        
        if (!$user) {
        return redirect('/');
        }

        // Restrict quick status change to Admin/Super Admin only
        if (!($user->hasRole('admin') || $user->hasRole('super_admin'))) {
            return redirect()->route('qr.actions', $supply)
                ->with('error', 'Status change is restricted to Admins and Super Admins.');
        }

        $canDirectChange = true;
        
        return view('qr_actions.status-change', compact('supply', 'canDirectChange'));
    }

    /**
     * Process Quick Status Change
     */
    public function processQuickStatusChange(Request $request, Supply $supply)
    {
        $user = Auth::user();
        
        // Restrict quick status change to Admin/Super Admin only
        if (!($user && ($user->hasRole('admin') || $user->hasRole('super_admin')))) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            return redirect()->route('qr.actions', $supply)
                ->with('error', 'Status change is restricted to Admins and Super Admins.');
        }

        $request->validate([
            'new_status' => 'required|in:active,inactive,damaged',
            'reason' => 'required|string|max:1000',
        ]);

        // Direct status change for Admin/Super Admin
        $oldStatus = $supply->status;
        $supply->update([
            'status' => $request->new_status,
            'notes' => $supply->notes . "\n" . now()->format('Y-m-d H:i') . " - Status changed from {$oldStatus} to {$request->new_status} by {$user->name}: " . $request->reason,
        ]);

        $message = 'Supply status updated successfully!';

        if ($request->expectsJson()) {
            return response()->json(['success' => $message]);
        }

        return redirect()->route('qr.actions', $supply)->with('success', $message);
    }

    /**
     * View borrowing information for a supply (read-only)
     */
    public function viewBorrowingInfo(Supply $supply)
    {
        $user = Auth::user();
        
        if (!$user) {
        return redirect('/');
        }

        // Get all active borrowed items for this supply
        $borrowedItems = BorrowedItem::with(['user', 'department', 'loanRequest'])
            ->where('supply_id', $supply->id)
            ->whereNull('returned_at')
            ->get();

        // Get all active inter-department borrowed items for this supply
        $interDeptBorrowedItems = InterDepartmentBorrowedItem::with([
                'borrowedBy', 
                'lendingDepartment', 
                'borrowingDepartment', 
                'issuedItem.supply',
                'interDepartmentLoanRequest'
            ])
            ->whereHas('issuedItem', function($q) use ($supply) {
                $q->where('supply_id', $supply->id);
            })
            ->where('status', 'active')
            ->get();

        return view('qr_actions.view_borrowing_info', compact('supply', 'borrowedItems', 'interDeptBorrowedItems'));
    }

    /**
     * Quick Borrow Request
     */
    public function quickBorrowRequest(Supply $supply)
    {
        $user = Auth::user();
        
        if (!$user) {
        return redirect('/');
        }

        // Check if supply is borrowable
        if (!$supply->isBorrowable()) {
            return redirect()->back()->with('error', 'This supply cannot be borrowed.');
        }

        $isAdmin = $user->hasRole('admin') || $user->hasRole('super_admin');
        
        // For admins, show pending requests for approval
        if ($isAdmin) {
            $pendingLoanRequests = LoanRequest::with(['supply', 'requestedBy', 'department'])
                ->where('supply_id', $supply->id)
                ->where('status', 'pending')
                ->get();

            $pendingInterDeptRequests = InterDepartmentLoanRequest::with(['issuedItem.supply', 'requestedBy', 'lendingDepartment', 'borrowingDepartment'])
                ->whereHas('issuedItem', function($q) use ($supply) {
                    $q->where('supply_id', $supply->id);
                })
                ->whereIn('status', ['pending', 'lending_approved', 'borrowing_confirmed'])
                ->get();

            return view('qr_actions.admin_borrow_requests', compact('supply', 'pendingLoanRequests', 'pendingInterDeptRequests'));
        }

        // For regular users, show borrow request form
        // No need to pass departments since they will be auto-assigned
        
        // Get available issued items for interdepartment loans (filter by available quantity)
        $availableIssuedItems = IssuedItem::with(['supply', 'department'])
            ->where('supply_id', $supply->id)
            ->where('department_id', '!=', $user->department_id)
            ->whereNull('user_id')
            ->get()
            ->filter(function ($item) {
                return $item->availableQuantity > 0;
            });

        return view('qr_actions.quick_borrow_request', compact('supply', 'availableIssuedItems'));
    }

    /**
     * Process Quick Borrow Request
     */
    public function processQuickBorrowRequest(Request $request, Supply $supply)
    {
        $user = Auth::user();
        
        $request->validate([
            'loan_type' => 'required|in:regular,interdepartment',
            'quantity_requested' => 'required|integer|min:1',
            'purpose' => 'required|string|max:1000',
            'expected_return_date' => 'required|date|after:today',
            'issued_item_id' => 'required_if:loan_type,interdepartment|exists:issued_items,id',
        ]);

        if ($request->loan_type === 'regular') {
            // Create regular loan request
            if ($request->quantity_requested > $supply->availableQuantity()) {
                return back()->withErrors(['quantity_requested' => 'Requested quantity exceeds available stock.']);
            }

            // Auto-assign department: For students, use their registered department
            $targetDepartmentId = $user->department_id;

            LoanRequest::create([
                'supply_id' => $supply->id,
                'department_id' => $targetDepartmentId,
                'requested_by' => $user->id,
                'quantity_requested' => $request->quantity_requested,
                'purpose' => $request->purpose,
                'expected_return_date' => $request->expected_return_date,
                'status' => 'pending'
            ]);

            $message = 'Regular loan request submitted successfully!';
        } else {
            // Create interdepartment loan request
            $issuedItem = IssuedItem::findOrFail($request->issued_item_id);
            
            if ($request->quantity_requested > $issuedItem->quantity) {
                return back()->withErrors(['quantity_requested' => 'Requested quantity exceeds available quantity.']);
            }

            // Auto-assign departments: 
            // - Lending department: The department that owns the issued item
            // - Borrowing department: The student's registered department
            $lendingDepartmentId = $issuedItem->department_id;
            $borrowingDepartmentId = $user->department_id;

            InterDepartmentLoanRequest::create([
                'issued_item_id' => $request->issued_item_id,
                'lending_department_id' => $lendingDepartmentId,
                'borrowing_department_id' => $borrowingDepartmentId,
                'requested_by' => $user->id,
                'quantity_requested' => $request->quantity_requested,
                'purpose' => $request->purpose,
                'expected_return_date' => $request->expected_return_date,
                'status' => 'pending',
            ]);

            $message = 'Interdepartment loan request submitted successfully!';
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => $message]);
        }

        return redirect()->route('qr.actions', $supply)->with('success', $message);
    }

    /**
     * Get available actions based on user role and supply
     */
    private function getAvailableActions($user, $supply)
    {
        $actions = [];

        // Check if this supply has borrowed items by other users (for read-only display)
        $otherUserBorrowedItems = BorrowedItem::where('supply_id', $supply->id)
            ->where('user_id', '!=', $user->id)
            ->whereNull('returned_at')
            ->exists();

        $otherUserInterDeptBorrowedItems = InterDepartmentBorrowedItem::whereHas('issuedItem', function($q) use ($supply) {
                $q->where('supply_id', $supply->id);
            })
            ->where('borrowed_by', '!=', $user->id)
            ->where('status', 'active')
            ->exists();

        if ($otherUserBorrowedItems || $otherUserInterDeptBorrowedItems) {
            $actions['view_borrowing_info'] = [
                'title' => 'View Borrowing Information',
                'description' => 'View details of who has borrowed this item',
                'icon' => 'fas fa-info-circle',
                'color' => 'secondary'
            ];
        }

        // Quick Issue Items (Admin/Super Admin only)
        if (($user->hasRole('admin') || $user->hasRole('super_admin')) && 
            $supply->supply_type === 'grantable' && 
            $supply->status === 'active' && 
            $supply->availableQuantity() > 0) {
            $actions['quick_issue'] = [
                'title' => 'Quick Issue Items',
                'description' => 'Issue this supply to a department or user',
                'icon' => 'fas fa-hand-holding',
                'color' => 'primary'
            ];
        } elseif (($user->hasRole('admin') || $user->hasRole('super_admin')) && 
                  $supply->supply_type === 'grantable' && 
                  $supply->status === 'active' && 
                  $supply->availableQuantity() <= 0) {
            $actions['quick_issue_disabled'] = [
                'title' => 'Quick Issue Items',
                'description' => 'Cannot issue - Out of stock',
                'icon' => 'fas fa-hand-holding',
                'color' => 'danger',
                'disabled' => true
            ];
        }

        // Quick Status Change (Admin/Super Admin only)
        if ($user->hasRole('admin') || $user->hasRole('super_admin')) {
            $actions['quick_status_change'] = [
                'title' => 'Quick Status Change',
                'description' => 'Change supply status directly',
                'icon' => 'fas fa-exchange-alt',
                'color' => 'warning'
            ];
        }

        // Quick Return (For users who have borrowed this item)
        $hasBorrowedItems = BorrowedItem::where('supply_id', $supply->id)
            ->where('user_id', $user->id)
            ->whereNull('returned_at')
            ->exists();

        $hasInterDeptBorrowedItems = InterDepartmentBorrowedItem::where('borrowed_by', $user->id)
            ->whereHas('issuedItem', function($q) use ($supply) {
                $q->where('supply_id', $supply->id);
            })
            ->where('status', 'active')
            ->exists();

        if ($hasBorrowedItems || $hasInterDeptBorrowedItems) {
            $actions['quick_return'] = [
                'title' => 'Quick Return',
                'description' => 'Return borrowed items of this supply',
                'icon' => 'fas fa-undo',
                'color' => 'info'
            ];
        }

        // View Borrowing Info (if there are borrowed items by other users)
        if ($otherUserBorrowedItems || $otherUserInterDeptBorrowedItems) {
            $actions['view_borrowing_info'] = [
                'title' => 'View Borrowing Info',
                'description' => 'View who has borrowed this item',
                'icon' => 'fas fa-info-circle',
                'color' => 'info'
            ];
        }

        // Quick Supply Request (Advisers only)
        if ($user->hasRole('adviser')) {
            $actions['supply_request'] = [
                'title' => 'Quick Supply Request',
                'description' => 'Create a supply request for this item',
                'icon' => 'fas fa-plus-circle',
                'color' => 'success'
            ];
        }

        // Quick Borrow Request (All authenticated users, if supply is borrowable)
        if ($supply->isBorrowable()) {
            if ($supply->availableQuantity() > 0) {
                $actions['borrow_request'] = [
                    'title' => 'Quick Borrow Request',
                    'description' => ($user->hasRole('admin') || $user->hasRole('super_admin'))
                        ? 'Manage pending borrow requests'
                        : 'Create a new borrow request (including cross-department)',
                    'icon' => 'fas fa-handshake',
                    'color' => 'success'
                ];
            } else {
                $actions['borrow_request_disabled'] = [
                    'title' => 'Quick Borrow Request',
                    'description' => 'Cannot borrow - Out of stock',
                    'icon' => 'fas fa-handshake',
                    'color' => 'danger',
                    'disabled' => true
                ];
            }
        }

        return $actions;
    }

    /**
     * Approve a borrow request
     */
    public function approveBorrowRequest(Request $request, Supply $supply, LoanRequest $loanRequest)
    {
        $user = Auth::user();
        
        // Only Admin and Super Admin can approve requests
        if (!$user->hasRole('admin') && !$user->hasRole('super_admin')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Validate that the loan request belongs to this supply
        if ($loanRequest->supply_id !== $supply->id) {
            return response()->json(['error' => 'Invalid loan request'], 400);
        }

        // Check if request is still pending
        if ($loanRequest->status !== 'pending') {
            return response()->json(['error' => 'This request has already been processed'], 400);
        }

        // Check if there's enough quantity available
        if ($loanRequest->quantity_requested > $supply->availableQuantity()) {
            return response()->json(['error' => 'Insufficient quantity available'], 400);
        }

        DB::transaction(function () use ($loanRequest, $supply, $user) {
            // Update loan request status to approved first
            $loanRequest->update([
                'status' => 'approved',
                'approved_by' => $user->id,
                'approved_at' => now(),
            ]);

            // Create borrowed item record (regular borrowing)
            $borrowedItem = \App\Models\BorrowedItem::create([
                'supply_id' => $supply->id,
                'department_id' => $loanRequest->department_id,
                'user_id' => $loanRequest->requested_by,
                'quantity' => $loanRequest->quantity_requested,
                'borrowed_at' => now(),
            ]);

            // Mark request as borrowed and link to the borrowed item
            $loanRequest->update([
                'status' => 'borrowed',
                'borrowed_item_id' => $borrowedItem->id,
            ]);

            // Do not update total supply quantity; availability is computed dynamically
        });

        if ($request->expectsJson()) {
            return response()->json(['success' => 'Loan request approved successfully']);
        }

        return redirect()->route('qr.borrow-request', $supply)->with('success', 'Loan request approved and marked as Borrowed.');
    }

    /**
     * Reject a borrow request
     */
    public function rejectBorrowRequest(Request $request, Supply $supply, LoanRequest $loanRequest)
    {
        $user = Auth::user();
        
        // Only Admin and Super Admin can reject requests
        if (!$user->hasRole('admin') && !$user->hasRole('super_admin')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Validate that the loan request belongs to this supply
        if ($loanRequest->supply_id !== $supply->id) {
            return response()->json(['error' => 'Invalid loan request'], 400);
        }

        // Check if request is still pending
        if ($loanRequest->status !== 'pending') {
            return response()->json(['error' => 'This request has already been processed'], 400);
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        // Update loan request status
        $loanRequest->update([
            'status' => 'rejected',
            'approved_by' => $user->id,
            'approved_at' => now(),
            'rejection_reason' => $request->rejection_reason,
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => 'Loan request rejected successfully']);
        }

        return redirect()->route('qr.borrow-request', $supply)->with('success', 'Loan request rejected successfully!');
    }

    /**
     * Show Quick Return form
     */
    public function quickReturn(Supply $supply)
    {
        $user = Auth::user();

        if (!$user) {
        return redirect('/');
        }

        // Get user's borrowed items for this supply
        $borrowedItems = BorrowedItem::with(['supply', 'department'])
            ->where('supply_id', $supply->id)
            ->where('user_id', $user->id)
            ->whereNull('returned_at')
            ->get();

        // Get user's inter-department borrowed items for this supply
        $interDeptBorrowedItems = InterDepartmentBorrowedItem::with(['issuedItem.supply', 'lendingDepartment', 'borrowingDepartment'])
            ->where('borrowed_by', $user->id)
            ->whereHas('issuedItem', function($q) use ($supply) {
                $q->where('supply_id', $supply->id);
            })
            ->where('status', 'active')
            ->get();

        if ($borrowedItems->isEmpty() && $interDeptBorrowedItems->isEmpty()) {
            return redirect()->route('qr.actions', $supply)->with('error', 'You have no borrowed items of this supply to return.');
        }

        return view('qr_actions.quick_return', compact('supply', 'borrowedItems', 'interDeptBorrowedItems'));
    }

    /**
     * Process Quick Return
     */
    public function processQuickReturn(Request $request, Supply $supply)
    {
        $user = Auth::user();

        $request->validate([
            'return_type' => 'required|in:regular,interdepartment',
            'item_id' => 'required|integer',
            'return_quantity' => 'required|integer|min:1',
            'return_notes' => 'nullable|string|max:1000',
            'return_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'missing_count' => 'nullable|integer|min:0',
            'damaged_count' => 'nullable|integer|min:0',
            'damage_severity' => 'nullable|string|in:minor,moderate,severe,total_loss',
        ]);

        $returnPhotoPath = null;
        if ($request->hasFile('return_photo')) {
            $returnPhotoPath = $request->file('return_photo')->store('return_photos', 'public');
        }

        DB::transaction(function () use ($request, $supply, $user, $returnPhotoPath) {
            if ($request->return_type === 'regular') {
                $borrowedItem = BorrowedItem::where('id', $request->item_id)
                    ->where('supply_id', $supply->id)
                    ->where('user_id', $user->id)
                    ->whereNull('returned_at')
                    ->firstOrFail();

                if ($request->return_quantity > $borrowedItem->quantity) {
                    throw new \Exception('Return quantity cannot exceed borrowed quantity.');
                }

                if ($request->return_quantity == $borrowedItem->quantity) {
                    // Full return
                    $borrowedItem->update([
                        'returned_at' => now(),
                        'return_notes' => $request->return_notes,
                        'return_photo' => $returnPhotoPath,
                    ]);
                } else {
                    // Partial return - update quantity and create new record for returned portion
                    $borrowedItem->decrement('quantity', $request->return_quantity);
                    
                    BorrowedItem::create([
                        'supply_id' => $supply->id,
                        'department_id' => $borrowedItem->department_id,
                        'user_id' => $user->id,
                        'quantity' => $request->return_quantity,
                        'borrowed_at' => $borrowedItem->borrowed_at,
                        'returned_at' => now(),
                        'return_notes' => $request->return_notes,
                        'return_photo' => $returnPhotoPath,
                    ]);
                }

                // Do not update total supply quantity on return; availability is computed dynamically

            } else {
                // Inter-department return: set to return_pending for verification
                $interDeptBorrowedItem = InterDepartmentBorrowedItem::where('id', $request->item_id)
                    ->where('borrowed_by', $user->id)
                    ->where('status', 'active')
                    ->firstOrFail();

                if ($request->return_quantity > $interDeptBorrowedItem->quantity) {
                    throw new \Exception('Return quantity cannot exceed borrowed quantity.');
                }

                if ($request->return_quantity == $interDeptBorrowedItem->quantity) {
                    // Full return initiation: mark item as return_pending
                    $interDeptBorrowedItem->update([
                        'status' => 'return_pending',
                        'returned_to' => $user->id,
                        'return_notes' => $request->return_notes,
                        'return_photo' => $returnPhotoPath,
                    ]);

                    // Create return record
                    $damaged = $request->input('damaged_count');
                    $severity = $request->input('damage_severity');
                    if (empty($damaged) || intval($damaged) === 0) {
                        $severity = null;
                    }

                    \App\Models\InterDepartmentReturnRecord::create([
                        'inter_department_borrowed_item_id' => $interDeptBorrowedItem->id,
                        'initiated_by' => $user->id,
                        'notes' => $request->return_notes,
                        'photo_path' => $returnPhotoPath,
                        'missing_count' => $request->input('missing_count'),
                        'damaged_count' => $damaged,
                        'damage_severity' => $severity,
                    ]);
                } else {
                    // Partial return initiation: split into a pending return item
                    $interDeptBorrowedItem->decrement('quantity', $request->return_quantity);

                    $pendingReturnItem = InterDepartmentBorrowedItem::create([
                        'inter_department_loan_request_id' => $interDeptBorrowedItem->inter_department_loan_request_id,
                        'issued_item_id' => $interDeptBorrowedItem->issued_item_id,
                        'lending_department_id' => $interDeptBorrowedItem->lending_department_id,
                        'borrowing_department_id' => $interDeptBorrowedItem->borrowing_department_id,
                        'quantity' => $request->return_quantity,
                        'borrowed_date' => $interDeptBorrowedItem->borrowed_date,
                        'expected_return_date' => $interDeptBorrowedItem->expected_return_date,
                        'borrowed_by' => $user->id,
                        'status' => 'return_pending',
                        'return_notes' => $request->return_notes,
                        'return_photo' => $returnPhotoPath,
                    ]);

                    $damaged = $request->input('damaged_count');
                    $severity = $request->input('damage_severity');
                    if (empty($damaged) || intval($damaged) === 0) {
                        $severity = null;
                    }

                    \App\Models\InterDepartmentReturnRecord::create([
                        'inter_department_borrowed_item_id' => $pendingReturnItem->id,
                        'initiated_by' => $user->id,
                        'notes' => $request->return_notes,
                        'photo_path' => $returnPhotoPath,
                        'missing_count' => $request->input('missing_count'),
                        'damaged_count' => $damaged,
                        'damage_severity' => $severity,
                    ]);
                }

                // Update loan request status to return_pending
                if ($interDeptBorrowedItem->interDepartmentLoanRequest) {
                    $interDeptBorrowedItem->interDepartmentLoanRequest->update(['status' => 'return_pending']);
                }

                // Do NOT update issued item quantity here; it will be updated upon verification
            }
        });

        $message = 'Return initiated. Awaiting lending department verification.';
        if ($returnPhotoPath) {
            $message .= ' Photo attached to the return record.';
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => $message]);
        }

        return redirect()->route('qr.actions', $supply)->with('success', $message);
    }

    /**
     * Quick Supply Request - Pre-fill supply request form with supply
     */
    public function quickSupplyRequest(Supply $supply)
    {
        $user = Auth::user();
        
        // Only advisers can create supply requests
        if (!$user->hasRole('adviser')) {
            return redirect()->route('qr.actions', $supply)->with('error', 'Only advisers can create supply requests.');
        }

        $supply->load(['variants']);
        
        return view('qr_actions.quick_supply_request', compact('supply'));
    }

    /**
     * Process Quick Supply Request
     */
    public function processQuickSupplyRequest(Request $request, Supply $supply)
    {
        $user = Auth::user();
        
        // Only advisers can create supply requests
        if (!$user->hasRole('adviser')) {
            return redirect()->route('qr.actions', $supply)->with('error', 'Only advisers can create supply requests.');
        }

        $request->validate([
            'quantity' => 'required|integer|min:1',
            'purpose' => 'required|string|max:500',
            'priority' => 'required|in:low,medium,high',
            'supply_variant_id' => 'nullable|exists:supply_variants,id',
        ]);

        // Create the supply request
        $supplyRequest = \App\Models\SupplyRequest::create([
            'item_name' => $supply->name,
            'supply_id' => $supply->id,
            'supply_variant_id' => $request->supply_variant_id,
            'user_id' => $user->id,
            'department_id' => $user->department_id,
            'quantity' => $request->quantity,
            'unit' => $supply->unit,
            'description' => $request->purpose,
            'priority' => $request->priority,
            'status' => 'pending',
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => 'Supply request created successfully!',
                'supply_request_id' => $supplyRequest->id
            ]);
        }

        return redirect()->route('supply-requests.show', $supplyRequest)
            ->with('success', 'Supply request created successfully! You can view and track its status here.');
    }

    /**
     * Return users by department for strict filtering in quick issue
     */
    public function usersByDepartment(Department $department)
    {
        $users = User::where('department_id', $department->id)
            ->whereHas('role', function($q) {
                $q->whereIn('name', [
                    Role::STUDENT,
                    Role::ADVISER,
                    Role::DEAN,
                    Role::USER,
                ]);
            })
            ->with(['department'])
            ->orderBy('name')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'department_id' => $user->department_id,
                    'department_name' => optional($user->department)->name ?? 'N/A',
                ];
            });

        return response()->json(['users' => $users]);
    }

}