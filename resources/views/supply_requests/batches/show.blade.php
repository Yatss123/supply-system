@extends('layouts.app')

@section('content')
@php($pageTitle = 'Supply Request Batch Details')
<div class="container-fluid px-4 py-4">
    <!-- Flash Messages -->
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 text-dark fw-bold">
                <i class="fas fa-clipboard-list text-primary me-2"></i>
                {{ $pageTitle }}
            </h1>
            <p class="text-muted mb-0">Batch #{{ $batch->id }} · {{ $batch->created_at->format('M d, Y g:i A') }}</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge {{ $batch->status === 'approved' ? 'bg-success' : ($batch->status === 'rejected' ? 'bg-danger' : ($batch->status === 'partial' ? 'bg-warning' : 'bg-secondary')) }}">{{ ucfirst($batch->status) }}</span>
            <a href="{{ route('supply-request-batches.index') }}" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
            @if($batch->department_id)
            <a href="{{ route('department-carts.show', $batch->department_id) }}" class="btn btn-outline-secondary">
                <i class="fas fa-shopping-cart me-1"></i> View Department Cart
            </a>
            @endif
        </div>
    </div>

    <!-- Summary + Actions -->
    <div class="row g-4 mb-1">
        <!-- Summary -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-light">
                    <h6 class="mb-0 fw-semibold">Batch Summary</h6>
                </div>
                <div class="card-body">
                    <div class="row mb-2">
                        <div class="col-4 text-muted"><i class="fas fa-user me-2"></i>Requester</div>
                        <div class="col-8">{{ $batch->user->name ?? '—' }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4 text-muted"><i class="fas fa-building me-2"></i>Department</div>
                        <div class="col-8">{{ $batch->department->department_name ?? '—' }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4 text-muted"><i class="fas fa-clipboard me-2"></i>Description</div>
                        <div class="col-8">{{ $batch->description ?? '—' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Center (approve actions hidden) -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-light">
                    <h6 class="mb-0 fw-semibold">Action Center</h6>
                </div>
                <div class="card-body">
                    <!-- Approve actions intentionally hidden on batch details page -->
                </div>
            </div>
        </div>
    </div>

    <!-- Items Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-light">
            <h6 class="mb-0 fw-semibold">Batch Items</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm table-striped align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <!-- Selection checkbox column hidden -->
                            <th scope="col" class="text-muted">Item</th>
                            <th scope="col" class="text-muted">Quantity</th>
                            <th scope="col" class="text-muted">Type</th>
                            <th scope="col" class="text-muted">Status</th>
                            <th scope="col" class="text-muted">Reason</th>
                            <th scope="col" class="text-muted">Note</th>
                            <!-- Review note for approval hidden -->
                            <th scope="col" class="text-muted">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($batch->items as $item)
                            <tr>
                                <!-- Per-item selection checkbox hidden -->
                                <td>{{ $item->item_name ?? ($item->supply->name ?? '—') }}</td>
                                <td>{{ number_format($item->quantity) }} {{ $item->unit }}</td>
                                <td>{{ $item->supply_id ? 'Existing' : 'New Item' }}</td>
                                <td>
                                    <span class="badge {{ $item->status === 'approved' ? 'bg-success' : (($item->status === 'rejected' || $item->status === 'declined') ? 'bg-danger' : 'bg-secondary') }}">{{ ucfirst($item->status) }}</span>
                                    @php($cartItem = \App\Models\DepartmentCartItem::where('supply_request_id', $item->id)->first())
                                    @if($cartItem)
                                        <span class="badge bg-success ms-2"><i class="fas fa-shopping-cart me-1"></i> Added to Cart</span>
                                    @endif
                                </td>
                                <td>{{ $item->rejection_reason ?? '—' }}</td>
                                <td>{{ $item->admin_note ?? '—' }}</td>
                                <!-- Review note input hidden -->
                                <td>
                                    <a href="{{ route('supply-requests.show', $item) }}" class="btn btn-outline-secondary btn-sm">
                                        <i class="fas fa-eye me-1"></i> View
                                    </a>
                                    @if(isset($cartItem) && $cartItem && $batch->department_id)
                                        <a href="{{ route('department-carts.show', $batch->department_id) }}" class="btn btn-outline-primary btn-sm ms-2">
                                            <i class="fas fa-shopping-cart me-1"></i> View Cart
                                        </a>
                                    @endif
                                    @if(auth()->user()->hasAdminPrivileges() && $item->status === 'pending')
                                        <form method="POST" action="{{ route('supply-requests.decline', $item) }}" class="d-inline-flex align-items-center gap-2 ms-2">
                                            @csrf
                                            @method('PATCH')
                                            <input type="text" name="rejection_reason" placeholder="Reason" class="form-control form-control-sm" required>
                                            <input type="text" name="admin_note" placeholder="Note (optional)" class="form-control form-control-sm">
                                            <button type="submit" class="btn btn-danger btn-sm">
                                                <i class="fas fa-times me-1"></i> Decline
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger mt-3" role="alert">{{ $errors->first() }}</div>
    @endif
</div>

<script>
// Approve actions are hidden; selection and approval scripting removed.
</script>
@endsection