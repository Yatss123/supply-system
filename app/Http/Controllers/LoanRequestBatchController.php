<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\LoanRequestBatch;
use App\Models\LoanRequest;
use App\Models\BorrowedItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LoanRequestBatchController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        // Admin-only access for batch actions
        $this->middleware(function ($request, $next) {
            $action = $request->route()->getActionMethod();
            $restricted = ['approveAll', 'declineAll', 'approveSelected'];
            if (in_array($action, $restricted)) {
                $user = Auth::user();
                if (!$user || !$user->hasAdminPrivileges()) {
                    abort(403, 'Unauthorized. Admin access required.');
                }
            }
            return $next($request);
        });
    }

    /**
     * Approve and issue all pending, eligible items in a batch.
     */
    public function approveAll(Request $request, LoanRequestBatch $batch)
    {
        $request->validate([
            'approval_notes' => 'nullable|string|max:1000',
        ]);

        $userId = Auth::id();
        $notes = $request->input('approval_notes');

        $approvedCount = 0;
        $skipped = [];

        DB::beginTransaction();
        try {
            $items = $batch->items()->with(['supply', 'requestedBy'])->where('status', 'pending')->get();
            foreach ($items as $item) {
                if (method_exists($item, 'canBeApproved') && $item->canBeApproved()) {
                    $borrowedItem = BorrowedItem::create([
                        'supply_id' => $item->supply_id,
                        'department_id' => $item->department_id,
                        'user_id' => $item->requested_by,
                        'quantity' => $item->quantity_requested,
                        'borrowed_at' => now(),
                    ]);

                    $item->approve($userId, $notes);
                    $item->update(['borrowed_item_id' => $borrowedItem->id]);
                    $approvedCount++;
                } else {
                    $reason = 'Prerequisites not met';
                    if (!$item->isPending()) {
                        $reason = 'Not pending';
                    } elseif ($item->needsDeanApproval() && !$item->isDeanApproved()) {
                        $reason = 'Waiting for dean approval';
                    } elseif ($item->supply && $item->supply->availableQuantity() < $item->quantity_requested) {
                        $reason = 'Insufficient stock';
                    }
                    $skipped[] = [
                        'id' => $item->id,
                        'supply' => optional($item->supply)->name,
                        'reason' => $reason,
                    ];
                }
            }

            $this->updateBatchStatus($batch);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'Batch approval failed: ' . $e->getMessage()]);
        }

        $message = 'Approved ' . $approvedCount . ' item(s) in this batch.';
        if (count($skipped) > 0) {
            $message .= ' Skipped ' . count($skipped) . ' item(s): ' . collect($skipped)->map(function ($s) {
                return '#' . $s['id'] . ' (' . ($s['supply'] ?? 'N/A') . ') - ' . $s['reason'];
            })->implode('; ');
        }

        $firstItem = $batch->items()->orderBy('id')->first();
        if ($firstItem) {
            return redirect()->route('loan-requests.show', $firstItem)->with('success', $message);
        }
        return redirect()->route('loan-requests.index', ['tab' => 'standard'])->with('success', $message);
    }

    /**
     * Decline all pending items in a batch with a reason.
     */
    public function declineAll(Request $request, LoanRequestBatch $batch)
    {
        $request->validate([
            'decline_reason' => 'nullable|string|max:1000',
        ]);

        $reason = $request->input('decline_reason');
        $declinedCount = 0;

        DB::beginTransaction();
        try {
            $items = $batch->items()->where('status', 'pending')->get();
            foreach ($items as $item) {
                $item->decline($reason);
                $declinedCount++;
            }

            $this->updateBatchStatus($batch);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'Batch decline failed: ' . $e->getMessage()]);
        }

        $message = 'Declined ' . $declinedCount . ' pending item(s) in this batch.';
        $firstItem = $batch->items()->orderBy('id')->first();
        if ($firstItem) {
            return redirect()->route('loan-requests.show', $firstItem)->with('success', $message);
        }
        return redirect()->route('loan-requests.index', ['tab' => 'standard'])->with('success', $message);
    }

    /**
     * Approve selected pending items and decline unselected items (optional reason).
     */
    public function approveSelected(Request $request, LoanRequestBatch $batch)
    {
        $request->validate([
            'selected' => 'array',
            'selected.*' => 'integer|exists:loan_requests,id',
            'approval_notes' => 'nullable|string|max:1000',
            'decline_reason' => 'nullable|string|max:1000',
        ]);

        $selectedIds = collect($request->input('selected', []))->map(fn($v) => (int) $v)->all();
        $userId = Auth::id();
        $notes = $request->input('approval_notes');
        $declineReason = $request->input('decline_reason');

        $approvedCount = 0;
        $declinedCount = 0;
        $skipped = [];

        DB::beginTransaction();
        try {
            $pendingItems = $batch->items()->with(['supply', 'requestedBy'])->where('status', 'pending')->get();
            $selectedItems = $pendingItems->whereIn('id', $selectedIds);
            $unselectedItems = $pendingItems->whereNotIn('id', $selectedIds);

            foreach ($selectedItems as $item) {
                if (method_exists($item, 'canBeApproved') && $item->canBeApproved()) {
                    $borrowedItem = BorrowedItem::create([
                        'supply_id' => $item->supply_id,
                        'department_id' => $item->department_id,
                        'user_id' => $item->requested_by,
                        'quantity' => $item->quantity_requested,
                        'borrowed_at' => now(),
                    ]);

                    $item->approve($userId, $notes);
                    $item->update(['borrowed_item_id' => $borrowedItem->id]);
                    $approvedCount++;
                } else {
                    $reason = 'Prerequisites not met';
                    if (!$item->isPending()) {
                        $reason = 'Not pending';
                    } elseif ($item->needsDeanApproval() && !$item->isDeanApproved()) {
                        $reason = 'Waiting for dean approval';
                    } elseif ($item->supply && $item->supply->availableQuantity() < $item->quantity_requested) {
                        $reason = 'Insufficient stock';
                    }
                    $skipped[] = [
                        'id' => $item->id,
                        'supply' => optional($item->supply)->name,
                        'reason' => $reason,
                    ];
                }
            }

            foreach ($unselectedItems as $item) {
                $item->decline($declineReason);
                $declinedCount++;
            }

            $this->updateBatchStatus($batch);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'Batch approve-selected failed: ' . $e->getMessage()]);
        }

        $message = 'Approved ' . $approvedCount . ' item(s).';
        if ($declinedCount > 0) {
            $message .= ' Declined ' . $declinedCount . ' unselected item(s).';
        }
        if (count($skipped) > 0) {
            $message .= ' Skipped ' . count($skipped) . ' item(s): ' . collect($skipped)->map(function ($s) {
                return '#' . $s['id'] . ' (' . ($s['supply'] ?? 'N/A') . ') - ' . $s['reason'];
            })->implode('; ');
        }

        $firstItem = $batch->items()->orderBy('id')->first();
        if ($firstItem) {
            return redirect()->route('loan-requests.show', $firstItem)->with('success', $message);
        }
        return redirect()->route('loan-requests.index', ['tab' => 'standard'])->with('success', $message);
    }

    protected function updateBatchStatus(LoanRequestBatch $batch): void
    {
        $batch->load('items');
        $statuses = $batch->items->pluck('status');
        $newStatus = 'pending';
        if ($statuses->count() > 0) {
            if ($statuses->every(fn($s) => $s === 'approved')) {
                $newStatus = 'approved';
            } elseif ($statuses->every(fn($s) => in_array($s, ['declined']))) {
                $newStatus = 'declined';
            } elseif ($statuses->contains('approved') || $statuses->contains('declined')) {
                $newStatus = 'partial';
            }
        }
        $batch->update(['status' => $newStatus]);
    }
}
