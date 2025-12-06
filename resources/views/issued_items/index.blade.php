@extends('layouts.app')

@section('title', 'Issued Items')

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-share-square text-primary me-2"></i>
                Issued Items Management
            </h1>
            <p class="text-muted mb-0">Track and manage all issued supply items</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('issued-items.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i>
                Issue New Item
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Issued Items
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['total_issued']) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-share-square fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Issued Today
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['issued_today']) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-day fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                This Month
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['issued_this_month']) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Filters and Search -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-filter me-1"></i>
                Filters & Search
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('issued-items.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="{{ request('search') }}" 
                           placeholder="Search items, departments, notes...">
                </div>

                <div class="col-md-2">
                    <label for="department_id" class="form-label">Department</label>
                    <select class="form-select" id="department_id" name="department_id">
                        <option value="">All Departments</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}" 
                                    {{ request('department_id') == $department->id ? 'selected' : '' }}>
                                {{ $department->department_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label for="category_id" class="form-label">Category</label>
                    <select class="form-select" id="category_id" name="category_id">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" 
                                    {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label for="supply_type" class="form-label">Supply Type</label>
                    <select class="form-select" id="supply_type" name="supply_type">
                        <option value="">All Types</option>
                        <option value="consumable" {{ request('supply_type') == 'consumable' ? 'selected' : '' }}>
                            Consumable
                        </option>
                        <option value="grantable" {{ request('supply_type') == 'grantable' ? 'selected' : '' }}>
                            Grantable
                        </option>
                    </select>
                </div>

                <div class="col-md-1">
                    <label for="date_from" class="form-label">From Date</label>
                    <input type="date" class="form-control" id="date_from" name="date_from" 
                           value="{{ request('date_from') }}">
                </div>

                <div class="col-md-1">
                    <label for="date_to" class="form-label">To Date</label>
                    <input type="date" class="form-control" id="date_to" name="date_to" 
                           value="{{ request('date_to') }}">
                </div>

                <div class="col-md-1 d-flex align-items-end">
                    <div class="btn-group w-100" role="group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i>
                        </button>
                        <a href="{{ route('issued-items.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Issued Items Table -->
    <div class="bg-white shadow rounded overflow-x-auto">
        <div class="px-4 py-3 border-b bg-gray-50 flex items-center justify-between">
            <div class="flex items-baseline gap-3">
                <h2 class="text-lg font-semibold">Issued Items</h2>
                <div class="text-sm text-gray-600">
                    @if($issuedItems->count())
                        Showing {{ $issuedItems->firstItem() }}–{{ $issuedItems->lastItem() }} of {{ $issuedItems->total() }} · Page {{ $issuedItems->currentPage() }} of {{ $issuedItems->lastPage() }}
                    @endif
                </div>
            </div>
            <div>
                @if($issuedItems->hasPages())
                    {{ $issuedItems->withQueryString()->links() }}
                @endif
            </div>
        </div>
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Issue Date</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Issue ID</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Variant</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Recipient</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Issued By</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($issuedItems as $item)
                    <tr class="cursor-pointer hover:bg-blue-50" onclick="window.location='{{ route('issued-items.show', $item) }}'">
                        <td class="px-4 py-3 text-sm">
                            <div class="text-blue-600 font-semibold">{{ $item->formatted_issued_on }}</div>
                            <div class="text-gray-500 text-xs">{{ $item->issued_on->diffForHumans() }}</div>
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <a href="{{ route('issued-items.show', $item) }}" class="text-blue-600 hover:underline">Issue #{{ $item->issue_id }}</a>
                            @if($item->supply)
                                <div class="text-gray-600 text-xs">{{ $item->supply->name }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm">
                            @if($item->supplyVariant)
                                <span class="inline-block px-2 py-1 rounded text-xs bg-blue-100 text-blue-800">{{ $item->supplyVariant->variant_name }}</span>
                            @else
                                <span class="text-gray-500">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm">
                            @if($item->department)
                                <span class="inline-block px-2 py-1 rounded text-xs bg-indigo-100 text-indigo-800">{{ $item->department->department_name }}</span>
                            @else
                                <span class="text-gray-500">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm">
                            @if($item->user)
                                <span class="text-gray-800">{{ $item->user->name }}</span>
                                @if($item->user->email)
                                    <div class="text-gray-500 text-xs">{{ $item->user->email }}</div>
                                @endif
                            @else
                                <span class="text-gray-500">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm">
                            @php $type = $item->supply->supply_type ?? 'unknown'; @endphp
                            <span class="inline-block px-2 py-1 rounded text-xs {{ $type === 'consumable' ? 'bg-yellow-100 text-yellow-800' : ($type === 'grantable' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800') }}">{{ ucfirst($type) }}</span>
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <span class="text-gray-800">{{ $item->issuedBy->name ?? 'N/A' }}</span>
                            <div class="text-gray-500 text-xs">{{ $item->created_at->format('M d, Y') }}</div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        @if(!$issuedItems->count())
            <div class="px-4 py-6 text-center text-sm text-gray-500">No issued items found.
                @if(request()->hasAny(['search', 'department_id', 'supply_type', 'date_from', 'date_to', 'issued_by']))
                    Try adjusting your search criteria or 
                    <a href="{{ route('issued-items.index') }}" class="text-blue-600 hover:underline">clear all filters</a>.
                @else
                    Start by <a href="{{ route('issued-items.create') }}" class="text-blue-600 hover:underline">issuing your first item</a>.
                @endif
            </div>
        @endif
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">
                    <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                    Confirm Deletion
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this issued item record?</p>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Note:</strong> This action will return the issued quantity back to the supply stock.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-1"></i>
                        Delete Item
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function confirmDelete(itemId) {
    const deleteForm = document.getElementById('deleteForm');
    deleteForm.action = `/issued-items/${itemId}`;
    
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    deleteModal.show();
}

// Auto-submit form on filter change (optional)
document.addEventListener('DOMContentLoaded', function() {
    const filterSelects = document.querySelectorAll('#department_id, #supply_type, #issued_by');
    filterSelects.forEach(select => {
        select.addEventListener('change', function() {
            // Uncomment the line below to auto-submit on filter change
            // this.form.submit();
        });
    });
});
</script>
@endpush
@endsection