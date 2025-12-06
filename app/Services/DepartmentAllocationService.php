<?php

namespace App\Services;

use App\Models\Department;
use App\Models\DepartmentMonthlyAllocation;
use App\Models\DepartmentMonthlyAllocationItem;
use App\Models\Supply;
use App\Models\IssuedItem;
use App\Models\DepartmentCart;
use App\Models\DepartmentCartItem;
use App\Models\RestockRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use App\Notifications\DepartmentAllocationLowStockNotification;

class DepartmentAllocationService
{
    /**
     * Ensure an allocation exists and refresh its items for the given department and month.
     * Month format: YYYY-MM
     */
    public function refreshForDepartmentMonth(Department $department, string $month): DepartmentMonthlyAllocation
    {
        $allocation = DepartmentMonthlyAllocation::firstOrCreate(
            ['department_id' => $department->id, 'month' => $month],
            ['status' => 'open', 'created_by' => Auth::id()]
        );

        // Date window for the month
        [$start, $end] = $this->monthDateRange($month);

        $lowItems = [];
        $supplies = Supply::active()->consumable()->orderBy('name')->get();
        foreach ($supplies as $supply) {
            $issuedQty = IssuedItem::where('department_id', $department->id)
                ->where('supply_id', $supply->id)
                ->whereBetween('issued_on', [$start, $end])
                ->sum('quantity');

            $minLevel = (int)($supply->minimum_stock_level ?? 0);
            $suggestQty = max($minLevel - (int)$issuedQty, 0);
            $lowStock = $issuedQty < $minLevel;

            // Preserve existing JSON attributes (e.g., audited actual_available)
            $existingItem = DepartmentMonthlyAllocationItem::where('allocation_id', $allocation->id)
                ->where('supply_id', $supply->id)
                ->first();
            $existingAttributes = $existingItem ? (array)($existingItem->attributes ?? []) : [];
            // Respect exclusion: skip refresh/update for explicitly excluded items
            if (!empty($existingAttributes['excluded'])) {
                continue;
            }
            $newAttributes = $existingAttributes;
            $newAttributes['unit'] = $supply->unit;

            $item = DepartmentMonthlyAllocationItem::updateOrCreate(
                ['allocation_id' => $allocation->id, 'supply_id' => $supply->id],
                [
                    'min_stock_level' => $minLevel,
                    'issued_qty' => (int)$issuedQty,
                    'suggest_qty' => (int)$suggestQty,
                    'low_stock' => (bool)$lowStock,
                    'attributes' => $newAttributes,
                ]
            );

            if ($lowStock && $suggestQty > 0) {
                $lowItems[] = $item;
            }
        }

        // Notify dean if any low-stock items
        if (!empty($lowItems)) {
            $dean = \App\Models\User::getDeanOfDepartment($department->id);
            if ($dean) {
                Notification::send($dean, new DepartmentAllocationLowStockNotification($department, $month, count($lowItems)));
            }
        }

        return $allocation->load(['items.supply']);
    }

    /**
     * Add low-stock suggestions into the department cart for the allocation's month.
     */
    public function refreshLowStockCart(Department $department, string $month): void
    {
        $allocation = $this->refreshForDepartmentMonth($department, $month);
        $cart = DepartmentCart::forDepartment($department->id);

        // Remove previous low-stock suggestions for this month
        DepartmentCartItem::where('cart_id', $cart->id)
            ->where('status', 'pending')
            ->whereJsonContains('attributes->source', 'low_stock_suggestion')
            ->whereJsonContains('attributes->month', $month)
            ->delete();

        foreach ($allocation->items as $item) {
            if ($item->low_stock && $item->suggest_qty > 0) {
                DepartmentCartItem::create([
                    'cart_id' => $cart->id,
                    'supply_id' => $item->supply_id,
                    'item_name' => $item->supply->name,
                    'unit' => $item->supply->unit,
                    'quantity' => (int)$item->suggest_qty,
                    'item_type' => DepartmentCartItem::TYPE_CONSUMABLE,
                    'attributes' => [
                        'source' => 'low_stock_suggestion',
                        'month' => $month,
                        'requested_by' => Auth::id(),
                    ],
                    'status' => 'pending',
                ]);
            }
        }
    }

    /**
     * Automated replenishment: issue exactly the quantity needed to reach each item's max_limit.
     * - Computes current department stock per supply (sum of IssuedItem.available_quantity).
     * - Issues from store or variants up to need amount; prevents over-issuance.
     * - Creates RestockRequest for any shortfall with status 'ordered'.
     * Returns a summary of actions.
     */
    public function replenishToMax(Department $department, string $month): array
    {
        $allocation = $this->refreshForDepartmentMonth($department, $month);

        $summary = [
            'department_id' => $department->id,
            'month' => $month,
            'issued' => [], // [supply_id => qty]
            'restock_requests' => [], // [supply_id => qty]
            'skipped' => [], // supplies with no need or max_limit = 0
        ];

        DB::transaction(function () use ($department, $month, $allocation, &$summary) {
            $batch = IssuedItemBatch::create([
                'department_id' => $department->id,
                'user_id' => null,
                'issued_by' => Auth::id(),
                'issued_on' => now(),
                'notes' => 'Auto-replenishment to max limit for ' . $department->department_name . ' (' . $month . ')',
            ]);

            foreach ($allocation->items as $item) {
                $max = (int) ($item->max_limit ?? 0);
                if ($max <= 0) {
                    $summary['skipped'][] = [
                        'supply_id' => $item->supply_id,
                        'reason' => 'max_limit_not_set',
                    ];
                    continue;
                }

                // Current department stock: sum of available quantities across issued records for this supply
                $currentDeptStock = IssuedItem::where('department_id', $department->id)
                    ->where('supply_id', $item->supply_id)
                    ->get()
                    ->sum(function ($issued) {
                        return (int) ($issued->available_quantity ?? 0);
                    });

                $need = $max - $currentDeptStock;
                if ($need <= 0) {
                    $summary['skipped'][] = [
                        'supply_id' => $item->supply_id,
                        'reason' => 'already_at_or_above_max',
                    ];
                    continue;
                }

                $supply = $item->supply; // loaded by refresh
                if (!$supply || !$supply->isActive()) {
                    $summary['skipped'][] = [
                        'supply_id' => $item->supply_id,
                        'reason' => 'inactive_or_missing_supply',
                    ];
                    continue;
                }

                $issueQtyTotal = 0;
                $availableStore = $supply->availableQuantity();
                $toIssue = min($need, $availableStore);

                if ($toIssue > 0) {
                    // Issue from variants if present; otherwise issue from supply-level
                    if ($supply->hasVariants()) {
                        $remaining = $toIssue;
                        // Prefer variants with highest available quantities
                        $variants = $supply->variants()->where('status', 'active')->orderByDesc('quantity')->get();
                        foreach ($variants as $variant) {
                            if ($remaining <= 0) { break; }
                            $vAvail = $variant->availableQuantity();
                            if ($vAvail <= 0) { continue; }
                            $chunk = min($remaining, $vAvail);

                            // Deduct variant stock and create issued item
                            $variant->decrement('quantity', $chunk);

                            IssuedItem::create([
                                'batch_id' => $batch->id,
                                'supply_id' => $supply->id,
                                'supply_variant_id' => $variant->id,
                                'department_id' => $department->id,
                                'user_id' => null,
                                'quantity' => $chunk,
                                'issued_on' => now(),
                                'notes' => 'Auto-replenishment to max (' . $month . ')',
                                'issued_by' => Auth::id(),
                                'available_for_borrowing' => false,
                                'borrowed_quantity' => 0,
                            ]);

                            $issueQtyTotal += $chunk;
                            $remaining -= $chunk;
                        }
                    } else {
                        // Non-variant: deduct from supply stock and issue
                        $supply->decrement('quantity', $toIssue);

                        IssuedItem::create([
                            'batch_id' => $batch->id,
                            'supply_id' => $supply->id,
                            'department_id' => $department->id,
                            'user_id' => null,
                            'quantity' => $toIssue,
                            'issued_on' => now(),
                            'notes' => 'Auto-replenishment to max (' . $month . ')',
                            'issued_by' => Auth::id(),
                            'available_for_borrowing' => false,
                            'borrowed_quantity' => 0,
                        ]);

                        $issueQtyTotal += $toIssue;
                    }
                }

                // If we couldn't fully satisfy need, create restock request for the shortfall
                $shortfall = $need - $issueQtyTotal;
                if ($shortfall > 0) {
                    RestockRequest::create([
                        'supply_id' => $supply->id,
                        'quantity' => $shortfall,
                        'status' => 'ordered',
                        'supplier_id' => null,
                        'requested_department_id' => $department->id,
                    ]);
                    $summary['restock_requests'][] = [
                        'supply_id' => $supply->id,
                        'quantity' => $shortfall,
                    ];
                }

                if ($issueQtyTotal > 0) {
                    $summary['issued'][] = [
                        'supply_id' => $supply->id,
                        'quantity' => $issueQtyTotal,
                    ];
                }
            }
        });

        return $summary;
    }

    /**
     * Return Carbon date range for a given month string.
     */
    private function monthDateRange(string $month): array
    {
        $start = \Carbon\Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $end = \Carbon\Carbon::createFromFormat('Y-m', $month)->endOfMonth();
        return [$start, $end];
    }
}