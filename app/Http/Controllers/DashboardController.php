<?php

namespace App\Http\Controllers;

use App\Models\SupplyRequest;
use App\Models\BorrowedItem;
use App\Models\RestockRequest;
use App\Models\IssuedItem;
use App\Models\LoanRequest;
use App\Models\InterDepartmentLoanRequest;
use App\Models\Supply;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Sanitize paginator page query params to avoid type errors
        $pageParams = ['supply_requests_page', 'borrowed_items_page', 'restock_requests_page', 'issued_items_page'];
        foreach ($pageParams as $param) {
            $val = $request->query($param);
            if ($val !== null && !ctype_digit((string) $val)) {
                $request->merge([$param => 1]);
            }
        }
        
        // Search functionality
        $searchSupplyRequest = $request->get('search_supply_request');
        $searchBorrowedItem = $request->get('search_borrowed_item');
        $searchIssuedItem = $request->get('search_issued_item');

        // Supply Requests with search, pagination, and ordering
        $supplyRequests = SupplyRequest::when($searchSupplyRequest, function ($query, $searchSupplyRequest) {
            return $query->where('item_name', 'LIKE', "%{$searchSupplyRequest}%")
                ->orWhereHas('department', function ($query) use ($searchSupplyRequest) {
                    $query->where('department_name', 'LIKE', "%{$searchSupplyRequest}%");
                });
        })->orderBy('created_at', 'desc')->paginate(10, ['*'], 'supply_requests_page');

        // Borrowed Items with search, pagination, and ordering
        $borrowedItems = BorrowedItem::when($searchBorrowedItem, function ($query, $searchBorrowedItem) {
            return $query->whereHas('supply', function ($query) use ($searchBorrowedItem) {
                $query->where('name', 'LIKE', "%{$searchBorrowedItem}%");
            })->orWhereHas('department', function ($query) use ($searchBorrowedItem) {
                $query->where('department_name', 'LIKE', "%{$searchBorrowedItem}%");
            });
        })->orderBy('created_at', 'desc')->paginate(10, ['*'], 'borrowed_items_page');

        // Restock Requests with pagination and ordering
        $restockRequests = RestockRequest::orderBy('created_at', 'desc')->paginate(10, ['*'], 'restock_requests_page');

        // Issued Items with search, pagination, and ordering
        $issuedItems = IssuedItem::when($searchIssuedItem, function ($query, $searchIssuedItem) {
            return $query->where('item_name', 'LIKE', "%{$searchIssuedItem}%")
                ->orWhereHas('department', function ($query) use ($searchIssuedItem) {
                    $query->where('department_name', 'LIKE', "%{$searchIssuedItem}%");
                });
        })->orderBy('created_at', 'desc')->paginate(10, ['*'], 'issued_items_page');

        // Loan Requests for current user (students)
        $loanRequests = LoanRequest::with(['supply', 'department', 'requestedBy', 'approvedBy'])
            ->where('requested_by', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Inter-Department Loan Requests for current user (students)
        $interDepartmentLoanRequests = InterDepartmentLoanRequest::with([
            'issuedItem.supply', 
            'lendingDepartment', 
            'borrowingDepartment', 
            'requestedBy'
        ])
            ->where('requested_by', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Loan request statistics for students
        $loanRequestStats = [
            'pending' => $loanRequests->where('status', 'pending')->count(),
            'approved' => $loanRequests->where('status', 'approved')->count(),
            'declined' => $loanRequests->where('status', 'declined')->count(),
            'completed' => $loanRequests->where('status', 'completed')->count(),
        ];

        // Inter-department loan statistics for students
        $interDeptLoanStats = [
            'pending' => $interDepartmentLoanRequests->where('status', 'pending')->count(),
            'lending_approved' => $interDepartmentLoanRequests->where('status', 'lending_approved')->count(),
            'borrowing_confirmed' => $interDepartmentLoanRequests->where('status', 'borrowing_confirmed')->count(),
            'admin_approved' => $interDepartmentLoanRequests->where('status', 'admin_approved')->count(),
            'declined' => $interDepartmentLoanRequests->where('status', 'declined')->count(),
            'completed' => $interDepartmentLoanRequests->where('status', 'completed')->count(),
            'returned' => $interDepartmentLoanRequests->where('status', 'returned')->count(),
        ];

        // Pending requests count
        $pendingRequests = SupplyRequest::where('status', 'pending')->count();

        // Get low stock items (exclude borrowable supplies)
        $lowStockItems = \App\Models\Supply::where(function($query) {
            $query->whereColumn('quantity', '<=', 'minimum_stock_level')
                  ->orWhere(function($subQuery) {
                      $subQuery->whereNull('minimum_stock_level')
                               ->where('quantity', '<=', 10);
                  });
        })
        ->where('supply_type', '!=', \App\Models\Supply::TYPE_BORROWABLE)
        ->with(['categories', 'suppliers'])
        ->get();

        // Compute ordered supply ids and ordered-by/request maps
        $orderedRequests = RestockRequest::with('requestedDepartment')
            ->where('status', 'ordered')
            ->orderBy('created_at', 'desc')
            ->get(['id','supply_id','items_json','requested_department_id']);

        $orderedSupplyIds = [];
        $orderedByMap = [];
        $supplyToRequestId = [];

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

        // Collect low stock alerts (exclude borrowable supplies)
        $lowStockAlerts = RestockRequest::whereHas('supply', function ($query) {
            $query->where('supply_type', '!=', \App\Models\Supply::TYPE_BORROWABLE)
                  ->whereColumn('quantity', '<=', 'minimum_stock_level');
        })
        ->get()
        ->pluck('supply.name');

        // Aggregated missing and damaged totals from verified returns
        $missingItemsTotal = BorrowedItem::whereNotNull('return_verified_at')
            ->where('returned_status', 'returned_with_missing')
            ->sum('missing_count');
        $damagedItemsTotal = BorrowedItem::whereNotNull('return_verified_at')
            ->where('returned_status', 'returned_with_damage')
            ->sum('damaged_count');

        // Issued and Ordered activity metrics (daily/weekly/monthly)
        $today = Carbon::today();
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        $issuedDaily = IssuedItem::whereDate('issued_on', $today)->count();
        $issuedWeekly = IssuedItem::whereBetween('issued_on', [$startOfWeek, $endOfWeek])->count();
        $issuedMonthly = IssuedItem::whereBetween('issued_on', [$startOfMonth, $endOfMonth])->count();

        $orderedDaily = RestockRequest::where('status', 'ordered')->whereDate('updated_at', $today)->count();
        $orderedWeekly = RestockRequest::where('status', 'ordered')->whereBetween('updated_at', [$startOfWeek, $endOfWeek])->count();
        $orderedMonthly = RestockRequest::where('status', 'ordered')->whereBetween('updated_at', [$startOfMonth, $endOfMonth])->count();

        // Dean approval requests (for dean users only)
        $deanApprovalRequests = collect();
        $deanReturnApprovals = collect();
        if ($user && method_exists($user, 'hasRole') && $user->hasRole('dean')) {
            $deanApprovalRequests = LoanRequest::with(['supply', 'department', 'requestedBy'])
                ->where('department_id', $user->department_id)
                ->where('status', 'pending')
                ->whereNull('dean_approved_at')
                ->orderBy('updated_at', 'desc')
                ->get();

            // Pending return verifications for lending department
            $deanReturnApprovals = InterDepartmentLoanRequest::with(['issuedItem.supply','lendingDepartment','borrowingDepartment','interDepartmentBorrowedItems'])
                ->where('lending_department_id', $user->department_id)
                ->where(function($q){
                    $q->where('status', 'borrowing_confirmed')
                      ->orWhere('status', 'returned');
                })
                ->orderBy('updated_at', 'desc')
                ->get();
        }

        // Compute top 5 issued items (by quantity) for the current month, stacked by department
        $issuedBySupplyDept = IssuedItem::selectRaw('supply_id, department_id, SUM(quantity) as qty')
            ->whereBetween('issued_on', [$startOfMonth, $endOfMonth])
            ->groupBy('supply_id', 'department_id')
            ->get();

        $issuedTotalsBySupply = $issuedBySupplyDept->groupBy('supply_id')->map(function ($group) {
            return (int) $group->sum('qty');
        });
        $topIssuedSupplyIds = collect($issuedTotalsBySupply)->sortDesc()->keys()->take(5)->values()->all();
        $issuedSupplyNames = Supply::whereIn('id', $topIssuedSupplyIds)->pluck('name', 'id');

        $issuedDeptIds = $issuedBySupplyDept->whereIn('supply_id', $topIssuedSupplyIds)->pluck('department_id')->unique()->values();
        $issuedDeptNames = Department::whereIn('id', $issuedDeptIds)->pluck('department_name', 'id');

        $issuedDataByDept = [];
        foreach ($issuedDeptIds as $deptId) {
            $perSupply = [];
            foreach ($topIssuedSupplyIds as $sid) {
                $qty = (int) ($issuedBySupplyDept->firstWhere(fn($r) => $r->supply_id === $sid && $r->department_id === $deptId)->qty ?? 0);
                $perSupply[] = $qty;
            }
            $issuedDataByDept[$deptId] = $perSupply;
        }

        $issuedTopChart = [
            'labels' => array_map(function ($sid) use ($issuedSupplyNames) { return $issuedSupplyNames[$sid] ?? (string) $sid; }, $topIssuedSupplyIds),
            'departments' => array_map(function ($did) use ($issuedDeptNames) { return [ 'id' => $did, 'name' => $issuedDeptNames[$did] ?? (string) $did ]; }, $issuedDeptIds->all()),
            'series' => $issuedDataByDept,
            'title' => 'Top 5 Issued Items by Department (Monthly)'
        ];

        // Compute top 5 ordered items (by quantity) for the current month, stacked by department
        $orderedBySupplyDept = RestockRequest::selectRaw('supply_id, requested_department_id as department_id, SUM(quantity) as qty')
            ->where('status', 'ordered')
            ->whereBetween('updated_at', [$startOfMonth, $endOfMonth])
            ->groupBy('supply_id', 'requested_department_id')
            ->get();

        $orderedTotalsBySupply = $orderedBySupplyDept->groupBy('supply_id')->map(function ($group) {
            return (int) $group->sum('qty');
        });
        $topOrderedSupplyIds = collect($orderedTotalsBySupply)->sortDesc()->keys()->take(5)->values()->all();
        $orderedSupplyNames = Supply::whereIn('id', $topOrderedSupplyIds)->pluck('name', 'id');

        $orderedDeptIds = $orderedBySupplyDept->whereIn('supply_id', $topOrderedSupplyIds)->pluck('department_id')->unique()->values();
        $orderedDeptNames = Department::whereIn('id', $orderedDeptIds)->pluck('department_name', 'id');

        $orderedDataByDept = [];
        foreach ($orderedDeptIds as $deptId) {
            $perSupply = [];
            foreach ($topOrderedSupplyIds as $sid) {
                $qty = (int) ($orderedBySupplyDept->firstWhere(fn($r) => $r->supply_id === $sid && $r->department_id === $deptId)->qty ?? 0);
                $perSupply[] = $qty;
            }
            $orderedDataByDept[$deptId] = $perSupply;
        }

        $orderedTopChart = [
            'labels' => array_map(function ($sid) use ($orderedSupplyNames) { return $orderedSupplyNames[$sid] ?? (string) $sid; }, $topOrderedSupplyIds),
            'departments' => array_map(function ($did) use ($orderedDeptNames) { return [ 'id' => $did, 'name' => $orderedDeptNames[$did] ?? (string) $did ]; }, $orderedDeptIds->all()),
            'series' => $orderedDataByDept,
            'title' => 'Top 5 Ordered Items by Department (Monthly)'
        ];

        return view('dashboard', compact(
            'supplyRequests', 
            'borrowedItems', 
            'restockRequests', 
            'issuedItems', 
            'pendingRequests', 
            'searchSupplyRequest', 
            'searchBorrowedItem', 
            'searchIssuedItem', 
            'lowStockAlerts', 
            'lowStockItems',
            'orderedSupplyIds',
            'orderedByMap',
            'supplyToRequestId',
            'deanApprovalRequests',
            'deanReturnApprovals',
            'missingItemsTotal',
            'damagedItemsTotal',
            'loanRequestStats',
            'interDeptLoanStats',
            'issuedDaily', 'issuedWeekly', 'issuedMonthly',
            'orderedDaily', 'orderedWeekly', 'orderedMonthly',
            'issuedTopChart', 'orderedTopChart'
         ));
    }
}
