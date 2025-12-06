@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center bg-primary text-white">
                    <h3 class="mb-0"><i class="fas fa-boxes"></i> Order Requests</h3>
                    @if(auth()->check() && auth()->user()->hasAdminPrivileges())
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-light text-dark">
                                <i class="fas fa-info-circle me-1"></i>
                                Managed via Department Carts
                            </span>
                            <a href="{{ route('departments.index') }}" class="btn btn-light">
                                <i class="fas fa-building"></i> Go to Departments
                            </a>
                        </div>
                    @endif
                </div>
                <div class="card-body">
                    <div class="alert alert-warning d-flex align-items-start" role="alert">
                        <i class="fas fa-shopping-cart me-2 mt-1"></i>
                        <div>
                            Order requests are now managed via Department Carts.
                        </div>
                    </div>
                    <!-- Search Form -->
                    <form method="GET" action="{{ route('restock-requests.index') }}" class="mb-4">
                        <div class="row g-2">
                            <div class="col-md-10">
                                <input type="text" name="search" class="form-control" 
                                       placeholder="Search by supply name, supplier, status, or ID..." 
                                       value="{{ request('search') }}">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-outline-primary w-100">
                                    <i class="fas fa-search"></i> Search
                                </button>
                            </div>
                        </div>
                    </form>

                    @if(request('search'))
                        <!-- Search Results Section -->
                        <div class="mb-4">
                            <h4>Search Results for "{{ request('search') }}"</h4>
                            <a href="{{ route('restock-requests.index') }}" class="btn btn-sm btn-secondary mb-3">

                            </a>
                            
                            @if($restockRequests->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover table-striped align-middle">
                                        <thead class="table-dark">
                                            <tr>
                                                <th><i class="fas fa-hashtag me-1"></i>Order ID</th>
                                                <th><i class="fas fa-sort-numeric-up me-1"></i>Quantity</th>
                                                <th><i class="fas fa-truck me-1"></i>Supplier</th>
                                                <th><i class="fas fa-building me-1"></i>Requested By</th>
                                                <th><i class="fas fa-info-circle me-1"></i>Status</th>
                                                <th><i class="fas fa-calendar-alt me-1"></i>Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($restockRequests as $request)
                                            <tr class="align-middle" style="cursor: pointer;" onclick="window.location='{{ route('restock-requests.show', $request->id) }}'">
                                                <td class="fw-bold">#{{ $request->id }}</td>
                                                <td>
                                                    <span class="badge bg-secondary fs-6">{{ $request->quantity }}</span>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="me-2">
                                                            <i class="fas fa-truck text-info"></i>
                                                        </div>
                                                        <div>
                                                            {{ $request->supplier ? $request->supplier->name : 'Not assigned' }}<br>
                                                            @if($request->supplier)
                                                                <small class="text-muted">{{ $request->supplier->email }}</small>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="me-2">
                                                            <i class="fas fa-building text-secondary"></i>
                                                        </div>
                                                        <div>
                                                            {{ $request->requestedDepartment?->department_name ?? '—' }}
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    @if($request->status == 'pending')
                                                        <span class="badge bg-warning text-dark fs-6">
                                                            <i class="fas fa-clock me-1"></i>Pending
                                                        </span>
                                                    @elseif($request->status == 'ordered')
                                                        <span class="badge bg-info fs-6">
                                                            <i class="fas fa-shopping-cart me-1"></i>Ordered
                                                        </span>
                                                    @elseif($request->status == 'delivered')
                                                        <span class="badge bg-success fs-6">
                                                            <i class="fas fa-check-circle me-1"></i>Delivered
                                                        </span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <small class="text-muted">
                                                        <i class="fas fa-calendar-alt me-1"></i>
                                                        {{ $request->created_at->format('M d, Y') }}
                                                    </small>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> No order requests found matching your search.
                                </div>
                            @endif
                        </div>
                    @else
                        <!-- Table-Based Layout -->
                        @php

                            $allRequests = $newestPending->concat($newestOrdered)->concat($newestDelivered)->sortByDesc('created_at');
                        @endphp

                        @if($allRequests->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Order ID</th>
                                            <th>Quantity</th>
                                            <th>Supplier</th>
                                            <th>Requested By</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($allRequests as $request)
                                        <tr style="cursor: pointer;" onclick="window.location='{{ route('restock-requests.show', $request->id) }}'">
                                            <td>#{{ $request->id }}</td>
                                            <td>{{ $request->quantity }}</td>
                                            <td>
                                                {{ $request->supplier ? $request->supplier->name : 'Not assigned' }}
                                                @if($request->supplier && $request->supplier->email)
                                                    <br><small class="text-muted">{{ $request->supplier->email }}</small>
                                                @endif
                                            </td>
                                            <td>{{ $request->requestedDepartment?->department_name ?? '—' }}</td>
                                            <td>
                                                @if($request->status == 'pending')
                                                    <span class="badge bg-warning text-dark">Pending</span>
                                                @elseif($request->status == 'ordered')
                                                    <span class="badge bg-info">Ordered</span>
                                                @elseif($request->status == 'delivered')
                                                    <span class="badge bg-success">Delivered</span>
                                                @endif
                                            </td>
                                            <td>{{ $request->created_at->format('M d, Y') }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-info text-center">
                                <i class="fas fa-info-circle fa-2x mb-3"></i>
                                <h5>No Order Requests Found</h5>
                                <p class="mb-0">There are currently no order requests in the system.</p>
                            </div>
                        @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const filter = document.getElementById('departmentFilter');
    const btn = document.getElementById('viewDepartmentCartBtn');
    const baseUrl = "{{ url('/department-carts') }}";

    function updateButton() {
        const deptId = filter.value;
        if (deptId) {
            btn.style.display = 'inline-block';
            btn.setAttribute('href', baseUrl + '/' + deptId);
        } else {
            btn.style.display = 'none';
            btn.removeAttribute('href');
        }
    }

    filter.addEventListener('change', updateButton);
    // Initialize on load if a department is preselected via query
    updateButton();
});
</script>
@endsection