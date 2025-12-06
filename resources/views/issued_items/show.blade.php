@extends('layouts.app')

@section('title', 'Issued Item Details')

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-eye text-primary me-2"></i>
                Issued Item Details
            </h1>
            <p class="text-muted mb-0">View detailed information about this issued item</p>
        </div>
        <div class="col-md-4 text-end">
            <div class="btn-group" role="group">
                <a href="{{ route('issued-items.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>
                    Back to List
                </a>
                @can('update', $issuedItem)
                    <a href="{{ route('issued-items.edit', $issuedItem) }}" class="btn btn-outline-primary">
                        <i class="fas fa-edit me-1"></i>
                        Edit
                    </a>
                @endcan
                @can('delete', $issuedItem)
                    <button type="button" class="btn btn-outline-danger" onclick="confirmDelete({{ $issuedItem->id }})">
                        <i class="fas fa-trash me-1"></i>
                        Delete
                    </button>
                @endcan
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Main Information -->
        <div class="col-lg-8">
            <!-- Basic Information Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info-circle me-1"></i>
                        Basic Information
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">
                                <i class="fas fa-calendar text-primary me-1"></i>
                                Issue Date
                            </label>
                            <div class="fw-bold">{{ $issuedItem->formattedIssuedOn }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">
                                <i class="fas fa-clock text-primary me-1"></i>
                                Created At
                            </label>
                            <div class="fw-bold">{{ $issuedItem->created_at->format('M d, Y h:i A') }}</div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">
                                <i class="fas fa-user text-primary me-1"></i>
                                Issued By
                            </label>
                            <div class="fw-bold">
                                {{ $issuedItem->issuedBy->name ?? 'System' }}
                                @if($issuedItem->issuedBy)
                                    <br><small class="text-muted">{{ $issuedItem->issuedBy->email }}</small>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">
                                <i class="fas fa-tag text-primary me-1"></i>
                                Status
                            </label>
                            <div>
                                <span class="badge {{ $issuedItem->getStatusBadgeClass() }}">
                                    {{ $issuedItem->getStatusText() }}
                                </span>
                            </div>
                        </div>
                    </div>

                    @if($issuedItem->notes)
                        <div class="row">
                            <div class="col-12">
                                <label class="form-label text-muted">
                                    <i class="fas fa-sticky-note text-primary me-1"></i>
                                    Notes
                                </label>
                                <div class="p-3 bg-light rounded">
                                    {{ $issuedItem->notes }}
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($issuedItem->batch && $issuedItem->batch->items->count() > 0)
                        <hr>
                        <div class="row">
                            <div class="col-12">
                                <h6 class="text-secondary mb-2">
                                    <i class="fas fa-list me-1"></i>
                                    Items in this Transaction
                                </h6>
                                <div class="table-responsive">
                                    <table class="table table-sm align-middle">
                                        <thead>
                                            <tr>
                                                <th>Supply</th>
                                                <th>Variant</th>
                                                <th class="text-end">Quantity</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($issuedItem->batch->items as $item)
                                                <tr>
                                                    <td>{{ $item->supply->name }}</td>
                                                    <td>{{ optional($item->supplyVariant)->name ?? '—' }}</td>
                                                    <td class="text-end">{{ number_format($item->quantity) }} {{ $item->supplyVariant->unit ?? $item->supply->unit }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Supply Information Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">
                        <i class="fas fa-box me-1"></i>
                        Supply Information
                    </h6>
                </div>
                <div class="card-body">
                    @if($issuedItem->batch && $issuedItem->batch->items->count() > 0)
                        @foreach($issuedItem->batch->items as $item)
                            <div class="row mb-3">
                                <div class="col-md-8">
                                    <h5 class="text-primary mb-2">{{ $item->supply->name }}</h5>
                                    @if($item->supply->description)
                                        <p class="text-muted mb-3">{{ $item->supply->description }}</p>
                                    @endif
                                    <div class="row">
                                        <div class="col-sm-6 mb-2">
                                            <strong>Type:</strong>
                                            <span class="badge bg-{{ $item->supply->supply_type === 'consumable' ? 'warning' : 'success' }} ms-1">
                                                {{ ucfirst($item->supply->supply_type) }}
                                            </span>
                                        </div>
                                        <div class="col-sm-6 mb-2">
                                            <strong>Category:</strong>
                                            <span class="text-muted">{{ $item->supply->category ?? 'Uncategorized' }}</span>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-6 mb-2">
                                            <strong>Current Stock:</strong>
                                            <span class="fw-bold">{{ number_format($item->supply->current_stock) }} {{ $item->supply->unit }}</span>
                                        </div>
                                        <div class="col-sm-6 mb-2">
                                            <strong>Unit:</strong>
                                            <span class="text-muted">{{ $item->supply->unit }}</span>
                                        </div>
                                    </div>
                                    @if($item->supplyVariant)
                                        <div class="row">
                                            <div class="col-md-3">
                                                <strong>Variant:</strong><br>
                                                <span class="text-primary">{{ $item->supplyVariant->name }}</span>
                                            </div>
                                            <div class="col-md-3">
                                                <strong>Variant Stock:</strong><br>
                                                <span class="fw-bold">{{ number_format($item->supplyVariant->current_stock) }} {{ $item->supplyVariant->unit }}</span>
                                            </div>
                                            <div class="col-md-3">
                                                <strong>Variant Unit:</strong><br>
                                                <span class="text-muted">{{ $item->supplyVariant->unit }}</span>
                                            </div>
                                            <div class="col-md-3 text-end">
                                                <strong>Issued Quantity:</strong><br>
                                                <span class="fw-bold">{{ number_format($item->quantity) }} {{ $item->supplyVariant->unit }}</span>
                                            </div>
                                        </div>
                                    @else
                                        <div class="row">
                                            <div class="col-md-3 text-end">
                                                <strong>Issued Quantity:</strong><br>
                                                <span class="fw-bold">{{ number_format($item->quantity) }} {{ $item->supply->unit }}</span>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                <div class="col-md-4 text-center">
                                    <div class="p-3 bg-light rounded">
                                        <i class="fas fa-box fa-3x text-primary mb-2"></i>
                                        <div class="fw-bold">Supply Item</div>
                                        <small class="text-muted">{{ $item->supply->supply_type }}</small>
                                    </div>
                                </div>
                            </div>
                            @if(!$loop->last)
                                <hr>
                            @endif
                        @endforeach
                    @else
                        <div class="row">
                            <div class="col-md-8">
                                <h5 class="text-primary mb-2">{{ $issuedItem->supply->name }}</h5>
                                @if($issuedItem->supply->description)
                                    <p class="text-muted mb-3">{{ $issuedItem->supply->description }}</p>
                                @endif
                                <div class="row">
                                    <div class="col-sm-6 mb-2">
                                        <strong>Type:</strong>
                                        <span class="badge bg-{{ $issuedItem->supply->supply_type === 'consumable' ? 'warning' : 'success' }} ms-1">
                                            {{ ucfirst($issuedItem->supply->supply_type) }}
                                        </span>
                                    </div>
                                    <div class="col-sm-6 mb-2">
                                        <strong>Category:</strong>
                                        <span class="text-muted">{{ $issuedItem->supply->category ?? 'Uncategorized' }}</span>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-6 mb-2">
                                        <strong>Current Stock:</strong>
                                        <span class="fw-bold">{{ number_format($issuedItem->supply->current_stock) }} {{ $issuedItem->supply->unit }}</span>
                                    </div>
                                    <div class="col-sm-6 mb-2">
                                        <strong>Unit:</strong>
                                        <span class="text-muted">{{ $issuedItem->supply->unit }}</span>
                                    </div>
                                </div>
                                @if($issuedItem->supplyVariant)
                                    <div class="row">
                                        <div class="col-md-4">
                                            <strong>Variant:</strong><br>
                                            <span class="text-primary">{{ $issuedItem->supplyVariant->name }}</span>
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Variant Stock:</strong><br>
                                            <span class="fw-bold">{{ number_format($issuedItem->supplyVariant->current_stock) }} {{ $issuedItem->supplyVariant->unit }}</span>
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Variant Unit:</strong><br>
                                            <span class="text-muted">{{ $issuedItem->supplyVariant->unit }}</span>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <div class="col-md-4 text-center">
                                <div class="p-3 bg-light rounded">
                                    <i class="fas fa-box fa-3x text-primary mb-2"></i>
                                    <div class="fw-bold">Supply Item</div>
                                    <small class="text-muted">{{ $issuedItem->supply->supply_type }}</small>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Department & Recipient Information Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">
                        <i class="fas fa-building me-1"></i>
                        Department & Recipient Information
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary mb-2">Department</h6>
                            <div class="p-3 bg-light rounded">
                                <h5 class="mb-1">
                                    <a href="{{ route('departments.show', $issuedItem->department) }}" class="text-decoration-none">
                                        {{ $issuedItem->department->department_name }}
                                    </a>
                                </h5>
                                @if($issuedItem->department->description)
                                    <p class="text-muted mb-0">{{ $issuedItem->department->description }}</p>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-primary mb-2">Recipient</h6>
                            <div class="p-3 bg-light rounded">
                                @if($issuedItem->user)
                                    <h5 class="mb-1">
                                        <a href="{{ route('users.show', $issuedItem->user) }}" class="text-decoration-none">
                                            {{ $issuedItem->user->name }}
                                        </a>
                                    </h5>
                                    <p class="text-muted mb-0">{{ $issuedItem->user->email }}</p>
                                @else
                                    <p class="text-muted mb-0">No specific recipient assigned</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar Information -->
        <div class="col-lg-4">
            <!-- Quantity & Value Summary -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-warning">
                        <i class="fas fa-calculator me-1"></i>
                        Quantity & Value Summary
                    </h6>
                </div>
                <div class="card-body text-center">
                    <div class="row">
                        <div class="col-12 mb-3">
                            <div class="border-bottom pb-2">
                                <h3 class="text-primary mb-0">{{ $issuedItem->formattedQuantity }}</h3>
                                <small class="text-muted">Quantity Issued</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-6">
                            <div class="border-end">
                                <h5 class="text-success mb-0">₱{{ number_format($issuedItem->totalValue, 2) }}</h5>
                                <small class="text-muted">Total Value</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <h5 class="text-info mb-0">
                                ₱{{ number_format($issuedItem->supplyVariant ? $issuedItem->supplyVariant->price : $issuedItem->supply->price, 2) }}
                            </h5>
                            <small class="text-muted">Unit Price</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-secondary">
                        <i class="fas fa-tools me-1"></i>
                        Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @can('update', $issuedItem)
                            <a href="{{ route('issued-items.edit', $issuedItem) }}" class="btn btn-outline-primary">
                                <i class="fas fa-edit me-1"></i>
                                Edit This Item
                            </a>
                        @endcan
                        
                        <a href="{{ route('issued-items.create') }}" class="btn btn-outline-success">
                            <i class="fas fa-plus me-1"></i>
                            Issue New Item
                        </a>
                        
                        <a href="{{ route('supplies.show', $issuedItem->supply) }}" class="btn btn-outline-info">
                            <i class="fas fa-box me-1"></i>
                            View Supply Details
                        </a>
                        
                        @can('delete', $issuedItem)
                            <button type="button" class="btn btn-outline-danger" onclick="confirmDelete({{ $issuedItem->id }})">
                                <i class="fas fa-trash me-1"></i>
                                Delete Item
                            </button>
                        @endcan
                    </div>
                </div>
            </div>

            <!-- Related Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-dark">
                        <i class="fas fa-link me-1"></i>
                        Related Information
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6 class="text-muted mb-2">Supply Information</h6>
                        <ul class="list-unstyled mb-0">
                            <li class="mb-1">
                                <i class="fas fa-arrow-right text-muted me-2"></i>
                                <strong>SKU:</strong> {{ $issuedItem->supply->sku ?? 'N/A' }}
                            </li>
                            <li class="mb-1">
                                <i class="fas fa-arrow-right text-muted me-2"></i>
                                <strong>Minimum Stock:</strong> {{ $issuedItem->supply->minimum_stock_level ?? 'Not set' }}
                            </li>
                            <li class="mb-1">
                                <i class="fas fa-arrow-right text-muted me-2"></i>
                                <strong>Status:</strong> 
                                <span class="badge bg-{{ $issuedItem->supply->status === 'active' ? 'success' : 'secondary' }}">
                                    {{ ucfirst($issuedItem->supply->status) }}
                                </span>
                            </li>
                        </ul>
                    </div>

                    <div class="mb-3">
                        <h6 class="text-muted mb-2">Department Information</h6>
                        <ul class="list-unstyled mb-0">
                            <li class="mb-1">
                                <i class="fas fa-arrow-right text-muted me-2"></i>
                                <strong>Department:</strong> {{ $issuedItem->department->department_name }}
                            </li>
                            @if($issuedItem->department->head_name)
                                <li class="mb-1">
                                    <i class="fas fa-arrow-right text-muted me-2"></i>
                                    <strong>Head:</strong> {{ $issuedItem->department->head_name }}
                                </li>
                            @endif
                        </ul>
                    </div>

                    @if($issuedItem->canBeDeleted())
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-1"></i>
                            <small>This item can be deleted and its quantity will be returned to stock.</small>
                        </div>
                    @else
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            <small>This item cannot be deleted due to system constraints.</small>
                        </div>
                    @endif
                </div>
            </div>
        </div>
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
                <div class="alert alert-warning">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Important:</strong> Deleting this issued item will return the issued quantity back to the supply stock.
                </div>
                <p>Are you sure you want to delete this issued item?</p>
                <div class="bg-light p-3 rounded">
                    <strong>Item Details:</strong><br>
                    <i class="fas fa-box me-1"></i> {{ $issuedItem->supply->name }}<br>
                    <i class="fas fa-calculator me-1"></i> Quantity: {{ $issuedItem->formattedQuantity }}<br>
                    <i class="fas fa-building me-1"></i> Department: {{ $issuedItem->department->department_name }}<br>
                    <i class="fas fa-calendar me-1"></i> Issued: {{ $issuedItem->formattedIssuedOn }}
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>
                    Cancel
                </button>
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
</script>
@endpush
@endsection
