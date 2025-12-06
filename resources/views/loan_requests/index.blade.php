@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    @php $tab = $tab ?? request('tab', 'standard'); @endphp
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-semibold">Borrow Requests</h1>
        <button type="button" id="newLoanBtn" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">New Borrow Request</button>
    </div>

    <!-- Tabs: Standard / Inter-Department -->
    <div class="mb-4 flex gap-2">
        <a href="{{ request()->fullUrlWithQuery(['tab' => 'standard']) }}" class="px-3 py-2 rounded border {{ $tab === 'standard' ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-blue-600 border-blue-600 hover:bg-blue-50' }}">Standard</a>
        <a href="{{ request()->fullUrlWithQuery(['tab' => 'inter']) }}" class="px-3 py-2 rounded border {{ $tab === 'inter' ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-blue-600 border-blue-600 hover:bg-blue-50' }}">Inter-Department</a>
    </div>

    <form method="GET" action="{{ route('loan-requests.index') }}" class="mb-4 grid grid-cols-1 gap-3 md:grid-cols-3">
        <div>
            <label class="block text-sm font-medium text-gray-700">Search</label>
            <input type="text" name="search" value="{{ request('search') }}" class="mt-1 block w-full border rounded px-3 py-2" placeholder="Search by supply, department, or purpose" />
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Status</label>
            <select name="status" class="mt-1 block w-full border rounded px-3 py-2">
                @php
                    $statuses = ['', 'pending', 'approved', 'borrowed', 'return_pending', 'declined', 'completed'];
                @endphp
                @foreach($statuses as $status)
                    <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>
                        {{ $status === '' ? 'All' : ucfirst($status) }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="flex items-end">
            <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Filter</button>
        </div>
    </form>


    @if($tab === 'standard')
        <div class="bg-white shadow rounded overflow-x-auto">
            <div class="px-4 py-3 border-b bg-gray-50 flex items-center justify-between">
                <div class="flex items-baseline gap-3">
                    <h2 class="text-lg font-semibold">Standard Borrow Requests</h2>
                    <div class="text-sm text-gray-600">
                        @if($loanRequests->count())
                            Showing {{ $loanRequests->firstItem() }}–{{ $loanRequests->lastItem() }} of {{ $loanRequests->total() }} · Page {{ $loanRequests->currentPage() }} of {{ $loanRequests->lastPage() }}
                        @endif
                    </div>
                </div>
                <div>
                    @if($loanRequests->hasPages())
                        {{ $loanRequests->withQueryString()->links() }}
                    @endif
                </div>
            </div>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Batch / Request</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requested By</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @php $shownBatchIds = []; @endphp
                    @forelse($loanRequests as $lr)
                        @php $batchId = $lr->batch?->id; @endphp
                        @if($batchId)
                            @if(!in_array($batchId, $shownBatchIds))
                                @php
                                    $shownBatchIds[] = $batchId;
                                    $batchItems = $lr->batch->items()->with('borrowedItem')->get();
                                    $statuses = $batchItems->pluck('status');
                                    $hasReturnPending = $batchItems->contains(function($item){
                                        return $item->borrowedItem && is_null($item->borrowedItem->returned_at) && !is_null($item->borrowedItem->return_pending_at);
                                    });
                                    $hasBorrowed = !$hasReturnPending && $batchItems->contains(function($item){
                                        return $item->borrowedItem && is_null($item->borrowedItem->returned_at);
                                    });
                                    $allApproved = $statuses->count() > 0 && $statuses->every(fn($s) => $s === 'approved');
                                    $allDeclined = $statuses->count() > 0 && $statuses->every(fn($s) => $s === 'declined');
                                    $isPartial = $statuses->contains('approved') || $statuses->contains('declined');
                                    $displayStatus = 'pending';
                                    if ($hasReturnPending) {
                                        $displayStatus = 'return_pending';
                                    } elseif ($hasBorrowed) {
                                        $displayStatus = 'borrowed';
                                    } elseif ($allApproved) {
                                        $displayStatus = 'approved';
                                    } elseif ($allDeclined) {
                                        $displayStatus = 'declined';
                                    } elseif ($isPartial) {
                                        $displayStatus = 'partial';
                                    }
                                    $statusClass = $displayStatus === 'approved' ? 'bg-green-100 text-green-800' : (
                                        $displayStatus === 'return_pending' ? 'bg-yellow-100 text-yellow-800' : (
                                        $displayStatus === 'borrowed' ? 'bg-indigo-100 text-indigo-800' : (
                                        $displayStatus === 'declined' ? 'bg-red-100 text-red-800' : (
                                        $displayStatus === 'completed' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800'))));
                                    $firstItem = $lr->batch->items()->orderBy('id')->first();
                                    $itemsCount = $lr->batch->items()->count();
                                @endphp
                                <tr>
                                    <td class="px-4 py-3 text-sm">
                                        @if($firstItem)
                                            <a href="{{ route('loan-requests.show', $firstItem) }}" class="text-blue-600 hover:underline">Batch #{{ $batchId }} ({{ $itemsCount }} item{{ $itemsCount === 1 ? '' : 's' }})</a>
                                        @else
                                            Batch #{{ $batchId }}
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        @if($lr->department)
                                            <a href="{{ route('departments.show', $lr->department) }}" class="text-blue-600 hover:underline">
                                                {{ $lr->department->department_name ?? $lr->department->name }}
                                            </a>
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        @if($lr->requestedBy)
                                            <a href="{{ route('users.profile', $lr->requestedBy) }}" class="text-blue-600 hover:underline">
                                                {{ $lr->requestedBy->name }}
                                            </a>
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <span class="inline-block px-2 py-1 rounded text-xs {{ $statusClass }}">
                                            {{ ucfirst(str_replace('_', ' ', $displayStatus)) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        @if($lr->batch->expected_return_date)
                                            {{ is_string($lr->batch->expected_return_date) ? \Carbon\Carbon::parse($lr->batch->expected_return_date)->format('M j, Y') : $lr->batch->expected_return_date->format('M j, Y') }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm">{{ \Carbon\Carbon::parse($lr->batch->created_at)->format('M j, Y') }}</td>
                                </tr>
                            @endif
                        @else
                            <tr>
                                <td class="px-4 py-3 text-sm">
                                    <a href="{{ route('loan-requests.show', $lr) }}" class="text-blue-600 hover:underline">Request #{{ $lr->id }}</a>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    @if($lr->department)
                                        <a href="{{ route('departments.show', $lr->department) }}" class="text-blue-600 hover:underline">
                                            {{ $lr->department->department_name ?? $lr->department->name }}
                                        </a>
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    @if($lr->requestedBy)
                                        <a href="{{ route('users.profile', $lr->requestedBy) }}" class="text-blue-600 hover:underline">
                                            {{ $lr->requestedBy->name }}
                                        </a>
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    @php
                                        $status = $lr->status ?? 'pending';
                                        $bi = $lr->borrowedItem;
                                        $displayStatus = $status;
                                        if ($bi) {
                                            if (is_null($bi->returned_at) && !is_null($bi->return_pending_at)) {
                                                $displayStatus = 'return_pending';
                                            } elseif (is_null($bi->returned_at)) {
                                                $displayStatus = 'borrowed';
                                            }
                                        }
                                        $statusClass = $displayStatus === 'approved' ? 'bg-green-100 text-green-800' : (
                                            $displayStatus === 'return_pending' ? 'bg-yellow-100 text-yellow-800' : (
                                            $displayStatus === 'borrowed' ? 'bg-indigo-100 text-indigo-800' : (
                                            $displayStatus === 'declined' ? 'bg-red-100 text-red-800' : (
                                            $displayStatus === 'completed' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800'))));
                                    @endphp
                                    <span class="inline-block px-2 py-1 rounded text-xs {{ $statusClass }}">
                                        {{ ucfirst(str_replace('_', ' ', $displayStatus)) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    @if($lr->expected_return_date)
                                        {{ is_string($lr->expected_return_date) ? \Carbon\Carbon::parse($lr->expected_return_date)->format('M j, Y') : $lr->expected_return_date->format('M j, Y') }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm">{{ \Carbon\Carbon::parse($lr->created_at)->format('M j, Y') }}</td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-6 text-center text-sm text-gray-500">No borrow requests found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
    @endif

    @if(($tab === 'inter') && isset($interDeptRequests))
        <div class="bg-white shadow rounded overflow-x-auto mt-8">
            <div class="px-4 py-3 border-b bg-gray-50 flex items-center justify-between">
                <div class="flex items-baseline gap-3">
                    <h2 class="text-lg font-semibold">Inter-Department Borrow Requests</h2>
                    <div class="text-sm text-gray-600">
                        @if($interDeptRequests->count())
                            Showing {{ $interDeptRequests->firstItem() }}–{{ $interDeptRequests->lastItem() }} of {{ $interDeptRequests->total() }} · Page {{ $interDeptRequests->currentPage() }} of {{ $interDeptRequests->lastPage() }}
                        @endif
                    </div>
                </div>
                <div>
                    @if($interDeptRequests->hasPages())
                        {{ $interDeptRequests->withQueryString()->links() }}
                    @endif
                </div>
            </div>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Request ID</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lending Dept</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Borrowing Dept</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requested By</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($interDeptRequests as $idr)
                        <tr>
                            <td class="px-4 py-3 text-sm">
                                <a href="{{ route('loan-requests.inter-department.show', $idr) }}" class="text-blue-600 hover:underline">Request #{{ $idr->id }}</a>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                @if($idr->lendingDepartment)
                                    <a href="{{ route('departments.show', $idr->lendingDepartment) }}" class="text-blue-600 hover:underline">
                                        {{ $idr->lendingDepartment->department_name ?? $idr->lendingDepartment->name }}
                                    </a>
                                @else
                                    <span class="text-gray-500">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm">
                                @if($idr->borrowingDepartment)
                                    <a href="{{ route('departments.show', $idr->borrowingDepartment) }}" class="text-blue-600 hover:underline">
                                        {{ $idr->borrowingDepartment->department_name ?? $idr->borrowingDepartment->name }}
                                    </a>
                                @else
                                    <span class="text-gray-500">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm">
                                @if($idr->requestedBy)
                                    @can('view', $idr->requestedBy)
                                        @php $profileUrl = route('users.profile', $idr->requestedBy); @endphp
                                        <a href="{{ $profileUrl }}" class="text-blue-600 hover:underline">
                                            {{ $idr->requestedBy->name }}
                                        </a>
                                    @else
                                        <span class="text-gray-700">{{ $idr->requestedBy->name }}</span>
                                    @endcan
                                @else
                                    <span class="text-gray-500">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <span class="inline-block px-2 py-1 rounded text-xs 
                                    {{
                                        $idr->status === 'approved' ? 'bg-green-100 text-green-800' : (
                                        $idr->status === 'borrowed' ? 'bg-indigo-100 text-indigo-800' : (
                                        $idr->status === 'declined' ? 'bg-red-100 text-red-800' : (
                                        $idr->status === 'completed' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800')))
                                    }}">
                                    {{ ucfirst(str_replace('_', ' ', $idr->status)) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm">{{ \Carbon\Carbon::parse($idr->created_at)->format('M j, Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-6 text-center text-sm text-gray-500">No inter-department borrow requests found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
    @endif

    <!-- New Loan Request Selection Modal -->
    <div id="newLoanModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden">
        <div class="bg-white rounded shadow p-6 w-full max-w-md">
            <h2 class="text-xl font-semibold mb-4">Select Borrow Request Type</h2>
            <div class="space-y-3">
                <a href="{{ route('loan-requests.create') }}" class="block w-full text-center px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Standard Borrow Request</a>
                <a href="{{ route('loan-requests.inter-department.create') }}" class="block w-full text-center px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Inter-Department Borrow Request</a>
            </div>
            <div class="mt-4 text-right">
                <button type="button" id="closeNewLoanModal" class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300">Close</button>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const btn = document.getElementById('newLoanBtn');
            const modal = document.getElementById('newLoanModal');
            const closeBtn = document.getElementById('closeNewLoanModal');
            if (btn && modal && closeBtn) {
                btn.addEventListener('click', () => modal.classList.remove('hidden'));
                closeBtn.addEventListener('click', () => modal.classList.add('hidden'));
                modal.addEventListener('click', (e) => {
                    if (e.target === modal) modal.classList.add('hidden');
                });
            }
        });
    </script>
</div>
@endsection