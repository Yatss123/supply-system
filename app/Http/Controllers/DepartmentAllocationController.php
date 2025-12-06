<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\DepartmentMonthlyAllocation;
use App\Models\DepartmentMonthlyAllocationItem;
use App\Models\Supply;
use App\Services\DepartmentAllocationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\IssuedItemBatch;
use App\Models\IssuedItem;
use App\Models\User;
use App\Notifications\DeanReminderSettingsUpdatedNotification;

class DepartmentAllocationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('profile.complete');
    }

    /**
     * Admin overview: list allocations across all departments for a month.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->hasAdminPrivileges()) {
            abort(403, 'Unauthorized');
        }

        $month = $request->query('month') ?: now()->format('Y-m');
        $departments = Department::where('status', 'active')->orderBy('department_name')->get();

        // Map allocations by department id (do not refresh to avoid spam notifications)
        $allocations = DepartmentMonthlyAllocation::whereIn('department_id', $departments->pluck('id'))
            ->where('month', $month)
            ->with(['department', 'items.supply'])
            ->get()
            ->keyBy('department_id');

        return view('admin.allocations.index', [
            'departments' => $departments,
            'allocations' => $allocations,
            'month' => $month,
        ]);
    }

    /**
     * Dean allocation overview for a department and month.
     */
    public function show(Department $department, Request $request, DepartmentAllocationService $service)
    {
        $user = Auth::user();
        if (!$user->isDeanOf($department) && !$user->hasAdminPrivileges()) {
            abort(403, 'Unauthorized');
        }

        $month = $request->query('month') ?: now()->format('Y-m');
        $allocation = $service->refreshForDepartmentMonth($department, $month);

        $departmentUsers = \App\Models\User::where('department_id', $department->id)
            ->whereHas('role', function ($q) { $q->whereIn('name', ['student', 'adviser', 'dean']); })
            ->orderBy('name')
            ->get();

        // Preload consumable supplies for admin inventory picker in Update Stocks modal
        $consumables = Supply::where('supply_type', 'consumable')
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id','name','unit']);

        return view('departments.allocations.dean', compact('department', 'allocation', 'month', 'departmentUsers', 'consumables'));
    }

    /**
     * Dean: record actual available quantities per allocation item (audited stock).
     */
    public function updateActualAvailability(Department $department, Request $request, DepartmentAllocationService $service)
    {
        $user = Auth::user();
        if (!$user->isDeanOf($department) && !$user->hasAdminPrivileges()) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'month' => 'required|date_format:Y-m',
            'actual_available' => 'required|array',
            'actual_available.*' => 'nullable|integer|min:0',
        ]);

        // Ensure allocation exists and is refreshed
        $allocation = $service->refreshForDepartmentMonth($department, $validated['month']);

        $items = DepartmentMonthlyAllocationItem::where('allocation_id', $allocation->id)
            ->get()
            ->keyBy('id');

        $updated = 0;
        foreach ($validated['actual_available'] as $itemId => $value) {
            if ($value === null || $value === '') { continue; }
            $valueInt = (int)$value;
            if (!isset($items[$itemId])) { continue; }
            $item = $items[$itemId];
            $attrs = $item->attributes ?? [];

            $previous = isset($attrs['actual_available']) ? (int)$attrs['actual_available'] : null;
            // Update the actual_available
            $attrs['actual_available'] = $valueInt;
            // Append audit trail entry
            $audit = $attrs['actual_available_audit'] ?? [];
            $audit[] = [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'value' => $valueInt,
                'previous' => $previous,
                'when' => now()->toDateTimeString(),
            ];
            $attrs['actual_available_audit'] = $audit;

            $item->attributes = $attrs;
            $item->save();
            $updated++;
        }

        return redirect()->route('dean.allocations.show', ['department' => $department->id, 'month' => $validated['month']])
            ->with('success', "Updated actual availability for {$updated} item(s).");
    }

    /**
     * Admin-only: refresh allocation data for a department and month.
     */
    public function adminRefresh(Department $department, Request $request, DepartmentAllocationService $service)
    {
        $user = Auth::user();
        if (!$user || !$user->hasAdminPrivileges()) {
            abort(403, 'Unauthorized');
        }

        $month = $request->input('month') ?: now()->format('Y-m');
        $service->refreshForDepartmentMonth($department, $month);

        return redirect()->route('admin.allocations.index', ['month' => $month])
            ->with('success', 'Allocation data refreshed for '.$department->department_name.' ('.$month.').');
    }

    /**
     * Refresh low-stock suggestions into the department cart.
     */
    public function refreshCart(Department $department, Request $request, DepartmentAllocationService $service)
    {
        $user = Auth::user();
        if (!$user->isDeanOf($department) && !$user->hasAdminPrivileges()) {
            abort(403, 'Unauthorized');
        }

        $month = $request->input('month') ?: now()->format('Y-m');
        $service->refreshLowStockCart($department, $month);

        return redirect()->route('department-carts.show', $department->id)
            ->with('success', 'Low-stock suggestions added to your department cart.');
    }

    /**
     * Update min stock level for a specific allocation item and recompute suggestion.
     */
    public function updateItemMinLevel(DepartmentMonthlyAllocationItem $item, Request $request)
    {
        $allocation = $item->allocation;
        $department = $allocation->department;
        $user = Auth::user();
        if (!$user->isDeanOf($department) && !$user->hasAdminPrivileges()) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'min_stock_level' => 'required|integer|min:0',
        ]);

        // Recompute suggestion based on issued qty
        $minLevel = (int)$validated['min_stock_level'];
        $suggestQty = max($minLevel - (int)$item->issued_qty, 0);
        $lowStock = $item->issued_qty < $minLevel;

        $item->update([
            'min_stock_level' => $minLevel,
            'suggest_qty' => $suggestQty,
            'low_stock' => $lowStock,
        ]);

        return redirect()->back()->with('success', 'Min stock level updated.');
    }

    /**
     * Admin-only: update max limit for an allocation item.
     */
    public function updateItemMaxLimit(DepartmentMonthlyAllocationItem $item, Request $request)
    {
        $allocation = $item->allocation;
        $department = $allocation->department;
        $user = Auth::user();
        if (!$user || !$user->hasAdminPrivileges()) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'max_limit' => 'required|integer|min:0',
        ]);

        $item->update(['max_limit' => (int)$validated['max_limit']]);

        return redirect()->back()->with('success', 'Max limit updated.');
    }

    /**
     * Admin-only: update configured issuance quantity for an allocation item.
     */
    public function updateItemIssueQty(DepartmentMonthlyAllocationItem $item, Request $request)
    {
        $allocation = $item->allocation;
        $department = $allocation->department;
        $user = Auth::user();
        if (!$user || !$user->hasAdminPrivileges()) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'target_issue_qty' => 'nullable|integer|min:0',
        ]);

        $item->update(['target_issue_qty' => $validated['target_issue_qty'] ?? null]);

        return redirect()->back()->with('success', 'Issuance quantity configured.');
    }

    /**
     * Admin-only: issue items to reach maximum limits for a department/month.
     */
    public function issueToMax(Department $department, Request $request, DepartmentAllocationService $service)
    {
        $user = Auth::user();
        if (!$user || !$user->hasAdminPrivileges()) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'month' => 'required|date_format:Y-m',
        ]);

        $summary = $service->replenishToMax($department, $validated['month']);

        return redirect()->route('admin.allocations.index', ['month' => $validated['month']])
            ->with('success', 'Replenishment completed. Issued '.count($summary['issued']).' items; created '.count($summary['restock_requests']).' restock requests.');
    }

    /**
     * Admin-only: update allocation status (open/closed) for a department and month.
     */
    public function updateStatus(Department $department, Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->hasAdminPrivileges()) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'month' => 'required|date_format:Y-m',
            'status' => 'required|in:open,closed',
        ]);

        $allocation = DepartmentMonthlyAllocation::where('department_id', $department->id)
            ->where('month', $validated['month'])
            ->first();

        if (!$allocation) {
            return redirect()->back()->with('error', 'Allocation not found. Please refresh first.');
        }

        $allocation->update([
            'status' => $validated['status'],
            'updated_by' => $user->id,
        ]);

        return redirect()->route('admin.allocations.index', ['month' => $validated['month']])
            ->with('success', 'Status updated to '.strtoupper($validated['status']).' for '.$department->department_name.' ('.$validated['month'].').');
    }

    /**
     * Admin-only: stage selected items as Ready to Pick Up using target_issue_qty.
     */
    public function stageIssueSelected(Department $department, Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->hasAdminPrivileges()) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'month' => 'required|date_format:Y-m',
            'items' => 'required|array',
            'items.*' => 'integer',
        ]);

        $allocation = DepartmentMonthlyAllocation::where('department_id', $department->id)
            ->where('month', $validated['month'])
            ->first();
        if (!$allocation) {
            return redirect()->back()->with('error', 'Allocation not found. Please refresh first.');
        }

        $items = DepartmentMonthlyAllocationItem::where('allocation_id', $allocation->id)
            ->whereIn('id', $validated['items'])
            ->get();

        $stagedCount = 0;
        $insufficientSupplies = [];

        foreach ($items as $item) {
            $supply = $item->supply;
            if (!$supply || !$supply->isActive()) {
                continue;
            }

            // Determine quantity: prefer configured target_issue_qty; fallback to suggestion from min stock.
            $baseQty = (int)($item->target_issue_qty ?? 0);
            if ($baseQty <= 0) {
                $fallback = max((int)$item->min_stock_level - (int)$item->issued_qty, 0);
                $baseQty = (int)$fallback;
            }
            if ($baseQty <= 0) {
                // Nothing meaningful to stage for this item
                continue;
            }

            $available = (int)$supply->availableQuantity();
            if ($available <= 0) {
                $insufficientSupplies[] = $supply->name;
                continue;
            }

            $qty = min($baseQty, $available);
            if ($qty <= 0) {
                $insufficientSupplies[] = $supply->name;
                continue;
            }

            $item->update([
                'issue_status' => 'ready',
                'staged_issue_qty' => $qty,
            ]);
            $stagedCount++;
        }

        // Notify dean when items are staged
        if ($stagedCount > 0) {
            $dean = User::getDeanOfDepartment($department->id);
            if ($dean) {
                $dean->notify(new \App\Notifications\DepartmentAllocationReadyNotification($department, $validated['month'], $stagedCount));
            }
        }

        $redirect = redirect()->back();

        if (!empty($insufficientSupplies)) {
            // Limit list length in message for readability
            $displayList = array_slice($insufficientSupplies, 0, 5);
            $more = count($insufficientSupplies) - count($displayList);
            $warning = 'Not enough supplies in inventory: ' . implode(', ', $displayList);
            if ($more > 0) { $warning .= " and {$more} more"; }
            $redirect = $redirect->with('warning', $warning);
        }

        return $redirect;
    }

    /**
     * Admin-only: finalize issuance for selected items currently Ready to Pick Up.
     * Issues from store/variants and updates department stock accordingly.
     */
    public function issueSelected(Department $department, Request $request, DepartmentAllocationService $service)
    {
        $user = Auth::user();
        if (!$user || !$user->hasAdminPrivileges()) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'month' => 'required|date_format:Y-m',
            'items' => 'required|array',
            'items.*' => 'integer',
            'receiver_id' => 'required|integer',
            'pickup_quantities' => 'nullable|array',
            'pickup_quantities.*' => 'integer|min:1',
        ]);

        $allocation = DepartmentMonthlyAllocation::where('department_id', $department->id)
            ->where('month', $validated['month'])
            ->first();
        if (!$allocation) {
            return redirect()->back()->with('error', 'Allocation not found. Please refresh first.');
        }

        // Validate receiver belongs to this department (mandatory)
        $receiverId = null;
        $receiverName = null;
        $receiver = User::find($validated['receiver_id']);
        if (!$receiver) {
            return redirect()->back()->withErrors(['receiver_id' => 'Selected receiver does not exist.'])->withInput();
        }
        if ((int)$receiver->department_id !== (int)$department->id) {
            return redirect()->back()->withErrors(['receiver_id' => 'Receiver must belong to the selected department.'])->withInput();
        }
        $receiverId = (int)$receiver->id;
        $receiverName = $receiver->name;

        $items = DepartmentMonthlyAllocationItem::where('allocation_id', $allocation->id)
            ->whereIn('id', $validated['items'])
            ->get();

        $stagedCount = 0;
        $issuedCount = 0;
        $insufficientSupplies = [];

        foreach ($items as $item) {
            $supply = $item->supply;
            if (!$supply || !$supply->isActive()) {
                continue;
            }

            $qtyToIssue = (int)($validated['pickup_quantities'][$item->id] ?? $item->staged_issue_qty ?? 0);
            if ($qtyToIssue <= 0) { continue; }

            $available = (int)$supply->availableQuantity();
            if ($available <= 0) {
                $insufficientSupplies[] = $supply->name;
                continue;
            }

            $qty = min($qtyToIssue, $available);
            if ($qty <= 0) {
                $insufficientSupplies[] = $supply->name;
                continue;
            }

            // Deduct supply/variants and create issued item
            DB::transaction(function () use ($department, $receiverId, $receiverName, $supply, $qty, $validated, $item, &$issuedCount) {
                $batch = IssuedItemBatch::create([
                    'department_id' => $department->id,
                    'user_id' => $receiverId,
                    'issued_by' => Auth::id(),
                    'issued_on' => now(),
                    'notes' => 'Allocation issuance (' . $validated['month'] . ') to ' . $receiverName,
                ]);

                if ($supply->hasVariants()) {
                    $remaining = $qty;
                    $variants = $supply->variants()->where('status', 'active')->orderByDesc('quantity')->get();
                    foreach ($variants as $variant) {
                        if ($remaining <= 0) { break; }
                        $vAvail = $variant->availableQuantity();
                        if ($vAvail <= 0) { continue; }
                        $chunk = min($remaining, $vAvail);
                        $variant->decrement('quantity', $chunk);

                        IssuedItem::create([
                            'batch_id' => $batch->id,
                            'supply_id' => $supply->id,
                            'supply_variant_id' => $variant->id,
                            'department_id' => $department->id,
                            'user_id' => $receiverId,
                            'quantity' => $chunk,
                            'issued_on' => now(),
                            'notes' => 'Allocation issuance (' . $validated['month'] . ')',
                            'issued_by' => Auth::id(),
                            'available_for_borrowing' => false,
                            'borrowed_quantity' => 0,
                        ]);

                        $remaining -= $chunk;
                    }
                } else {
                    $supply->decrement('quantity', $qty);

                    IssuedItem::create([
                        'batch_id' => $batch->id,
                        'supply_id' => $supply->id,
                        'department_id' => $department->id,
                        'user_id' => $receiverId,
                        'quantity' => $qty,
                        'issued_on' => now(),
                        'notes' => 'Allocation issuance (' . $validated['month'] . ')',
                        'issued_by' => Auth::id(),
                        'available_for_borrowing' => false,
                        'borrowed_quantity' => 0,
                    ]);
                }

                $item->update([
                    'issue_status' => 'issued',
                    'staged_issue_qty' => null,
                ]);

                $issuedCount++;
            });
        }

        $message = "Issued {$issuedCount} item(s).";
        $redirect = redirect()->route('dean.allocations.show', ['department' => $department->id, 'month' => $validated['month']])
            ->with('success', $message);

        if (!empty($insufficientSupplies)) {
            $displayList = array_slice($insufficientSupplies, 0, 5);
            $more = count($insufficientSupplies) - count($displayList);
            $warning = 'Not enough supplies in inventory: ' . implode(', ', $displayList);
            if ($more > 0) { $warning .= " and {$more} more"; }
            $redirect = $redirect->with('warning', $warning);
        }

        return $redirect;
    }

    /**
     * Dean: update weekly reminder day (1-7 ISO; null disables).
     */
    public function updateReminderDay(Department $department, Request $request)
    {
        $user = Auth::user();
        if (!$user->isDeanOf($department) && !$user->hasAdminPrivileges()) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'reminder_day' => 'nullable|integer|in:1,2,3,4,5,6,7',
        ]);

        $day = $validated['reminder_day'] ?? null; // null means disabled
        $department->stock_update_reminder_day = $day;
        $department->save();

        // Confirmation notification to appear in navbar notifications
        if ($department->dean) {
            $department->dean->notify(new DeanReminderSettingsUpdatedNotification($department, $day));
        }

        return redirect()->route('dean.allocations.show', [
            'department' => $department->id,
            'month' => $request->input('month', now()->format('Y-m')),
        ])->with('success', $day ? 'Weekly reminder updated.' : 'Weekly reminder disabled.');
    }

    /**
     * Admin-only: remove an item from a department's monthly allocation (mark excluded).
     */
    public function removeItem(DepartmentMonthlyAllocationItem $item, Request $request)
    {
        $allocation = $item->allocation;
        $department = $allocation->department;
        $user = Auth::user();
        if (!$user || !$user->hasAdminPrivileges()) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'month' => 'required|date_format:Y-m',
        ]);

        // Mark item as excluded; clear any staging
        $attrs = (array)($item->attributes ?? []);
        $attrs['excluded'] = true;
        $item->attributes = $attrs;
        $item->issue_status = null;
        $item->staged_issue_qty = null;
        $item->save();

        return redirect()->route('dean.allocations.show', [
            'department' => $department->id,
            'month' => $validated['month'],
        ])->with('success', 'Item removed from monthly allocation.');
    }

    /**
     * Admin-only: add/include a previously excluded item into the monthly allocation.
     * Only allows inclusion when the inventory has available quantity.
     */
    public function addItem(Department $department, Request $request, DepartmentAllocationService $service)
    {
        $user = Auth::user();
        if (!$user || !$user->hasAdminPrivileges()) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'month' => 'required|date_format:Y-m',
            'supply_id' => 'required|integer|exists:supplies,id',
        ]);

        // Ensure allocation exists and is refreshed
        $allocation = $service->refreshForDepartmentMonth($department, $validated['month']);

        $supply = Supply::find((int)$validated['supply_id']);
        if (!$supply || !$supply->isActive()) {
            return redirect()->route('dean.allocations.show', [
                'department' => $department->id,
                'month' => $validated['month'],
            ])->with('error', 'Selected supply is not active.');
        }

        $available = (int)$supply->availableQuantity();
        if ($available <= 0) {
            return redirect()->route('dean.allocations.show', [
                'department' => $department->id,
                'month' => $validated['month'],
            ])->with('warning', 'Supply has zero available inventory; item not added.');
        }

        // Find or create the item for the chosen supply
        $item = DepartmentMonthlyAllocationItem::firstOrCreate(
            ['allocation_id' => $allocation->id, 'supply_id' => (int)$validated['supply_id']],
            [
                'min_stock_level' => (int)($supply->minimum_stock_level ?? 0),
                'issued_qty' => 0,
                'suggest_qty' => 0,
                'low_stock' => false,
                'attributes' => ['unit' => $supply->unit],
            ]
        );

        // Clear exclusion flag if present
        $attrs = (array)($item->attributes ?? []);
        unset($attrs['excluded']);
        $item->attributes = $attrs;
        $item->save();

        // Recompute values after inclusion
        $service->refreshForDepartmentMonth($department, $validated['month']);

        return redirect()->route('dean.allocations.show', [
            'department' => $department->id,
            'month' => $validated['month'],
        ])->with('success', 'Item added to monthly allocation.');
    }

    // Admin-only: add/include multiple inventory items into the monthly allocation.
    public function addMultipleItems(Department $department, Request $request, DepartmentAllocationService $service)
    {
        $user = Auth::user();
        if (!$user || !$user->hasAdminPrivileges()) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'month' => 'required|date_format:Y-m',
            'supply_ids' => 'required|array',
            'supply_ids.*' => 'integer|exists:supplies,id',
        ]);

        $month = $validated['month'];
        $supplyIds = array_unique(array_map('intval', $validated['supply_ids']));

        // Ensure allocation exists and is refreshed
        $allocation = $service->refreshForDepartmentMonth($department, $month);

        $addedCount = 0;
        $skipped = [];

        foreach ($supplyIds as $sid) {
            $supply = Supply::find($sid);
            if (!$supply || !$supply->isActive()) { continue; }

            $available = (int)$supply->availableQuantity();
            if ($available <= 0) {
                $skipped[] = $supply->name;
                continue;
            }

            $item = DepartmentMonthlyAllocationItem::firstOrCreate(
                ['allocation_id' => $allocation->id, 'supply_id' => $sid],
                [
                    'min_stock_level' => (int)($supply->minimum_stock_level ?? 0),
                    'issued_qty' => 0,
                    'suggest_qty' => 0,
                    'low_stock' => false,
                    'attributes' => ['unit' => $supply->unit],
                ]
            );

            // Clear exclusion flag if present
            $attrs = (array)($item->attributes ?? []);
            unset($attrs['excluded']);
            $item->attributes = $attrs;
            $item->save();

            $addedCount++;
        }

        // Recompute values after inclusion
        $service->refreshForDepartmentMonth($department, $month);

        $message = 'Items added to monthly allocation.';
        if ($addedCount > 0) {
            $message = "Added {$addedCount} item(s) to monthly allocation.";
        }

        $redirect = redirect()->route('dean.allocations.show', [
            'department' => $department->id,
            'month' => $month,
        ])->with('success', $message);

        if (!empty($skipped)) {
            $displayList = array_slice($skipped, 0, 5);
            $more = count($skipped) - count($displayList);
            $warning = 'Skipped due to zero availability: ' . implode(', ', $displayList);
            if ($more > 0) { $warning .= " and {$more} more"; }
            $redirect = $redirect->with('warning', $warning);
        }

        return $redirect;
    }
}
