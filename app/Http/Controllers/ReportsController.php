<?php

namespace App\Http\Controllers;

use App\Models\BorrowedItem;
use App\Models\IssuedItem;
use App\Models\RestockRequest;
use App\Models\Department;
use App\Models\Supply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Carbon\Carbon;

class ReportsController extends Controller
{
    /**
     * Show list of items contributing to missing totals.
     */
    public function missingItems(Request $request)
    {
        $query = BorrowedItem::with(['supply', 'department', 'user', 'loanRequest.variant'])
            ->whereNotNull('return_verified_at')
            ->where('missing_count', '>', 0);

        // Date filters: month (YYYY-MM) or custom range
        if ($request->filled('month')) {
            try {
                $month = Carbon::createFromFormat('Y-m', $request->month);
                $start = $month->copy()->startOfMonth();
                $end = $month->copy()->endOfMonth();
                $query->whereBetween('return_verified_at', [$start, $end]);
            } catch (\Exception $e) {
                // ignore invalid month format
            }
        } else {
            if ($request->filled('from')) {
                $query->whereDate('return_verified_at', '>=', $request->from);
            }
            if ($request->filled('to')) {
                $query->whereDate('return_verified_at', '<=', $request->to);
            }
        }

        $items = $query->orderByDesc('return_verified_at')->get();
        $totalMissing = (clone $query)->sum('missing_count');

        if ($request->query('export') === 'csv') {
            $grouped = $items->groupBy(function($it){
                return $it->department->department_name ?? 'N/A';
            })->sortKeys();

            $rows = [];
            $preparedBy = $request->user()?->name ?? 'N/A';
            $rows[] = ['Prepared by', $preparedBy];
            $rows[] = ['Generated at', now()->format('Y-m-d H:i')];
            $rows[] = [''];
            foreach ($grouped as $deptName => $group) {
                $rows[] = ['Department', $deptName];
                $rows[] = ['Supply', 'Department', 'Borrower', 'Variant', 'Missing Count', 'Verified At'];
                foreach ($group as $it) {
                    $rows[] = [
                        $it->supply->name ?? 'N/A',
                        $deptName,
                        $it->user->name ?? 'N/A',
                        optional($it->loanRequest?->variant)->display_name ?? optional($it->loanRequest?->variant)->variant_name ?? '—',
                        (string) ($it->missing_count ?? 0),
                        optional($it->return_verified_at)->format('Y-m-d'),
                    ];
                }
                $rows[] = ['', '', '', 'Subtotal', (string) $group->sum('missing_count'), ''];
                $rows[] = [''];
            }

            $csv = $this->toCsv($rows);
            return response($csv, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="missing_items.csv"'
            ]);
        }

        return view('reports.missing_items', compact('items', 'totalMissing'));
    }

    /**
     * Show list of items contributing to damaged totals.
     */
    public function damagedItems(Request $request)
    {
        $query = BorrowedItem::with(['supply', 'department', 'user', 'loanRequest.variant'])
            ->whereNotNull('return_verified_at')
            ->where('damaged_count', '>', 0);

        // Date filters: month (YYYY-MM) or custom range
        if ($request->filled('month')) {
            try {
                $month = Carbon::createFromFormat('Y-m', $request->month);
                $start = $month->copy()->startOfMonth();
                $end = $month->copy()->endOfMonth();
                $query->whereBetween('return_verified_at', [$start, $end]);
            } catch (\Exception $e) {
                // ignore invalid month format
            }
        } else {
            if ($request->filled('from')) {
                $query->whereDate('return_verified_at', '>=', $request->from);
            }
            if ($request->filled('to')) {
                $query->whereDate('return_verified_at', '<=', $request->to);
            }
        }

        $items = $query->orderByDesc('return_verified_at')->get();
        $totalDamaged = (clone $query)->sum('damaged_count');

        if ($request->query('export') === 'csv') {
            $grouped = $items->groupBy(function($it){
                return $it->department->department_name ?? 'N/A';
            })->sortKeys();

            $rows = [];
            $preparedBy = $request->user()?->name ?? 'N/A';
            $rows[] = ['Prepared by', $preparedBy];
            $rows[] = ['Generated at', now()->format('Y-m-d H:i')];
            $rows[] = [''];
            foreach ($grouped as $deptName => $group) {
                $rows[] = ['Department', $deptName];
                $rows[] = ['Supply', 'Department', 'Borrower', 'Variant', 'Damaged Count', 'Severity', 'Verified At'];
                foreach ($group as $it) {
                    $rows[] = [
                        $it->supply->name ?? 'N/A',
                        $deptName,
                        $it->user->name ?? 'N/A',
                        optional($it->loanRequest?->variant)->display_name ?? optional($it->loanRequest?->variant)->variant_name ?? '—',
                        (string) ($it->damaged_count ?? 0),
                        (string) ($it->damage_severity ?? ''),
                        optional($it->return_verified_at)->format('Y-m-d'),
                    ];
                }
                $rows[] = ['', '', '', 'Subtotal', (string) $group->sum('damaged_count'), '', ''];
                $rows[] = [''];
            }

            $csv = $this->toCsv($rows);
            return response($csv, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="damaged_items.csv"'
            ]);
        }

        return view('reports.damaged_items', compact('items', 'totalDamaged'));
    }

    /**
     * Issued Items Activity report with Daily/Weekly/Monthly periods and optional CSV export
     */
    public function issuedActivity(Request $request)
    {
        $period = $request->query('period', 'monthly'); // 'daily' | 'weekly' | 'monthly'
        [$start, $end] = $this->resolvePeriodRange($period);

        $supplyId = $request->query('supply_id');
        $departmentId = $request->query('department_id');

        $query = IssuedItem::with(['supply', 'department', 'user', 'issuedBy'])
            ->whereBetween('issued_on', [$start, $end])
            ->orderByDesc('issued_on');

        if (!empty($supplyId)) {
            $query->where('supply_id', $supplyId);
        }
        if (!empty($departmentId)) {
            $query->where('department_id', $departmentId);
        }

        $total = (clone $query)->count();
        $items = $query->paginate(15)->withQueryString();

        if ($request->query('export') === 'csv') {
            // Fetch full matching dataset for export (not just current page)
            $allItems = (clone $query)->get();

            // Group items by department name (fallback to N/A)
            $grouped = $allItems->groupBy(function($it) {
                return $it->department->department_name ?? 'N/A';
            })->sortKeys();

            $rows = [];
            $preparedBy = $request->user()?->name ?? 'N/A';
            $rows[] = ['Prepared by', $preparedBy];
            $rows[] = ['Generated at', now()->format('Y-m-d H:i')];
            $rows[] = [''];
            foreach ($grouped as $deptName => $group) {
                // Group header row
                $rows[] = ['Department', $deptName];
                // Column headers per group
                $rows[] = ['Supply', 'Department', 'Received By', 'Issued By', 'Quantity', 'Issued On'];

                foreach ($group as $it) {
                    $rows[] = [
                        $it->supply->name ?? 'N/A',
                        $deptName,
                        $it->user->name ?? 'N/A',
                        $it->issuedBy->name ?? 'N/A',
                        (string) $it->quantity,
                        optional($it->issued_on)->format('Y-m-d'),
                    ];
                }

                // Subtotal row for the department
                $rows[] = ['', '', '', 'Subtotal', (string) $group->sum('quantity'), ''];
                // Separator blank row
                $rows[] = [''];
            }

            $csv = $this->toCsv($rows);
            return response($csv, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="issued_activity_'.$period.'.csv"'
            ]);
        }

        $summary = [
            'period' => $period,
            'start' => $start->format('Y-m-d'),
            'end' => $end->format('Y-m-d'),
            'count' => $total,
        ];

        $supplies = Supply::orderBy('name')->get(['id', 'name']);
        $departments = Department::orderBy('department_name')->get(['id', 'department_name']);

        return view('reports.issued_activity', compact('items', 'summary', 'supplies', 'departments'));
    }

    /**
     * Ordered Items Activity report with Daily/Weekly/Monthly periods and optional CSV export
     */
    public function orderedActivity(Request $request)
    {
        $period = $request->query('period', 'monthly'); // 'daily' | 'weekly' | 'monthly'
        [$start, $end] = $this->resolvePeriodRange($period);

        $query = RestockRequest::with(['supply', 'requestedDepartment', 'supplier'])
            ->where('status', 'ordered')
            ->whereBetween('updated_at', [$start, $end])
            ->orderByDesc('updated_at');

        // Search filter: match supply name, supplier name, or department name
        if ($request->filled('search')) {
            $search = trim($request->query('search'));
            $query->where(function($q) use ($search) {
                $q->whereHas('supply', function($sq) use ($search) {
                    $sq->where('name', 'like', "%{$search}%");
                })
                ->orWhereHas('supplier', function($sq) use ($search) {
                    $sq->where('name', 'like', "%{$search}%");
                })
                ->orWhereHas('requestedDepartment', function($dq) use ($search) {
                    $dq->where('department_name', 'like', "%{$search}%");
                });
            });
        }

        // Department filter: by requested department id
        if ($request->filled('department_id')) {
            $query->where('requested_department_id', $request->query('department_id'));
        }

        $total = (clone $query)->count();
        $orders = $query->paginate(15)->withQueryString();

        if ($request->query('export') === 'csv') {
            $rows = [];
            $preparedBy = $request->user()?->name ?? 'N/A';
            $rows[] = ['Prepared by', $preparedBy];
            $rows[] = ['Generated at', now()->format('Y-m-d H:i')];
            $rows[] = [''];
            // Fetch full matching dataset for export (not just current page)
            $allOrders = (clone $query)->get();

            // Group by Department, then by Supplier within each Department
            $groupedByDept = $allOrders->groupBy(function ($ord) {
                return $ord->requestedDepartment->department_name ?? 'N/A';
            })->sortKeys();

            foreach ($groupedByDept as $deptName => $deptOrders) {
                // Department header
                $rows[] = ['Department', $deptName];

                $groupedBySupplier = $deptOrders->groupBy(function ($ord) {
                    return $ord->supplier->name ?? 'N/A';
                })->sortKeys();

                foreach ($groupedBySupplier as $supplierName => $supplierOrders) {
                    // Supplier header
                    $rows[] = ['Supplier', $supplierName];
                    // Column headers within supplier group
                    $rows[] = ['Supply', 'Supplier', 'Department', 'Quantity', 'Ordered At'];

                    foreach ($supplierOrders as $ord) {
                        $rows[] = [
                            $ord->supply->name ?? 'N/A',
                            $supplierName,
                            $deptName,
                            (string) ($ord->quantity ?? ''),
                            optional($ord->updated_at)->format('Y-m-d'),
                        ];
                    }

                    // Supplier subtotal and separator
                    $rows[] = ['', '', '', 'Subtotal', (string) $supplierOrders->sum('quantity'), ''];
                    $rows[] = [''];
                }

                // Department subtotal and separator
                $rows[] = ['', '', '', 'Department Total', (string) $deptOrders->sum('quantity'), ''];
                $rows[] = [''];
            }

            $csv = $this->toCsv($rows);
            return response($csv, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="ordered_activity_'.$period.'.csv"'
            ]);
        }

        $summary = [
            'period' => $period,
            'start' => $start->format('Y-m-d'),
            'end' => $end->format('Y-m-d'),
            'count' => $total,
        ];

        $departments = \App\Models\Department::orderBy('department_name')->get(['id','department_name']);

        return view('reports.ordered_activity', compact('orders', 'summary', 'departments'));
    }

    private function resolvePeriodRange(string $period): array
    {
        $now = Carbon::now();
        switch ($period) {
            case 'daily':
                $start = $now->copy()->startOfDay();
                $end = $now->copy()->endOfDay();
                break;
            case 'weekly':
                $start = $now->copy()->startOfWeek();
                $end = $now->copy()->endOfWeek();
                break;
            case 'monthly':
            default:
                $start = $now->copy()->startOfMonth();
                $end = $now->copy()->endOfMonth();
                break;
        }
        return [$start, $end];
    }

    private function toCsv(array $rows): string
    {
        $fh = fopen('php://temp', 'r+');
        foreach ($rows as $row) {
            fputcsv($fh, $row);
        }
        rewind($fh);
        $csv = stream_get_contents($fh);
        fclose($fh);
        return $csv;
    }
}
