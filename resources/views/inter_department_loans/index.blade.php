@extends('layouts.app')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/inter-department-loans.css') }}">
@endsection

@push('scripts')
<script>
    // Initialize Bootstrap tooltips
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
@endpush

@section('content')
<div class="container-fluid px-4 py-6">
    <!-- Flash Messages -->
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-1 text-dark fw-bold">
                <i class="fas fa-exchange-alt text-primary me-2"></i>
                Inter-Department Loans
            </h1>
            <p class="text-muted mb-0">Manage cross-department equipment loans efficiently</p>
        </div>
        <a href="{{ route('loan-requests.inter-department.create') }}" class="btn btn-primary btn-lg shadow-sm">
            <i class="fas fa-plus me-2"></i>New Borrow Request
        </a>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary bg-gradient rounded-3 p-3">
                                <i class="fas fa-list-alt text-white fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="text-muted small">Total Requests</div>
                            <div class="h4 mb-0 fw-bold">{{ $totalRequests }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-warning bg-gradient rounded-3 p-3">
                                <i class="fas fa-clock text-white fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="text-muted small">Pending</div>
                            <div class="h4 mb-0 fw-bold">{{ $pendingRequests }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success bg-gradient rounded-3 p-3">
                                <i class="fas fa-check-circle text-white fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="text-muted small">Approved</div>
                            <div class="h4 mb-0 fw-bold">{{ $approvedRequests }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-danger bg-gradient rounded-3 p-3">
                                <i class="fas fa-times-circle text-white fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="text-muted small">Declined</div>
                            <div class="h4 mb-0 fw-bold">{{ $declinedRequests }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filter Section -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-light border-0">
            <h5 class="mb-0">
                <i class="fas fa-search me-2"></i>Search & Filter
            </h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('loan-requests.inter-department.index') }}">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="search" class="form-label">Supply Name</label>
                        <input type="text" 
                               class="form-control" 
                               id="search"
                               name="search" 
                               value="{{ request('search') }}" 
                               placeholder="Search by supply name...">
                    </div>
                    <div class="col-md-3">
                        <label for="department" class="form-label">Department</label>
                        <select class="form-select" id="department" name="department">
                            <option value="">All Departments</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}" 
                                        {{ request('department') == $department->id ? 'selected' : '' }}
                                        data-bs-toggle="tooltip" 
                                        title="Filter by {{ $department->department_name }}">
                                    {{ $department->department_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">All Status</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="dean_approved" {{ request('status') == 'dean_approved' ? 'selected' : '' }}>Dean Approved</option>
                            <option value="lending_dean_approved" {{ request('status') == 'lending_dean_approved' ? 'selected' : '' }}>Lending Dean Approved</option>
                            <option value="borrowed" {{ request('status') == 'borrowed' ? 'selected' : '' }}>Borrowed</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="declined" {{ request('status') == 'declined' ? 'selected' : '' }}>Declined</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-1"></i>Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Loan Requests Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-table me-2"></i>Borrow Requests
                </h5>
                <span class="badge bg-primary">{{ $loanRequests->total() }} Total</span>
            </div>
        </div>
        <div class="card-body p-0">
            @if($loanRequests->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="border-0 fw-semibold">Request ID</th>
                                <th class="border-0 fw-semibold">From → To</th>
                                <th class="border-0 fw-semibold">Requested By</th>
                                <th class="border-0 fw-semibold">Expected Return</th>
                                <th class="border-0 fw-semibold">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                // Prepare page items collection and track shown batch keys to avoid duplicates
                                $pageItems = collect($loanRequests instanceof \Illuminate\Pagination\LengthAwarePaginator ? $loanRequests->items() : $loanRequests);
                                $shownBatchKeys = [];
                            @endphp
                            @foreach($loanRequests as $request)
                                @php
                                    // Derive a batch key based on common header fields set during multi-item submission
                                    $plannedDate = optional($request->planned_start_date)->format('Ymd');
                                    $returnDate = optional($request->expected_return_date)->format('Ymd');
                                    $createdStamp = optional($request->created_at)->format('YmdHis');
                                    $batchKey = implode('|', [
                                        $request->requested_by,
                                        $request->borrowing_department_id,
                                        $plannedDate,
                                        $returnDate,
                                        $createdStamp,
                                        $request->purpose ?? '',
                                        $request->notes ?? '',
                                    ]);
                                @endphp
                                @continue(in_array($batchKey, $shownBatchKeys))
                                @php
                                    $shownBatchKeys[] = $batchKey;
                                    // Compute a batch identifier from items on the current page sharing the same key
                                    $batchId = $pageItems->filter(function($r) use ($request, $plannedDate, $returnDate, $createdStamp) {
                                        return $r->requested_by === $request->requested_by
                                            && $r->borrowing_department_id === $request->borrowing_department_id
                                            && optional($r->planned_start_date)->format('Ymd') === $plannedDate
                                            && optional($r->expected_return_date)->format('Ymd') === $returnDate
                                            && optional($r->created_at)->format('YmdHis') === $createdStamp
                                            && ($r->purpose ?? '') === ($request->purpose ?? '')
                                            && ($r->notes ?? '') === ($request->notes ?? '');
                                    })->min('id') ?? $request->id;
                                @endphp
                                <tr>
                                    <td class="align-middle">
                                        <a href="{{ route('loan-requests.inter-department.show', $batchId) }}" 
                                           class="fw-semibold text-decoration-none">
                                            Batch #{{ $batchId }}
                                        </a>
                                    </td>
                                    <td class="align-middle">
                                        <div class="d-flex align-items-center">
                                            <a href="{{ route('departments.show', $request->lendingDepartment) }}" class="badge bg-light text-dark text-decoration-none">
                                                {{ $request->lendingDepartment->department_name }}
                                            </a>
                                            <i class="fas fa-arrow-right mx-2 text-muted"></i>
                                            <a href="{{ route('departments.show', $request->borrowingDepartment) }}" class="badge bg-light text-dark text-decoration-none">
                                                {{ $request->borrowingDepartment->department_name }}
                                            </a>
                                        </div>
                                    </td>
                                    <td class="align-middle">
                                        <div>
                                            @can('view', $request->requestedBy)
                                                @php $profileUrl = route('users.profile', $request->requestedBy); @endphp
                                                <a href="{{ $profileUrl }}" class="fw-semibold text-decoration-none">
                                                    {{ $request->requestedBy->name }}
                                                </a>
                                            @else
                                                <span class="fw-semibold">{{ $request->requestedBy->name }}</span>
                                            @endcan
                                        </div>
                                    </td>
                                    <td class="align-middle">
                                        <div class="text-muted">
                                            {{ \Carbon\Carbon::parse($request->expected_return_date)->format('M d, Y') }}
                                        </div>
                                    </td>
                                    <td class="align-middle">
                                        @switch($request->status)
                                            @case('pending')
                                                <span class="badge bg-warning">
                                                    <i class="fas fa-clock me-1"></i>Pending
                                                </span>
                                                @break
                                            @case('dean_approved')
                                                <span class="badge bg-info">
                                                    <i class="fas fa-user-check me-1"></i>Dean Approved
                                                </span>
                                                @break
                                            @case('lending_dean_approved')
                                                <span class="badge bg-primary">
                                                    <i class="fas fa-handshake me-1"></i>Lending Dean Approved
                                                </span>
                                                @break
                                            @case('borrowed')
                                                <span class="badge bg-primary">
                                                    <i class="fas fa-box-open me-1"></i>Borrowed
                                                </span>
                                                @break
                                            @case('completed')
                                                <span class="badge bg-success">
                                                    <i class="fas fa-check-double me-1"></i>Completed
                                                </span>
                                                @break
                                            @case('declined')
                                                <span class="badge bg-danger">
                                                    <i class="fas fa-times-circle me-1"></i>Declined
                                                </span>
                                                @break
                                            @default
                                                <span class="badge bg-secondary">{{ ucfirst($request->status) }}</span>
                                        @endswitch
                                    </td>
                                    
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="card-footer bg-white border-0 py-3">
                    <div class="d-flex justify-content-center">
                        {{ $loanRequests->links() }}
                    </div>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No Borrow Requests Found</h5>
                    <p class="text-muted mb-4">
                        @if(request('search') || request('status'))
                            No requests match your current filters.
                        @else
                            There are no inter-department borrow requests yet.
                        @endif
                    </p>
                    <a href="{{ route('loan-requests.inter-department.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Create First Request
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection