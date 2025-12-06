@extends('layouts.app')

@section('title', 'User Profile - ' . $user->name)

@section('content')
<div class="container-fluid">
    <!-- User Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-1">{{ $user->name }}</h2>
                            <p class="text-muted mb-2">{{ $user->email }}</p>
                            <div class="d-flex gap-2">
                                <span class="badge bg-primary">{{ $user->role->name ?? 'No Role' }}</span>
                                @if($user->department)
                                    <span class="badge bg-secondary">
                                        <a href="{{ route('departments.show', $user->department) }}" class="text-white text-decoration-none">
                                            {{ $user->department->name }}
                                        </a>
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="text-end">
                            <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left"></i> Back
                            </a>
                            @if(auth()->user() && auth()->user()->hasRole('dean'))
                                @php($authUser = auth()->user())
                                @php($sameDepartment = $authUser->department_id && $user->department_id && $authUser->department_id === $user->department_id)
                                @php($eligibleRole = in_array($user->role->name ?? null, ['student','adviser']))
                                @php($notSelf = $authUser->id !== $user->id)
                                @if($sameDepartment && $eligibleRole && $notSelf)
                                    <form method="POST" action="{{ route('dean.users.access.assign', $user) }}" class="d-inline-block ms-2 align-middle">
                                        @csrf
                                        <div class="d-inline-flex align-items-center gap-2">
                                            <select name="duration" id="deanDurationSelect" class="form-select form-select-sm" style="width:auto">
                                                <option value="1_day">1 Day</option>
                                                <option value="1_week">1 Week</option>
                                                <option value="custom">Custom</option>
                                                <option value="indefinite">Indefinite</option>
                                            </select>
                                            <input type="datetime-local" name="expires_at" id="deanExpiresAt" class="form-control form-control-sm d-none" style="width:auto" />
                                            <button type="submit" class="btn btn-primary btn-sm" title="Grant temporary dean-level access">
                                                <i class="fas fa-user-shield me-1"></i> Grant Dean Access
                                            </button>
                                        </div>
                                    </form>
                                    <script>
                                        (function(){
                                            const duration = document.getElementById('deanDurationSelect');
                                            const expiresAt = document.getElementById('deanExpiresAt');
                                            function toggleCustom(){
                                                if(duration && expiresAt){
                                                    if(duration.value === 'custom'){
                                                        expiresAt.classList.remove('d-none');
                                                        // Pre-fill 24h in the future for convenience
                                                        const d = new Date(Date.now() + 24*60*60*1000);
                                                        const pad = n => String(n).padStart(2,'0');
                                                        const local = `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
                                                        expiresAt.value = local;
                                                    } else {
                                                        expiresAt.classList.add('d-none');
                                                        expiresAt.value = '';
                                                    }
                                                }
                                            }
                                            if(duration){ duration.addEventListener('change', toggleCustom); }
                                            toggleCustom();
                                        })();
                                    </script>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title text-primary">{{ $stats['total_borrowed'] }}</h5>
                    <p class="card-text">Total Borrowed Items</p>
                    <small class="text-muted">{{ $stats['active_borrowed'] }} currently active</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title text-info">{{ $stats['total_supply_requests'] }}</h5>
                    <p class="card-text">Supply Requests</p>
                    <small class="text-muted">{{ $stats['pending_supply_requests'] }} pending</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title text-warning">{{ $stats['total_loan_requests'] }}</h5>
                    <p class="card-text">Borrow Requests</p>
                    <small class="text-muted">{{ $stats['pending_loan_requests'] }} pending</small>
                </div>
            </div>
        </div>
        @if($user->hasAdminPrivileges())
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title text-success">{{ $issuedItems->total() ?? 0 }}</h5>
                    <p class="card-text">Items Issued</p>
                    <small class="text-muted">As admin</small>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Tabbed Content -->
    <div class="card">
        <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs" id="userTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="borrowed-tab" data-bs-toggle="tab" data-bs-target="#borrowed" type="button" role="tab">
                        Borrowed Items ({{ $borrowedItems->total() }})
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="supply-requests-tab" data-bs-toggle="tab" data-bs-target="#supply-requests" type="button" role="tab">
                        Supply Requests ({{ $supplyRequests->total() }})
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="loan-requests-tab" data-bs-toggle="tab" data-bs-target="#loan-requests" type="button" role="tab">
                        <h3 class="text-md font-medium text-gray-700">Borrow Requests</h3>
                        Borrow Requests ({{ $loanRequests->total() }})
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="interdept-loans-tab" data-bs-toggle="tab" data-bs-target="#interdept-loans" type="button" role="tab">
                        Inter-Dept Loans ({{ $interDeptLoanRequests->total() }})
                    </button>
                </li>
                @if($user->hasAdminPrivileges())
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="issued-items-tab" data-bs-toggle="tab" data-bs-target="#issued-items" type="button" role="tab">
                        Issued Items ({{ $issuedItems->total() ?? 0 }})
                    </button>
                </li>
                @endif
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content" id="userTabsContent">
                <!-- Borrowed Items Tab -->
                <div class="tab-pane fade show active" id="borrowed" role="tabpanel">
                    @if($borrowedItems->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Supply</th>
                                        <th>Borrowed Date</th>
                                        <th>Due Date</th>
                                        <th>Returned Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($borrowedItems as $item)
                                    <tr>
                                        <td>{{ $item->supply->name ?? 'N/A' }}</td>
                                        <td>{{ $item->borrowed_at ? $item->borrowed_at->format('M d, Y') : 'N/A' }}</td>
                                        <td>{{ $item->due_date ? $item->due_date->format('M d, Y') : 'N/A' }}</td>
                                        <td>{{ $item->returned_at ? $item->returned_at->format('M d, Y') : 'Not returned' }}</td>
                                        <td>
                                            @if($item->returned_at)
                                                <span class="badge bg-success">Returned</span>
                                            @elseif($item->due_date && $item->due_date->isPast())
                                                <span class="badge bg-danger">Overdue</span>
                                            @else
                                                <span class="badge bg-warning">Active</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        {{ $borrowedItems->links() }}
                    @else
                        <p class="text-muted">No borrowed items found.</p>
                    @endif
                </div>

                <!-- Supply Requests Tab -->
                <div class="tab-pane fade" id="supply-requests" role="tabpanel">
                    @if($supplyRequests->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Request Date</th>
                                        <th>Items</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($supplyRequests as $request)
                                    <tr>
                                        <td>{{ $request->created_at->format('M d, Y') }}</td>
                                        <td>{{ $request->items_count ?? 'N/A' }} items</td>
                                        <td>
                                            <span class="badge bg-{{ $request->status === 'approved' ? 'success' : ($request->status === 'rejected' ? 'danger' : 'warning') }}">
                                                {{ ucfirst($request->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('supply-requests.show', $request) }}" class="btn btn-sm btn-outline-primary">View</a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        {{ $supplyRequests->links() }}
                    @else
                        <p class="text-muted">No supply requests found.</p>
                    @endif
                </div>

                <!-- Loan Requests Tab -->
                <div class="tab-pane fade" id="loan-requests" role="tabpanel">
                    @if($loanRequests->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Supply</th>
                                        <th>Request Date</th>
                                        <th>Purpose</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($loanRequests as $request)
                                    <tr>
                                        <td>{{ $request->supply->name ?? 'N/A' }}</td>
                                        <td>{{ $request->created_at->format('M d, Y') }}</td>
                                        <td>{{ Str::limit($request->purpose, 50) }}</td>
                                        <td>
                                            <span class="badge bg-{{ $request->status === 'approved' ? 'success' : ($request->status === 'rejected' ? 'danger' : 'warning') }}">
                                                {{ ucfirst($request->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('loan-requests.show', $request) }}" class="btn btn-sm btn-outline-primary">View</a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        {{ $loanRequests->links() }}
                    @else
                        <p class="text-gray-500">No borrow requests found.</p>
                    @endif
                </div>

                <!-- Inter-Department Loans Tab -->
                <div class="tab-pane fade" id="interdept-loans" role="tabpanel">
                    @if($interDeptLoanRequests->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Items</th>
                                        <th>Departments</th>
                                        <th>Request Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($interDeptLoanRequests as $req)
                                    <tr>
                                        <td>
                                            @php($items = $req->requestItems ?? collect())
                                            @if(method_exists($items, 'count') ? $items->count() > 0 : count($items) > 0)
                                                @php($names = collect($items)->map(function($ri){ return optional(optional($ri->issuedItem)->supply)->name; })->filter()->unique()->values())
                                                {{ $names->take(3)->implode(', ') }}@if($names->count() > 3) +{{ $names->count() - 3 }} more @endif
                                            @else
                                                {{ optional(optional($req->issuedItem)->supply)->name ?? 'N/A' }}
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark">
                                                {{ $req->lendingDepartment->name ?? 'N/A' }}
                                                <i class="fas fa-arrow-right mx-1"></i>
                                                {{ $req->borrowingDepartment->name ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td>{{ $req->created_at->format('M d, Y') }}</td>
                                        <td>
                                            <span class="badge bg-{{ $req->status === 'pending' ? 'warning' : ($req->status === 'borrowed' ? 'info' : ($req->status === 'completed' ? 'success' : 'secondary')) }}">
                                                {{ ucfirst(str_replace('_', ' ', $req->status)) }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('inter-department-loans.show', $req) }}" class="btn btn-sm btn-outline-primary">
                                                View
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        {{ $interDeptLoanRequests->links() }}
                    @else
                        <p class="text-muted">No inter-department loan requests found.</p>
                    @endif
                </div>

                <!-- Issued Items Tab (Admin only) -->
                @if($user->hasAdminPrivileges())
                <div class="tab-pane fade" id="issued-items" role="tabpanel">
                    @if($issuedItems && $issuedItems->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Supply</th>
                                        <th>Variant</th>
                                        <th>Quantity</th>
                                        <th>Issued Date</th>
                                        <th>Purpose</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($issuedItems as $item)
                                    <tr>
                                        <td>{{ $item->supply->name ?? 'N/A' }}</td>
                                        <td>{{ $item->supplyVariant->variant_name ?? 'Default' }}</td>
                                        <td>{{ $item->quantity }}</td>
                                        <td>{{ $item->created_at->format('M d, Y') }}</td>
                                        <td>{{ Str::limit($item->purpose, 50) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        {{ $issuedItems->links() }}
                    @else
                        <p class="text-muted">No issued items found.</p>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection