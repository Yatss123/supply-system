@extends('layouts.app')

@section('content')
<div class="container-fluid px-2">
    <!-- Mobile-optimized header -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <button type="button" class="btn btn-link p-0 me-3" onclick="window.history.back()">
                            <i class="fas fa-arrow-left fs-4"></i>
                        </button>
                        <div class="flex-grow-1">
                            <h5 class="mb-1 fw-bold">Manage Borrow Requests</h5>
                            <p class="mb-0 text-muted small">{{ $supply->name }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Supply Information -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-3">
                    <div class="row g-2">
                        <div class="col-4">
                            <div class="text-center p-2 bg-light rounded">
                                <div class="fw-bold text-primary fs-5">{{ $supply->quantity }}</div>
                                <small class="text-muted">Available</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="text-center p-2 bg-light rounded">
                                <div class="fw-bold text-warning fs-5">{{ $pendingLoanRequests->count() + $pendingInterDeptRequests->count() }}</div>
                                <small class="text-muted">Pending</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="text-center p-2 bg-light rounded">
                                <span class="badge bg-{{ $supply->status === 'active' ? 'success' : ($supply->status === 'inactive' ? 'secondary' : 'danger') }}">
                                    {{ ucfirst($supply->status) }}
                                </span>
                                <div><small class="text-muted">Status</small></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Regular Loan Requests -->
    @if($pendingLoanRequests->count() > 0)
    <div class="row mb-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white p-3">
                    <h6 class="mb-0 fw-bold">
                        <i class="fas fa-handshake me-2"></i>Regular Loan Requests ({{ $pendingLoanRequests->count() }})
                    </h6>
                </div>
                <div class="card-body p-0">
                    @foreach($pendingLoanRequests as $request)
                    <div class="border-bottom p-3">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="flex-grow-1">
                                <div class="fw-bold">{{ $request->requestedBy->name }}</div>
                                <small class="text-muted">{{ $request->department->name }}</small>
                            </div>
                            <span class="badge bg-warning">{{ ucfirst($request->status) }}</span>
                        </div>
                        
                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <small class="text-muted">Quantity:</small>
                                <div class="fw-bold">{{ $request->quantity_requested }}</div>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">Return Date:</small>
                                <div class="fw-bold">{{ \Carbon\Carbon::parse($request->expected_return_date)->format('M d, Y') }}</div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <small class="text-muted">Purpose:</small>
                            <div>{{ $request->purpose }}</div>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <form action="{{ route('loan-requests.approve', $request) }}" method="POST" class="flex-grow-1">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-success btn-sm w-100">
                                    <i class="fas fa-check me-1"></i>Approve
                                </button>
                            </form>
                            <form action="{{ route('loan-requests.reject', $request) }}" method="POST" class="flex-grow-1">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-danger btn-sm w-100">
                                    <i class="fas fa-times me-1"></i>Reject
                                </button>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Inter-Department Loan Requests -->
    @if($pendingInterDeptRequests->count() > 0)
    <div class="row mb-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-info text-white p-3">
                    <h6 class="mb-0 fw-bold">
                        <i class="fas fa-exchange-alt me-2"></i>Inter-Department Borrow Requests ({{ $pendingInterDeptRequests->count() }})
                    </h6>
                </div>
                <div class="card-body p-0">
                    @foreach($pendingInterDeptRequests as $request)
                    <div class="border-bottom p-3">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="flex-grow-1">
                                <div class="fw-bold">{{ $request->requestedBy->name }}</div>
                                <small class="text-muted">
                                    {{ $request->borrowingDepartment->name }} 
                                    <i class="fas fa-arrow-left mx-1"></i> 
                                    {{ $request->lendingDepartment->name }}
                                </small>
                            </div>
                            <span class="badge bg-{{ $request->status === 'pending' ? 'warning' : ($request->status === 'lending_approved' ? 'info' : 'primary') }}">
                                {{ ucfirst(str_replace('_', ' ', $request->status)) }}
                            </span>
                        </div>
                        
                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <small class="text-muted">Quantity:</small>
                                <div class="fw-bold">{{ $request->quantity_requested }}</div>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">Return Date:</small>
                                <div class="fw-bold">{{ \Carbon\Carbon::parse($request->expected_return_date)->format('M d, Y') }}</div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <small class="text-muted">Purpose:</small>
                            <div>{{ $request->purpose }}</div>
                        </div>
                        
                        @if($request->status === 'pending')
                        <div class="d-flex gap-2">
                            <form action="{{ route('inter-department-loans.lending-approve', $request) }}" method="POST" class="flex-grow-1">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-success btn-sm w-100">
                                    <i class="fas fa-check me-1"></i>Approve (Lending)
                                </button>
                            </form>
                            <form action="{{ route('inter-department-loans.reject', $request) }}" method="POST" class="flex-grow-1">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-danger btn-sm w-100">
                                    <i class="fas fa-times me-1"></i>Reject
                                </button>
                            </form>
                        </div>
                        @elseif($request->status === 'lending_approved')
                        <div class="alert alert-info mb-0">
                            <small><i class="fas fa-info-circle me-1"></i>Waiting for borrowing department confirmation</small>
                        </div>
                        @elseif($request->status === 'borrowing_confirmed')
                        <div class="d-flex gap-2">
                            <form action="{{ route('inter-department-loans.admin-approve', $request) }}" method="POST" class="flex-grow-1">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-primary btn-sm w-100">
                                    <i class="fas fa-check-double me-1"></i>Final Approval
                                </button>
                            </form>
                            <form action="{{ route('inter-department-loans.reject', $request) }}" method="POST" class="flex-grow-1">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-danger btn-sm w-100">
                                    <i class="fas fa-times me-1"></i>Reject
                                </button>
                            </form>
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- No Pending Requests -->
    @if($pendingLoanRequests->count() === 0 && $pendingInterDeptRequests->count() === 0)
    <div class="row mb-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center p-5">
                    <i class="fas fa-inbox fs-1 text-muted mb-3"></i>
                    <h6 class="text-muted">No Pending Requests</h6>
                    <p class="text-muted mb-0">There are currently no pending borrow requests for this supply.</p>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Create New Borrow Request Button -->
    <div class="row mb-4">
        <div class="col-12">
            <a href="{{ route('qr.borrow', $supply) }}?admin_create=1" class="btn btn-primary btn-lg w-100">
                <i class="fas fa-plus me-2"></i>Create New Borrow Request
            </a>
        </div>
    </div>
</div>

<style>
/* Mobile-first responsive design */
@media (max-width: 768px) {
    .container-fluid {
        padding-left: 8px !important;
        padding-right: 8px !important;
    }
    
    .card {
        border-radius: 12px !important;
    }
    
    .btn-lg {
        padding: 12px 20px;
        font-size: 1.1rem;
    }
    
    .btn-sm {
        padding: 8px 12px;
        font-size: 0.875rem;
    }
    
    /* Touch-friendly buttons */
    .btn {
        min-height: 44px;
    }
    
    /* Better spacing for mobile */
    .mb-3 {
        margin-bottom: 1rem !important;
    }
}

/* Badge improvements */
.badge {
    font-size: 0.75rem;
    padding: 0.375rem 0.75rem;
}

/* Alert improvements */
.alert {
    border-radius: 8px;
    border: none;
}

/* Form improvements */
form {
    margin: 0;
}

/* Haptic feedback simulation */
.btn:active {
    transform: scale(0.98);
    transition: transform 0.1s;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add confirmation for reject actions
    const rejectForms = document.querySelectorAll('form[action*="reject"]');
    rejectForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!confirm('Are you sure you want to reject this request?')) {
                e.preventDefault();
            }
        });
    });

    // Add confirmation for approval actions
    const approveForms = document.querySelectorAll('form[action*="approve"]');
    approveForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!confirm('Are you sure you want to approve this request?')) {
                e.preventDefault();
            }
        });
    });

    // Haptic feedback simulation
    function triggerHapticFeedback() {
        if ('vibrate' in navigator) {
            navigator.vibrate(50);
        }
    }

    // Add haptic feedback to all buttons
    const buttons = document.querySelectorAll('.btn');
    buttons.forEach(button => {
        button.addEventListener('click', triggerHapticFeedback);
    });
});
</script>
@endsection