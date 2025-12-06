@extends('layouts.app')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/inter-department-loans.css') }}">
@endsection

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
                <i class="fas fa-file-alt text-primary me-2"></i>
                Borrow Request Details
            </h1>
            <p class="text-muted mb-0">Request #{{ $interDepartmentLoan->id }} - {{ $interDepartmentLoan->created_at->format('M d, Y') }}</p>
        </div>
        <div class="d-flex gap-2">
            @if($interDepartmentLoan->status == 'pending' && (auth()->user()->hasRole('admin') || auth()->user()->id == $interDepartmentLoan->requested_by))
                <a href="{{ route('loan-requests.inter-department.edit', $interDepartmentLoan) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-edit me-2"></i>Edit Request
                </a>
            @endif
            <a href="{{ route('loan-requests.index', ['tab' => 'inter']) }}" class="btn btn-primary">
                <i class="fas fa-arrow-left me-2"></i>Back to List
            </a>
        </div>
    </div>

    <!-- Status Badge -->
    <div class="mb-4">
        @switch($interDepartmentLoan->status)
            @case('pending')
                <span class="badge bg-warning fs-6 px-3 py-2">
                    <i class="fas fa-clock me-2"></i>Pending Dean Approval
                </span>
                @break
            @case('dean_approved')
                <span class="badge bg-info fs-6 px-3 py-2">
                    <i class="fas fa-user-check me-2"></i>Pending Lending Department Approval
                </span>
                @break
            @case('lending_approved')
                <span class="badge bg-primary fs-6 px-3 py-2">
                    <i class="fas fa-handshake me-2"></i>Pending Final Admin Approval
                </span>
                @break
            @case('admin_approved')
                <span class="badge bg-success fs-6 px-3 py-2">
                    <i class="fas fa-check-circle me-2"></i>Admin Approved - Ready for Transfer
                </span>
                @break
            @case('completed')
                <span class="badge bg-success fs-6 px-3 py-2">
                    <i class="fas fa-check-double me-2"></i>Completed
                </span>
                @break
            @case('declined')
                <span class="badge bg-danger fs-6 px-3 py-2">
                    <i class="fas fa-times-circle me-2"></i>Declined
                </span>
                @break
            @default
                <span class="badge bg-secondary fs-6 px-3 py-2">{{ ucfirst($interDepartmentLoan->status) }}</span>
        @endswitch
    </div>

    <div class="row g-4">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Request Information -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>Request Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        @if($interDepartmentLoan->requestItems && $interDepartmentLoan->requestItems->count() > 0)
                            <!-- Batch Requested Items -->
                            <div class="col-12">
                                <div class="d-flex align-items-start mb-2">
                                    <div class="bg-light rounded p-2 me-3">
                                        <i class="fas fa-list text-primary"></i>
                                    </div>
                                    <div>
                                        <h6 class="text-muted mb-1">Requested Items</h6>
                                        <div class="fw-semibold">{{ $interDepartmentLoan->requestItems->count() }} item(s) · Total Qty: {{ $interDepartmentLoan->quantity_requested }}</div>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-sm table-striped align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Supply</th>
                                                <th class="text-center">Quantity Requested</th>
                                                <th>From Department</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($interDepartmentLoan->requestItems as $ri)
                                                <tr>
                                                    <td>
                                                        <div class="fw-semibold">{{ optional(optional($ri->issuedItem)->supply)->name }}</div>
                                                        @if(optional(optional($ri->issuedItem)->supply)->description)
                                                            <small class="text-muted">{{ optional(optional($ri->issuedItem)->supply)->description }}</small>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge bg-info">{{ $ri->quantity_requested }}</span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-light text-dark">{{ optional(optional($ri->issuedItem)->department)->department_name }}</span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @else
                        <div class="col-md-6">
                            <div class="d-flex align-items-start">
                                <div class="bg-light rounded p-2 me-3">
                                    <i class="fas fa-box text-primary"></i>
                                </div>
                                <div>
                                    <h6 class="text-muted mb-1">Supply Item</h6>
                                    <div class="fw-semibold">{{ $interDepartmentLoan->issuedItem->supply->name }}</div>
                                    @if($interDepartmentLoan->issuedItem->supply->description)
                                        <small class="text-muted">{{ $interDepartmentLoan->issuedItem->supply->description }}</small>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="d-flex align-items-start">
                                <div class="bg-light rounded p-2 me-3">
                                    <i class="fas fa-hashtag text-primary"></i>
                                </div>
                                <div>
                                    <h6 class="text-muted mb-1">Quantity Requested</h6>
                                    <span class="badge bg-info fs-6">{{ $interDepartmentLoan->quantity_requested }}</span>
                                </div>
                            </div>
                        </div>
                        @endif

                        <div class="col-md-6">
                            <div class="d-flex align-items-start">
                                <div class="bg-light rounded p-2 me-3">
                                    <i class="fas fa-building text-primary"></i>
                                </div>
                                <div>
                                    <h6 class="text-muted mb-1">From Department</h6>
                                    <span class="badge bg-light text-dark">{{ $interDepartmentLoan->lendingDepartment->department_name }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="d-flex align-items-start">
                                <div class="bg-light rounded p-2 me-3">
                                    <i class="fas fa-building text-primary"></i>
                                </div>
                                <div>
                                    <h6 class="text-muted mb-1">To Department</h6>
                                    <span class="badge bg-light text-dark">{{ $interDepartmentLoan->borrowingDepartment->department_name }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="d-flex align-items-start">
                                <div class="bg-light rounded p-2 me-3">
                                    <i class="fas fa-user text-primary"></i>
                                </div>
                                <div>
                                    <h6 class="text-muted mb-1">Requested By</h6>
                                    <a href="{{ route('users.profile', $interDepartmentLoan->requestedBy) }}" class="fw-semibold text-decoration-none">
                                        {{ $interDepartmentLoan->requestedBy->name }}
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="d-flex align-items-start">
                                <div class="bg-light rounded p-2 me-3">
                                    <i class="fas fa-calendar text-primary"></i>
                                </div>
                                <div>
                                    <h6 class="text-muted mb-1">Expected Return Date</h6>
                                    <div class="fw-semibold">{{ \Carbon\Carbon::parse($interDepartmentLoan->expected_return_date)->format('F d, Y') }}</div>
                                </div>
                            </div>
                        </div>

                        @if($interDepartmentLoan->deanApprovedBy || $interDepartmentLoan->lendingApprovedBy || $interDepartmentLoan->adminApprovedBy)
                            <div class="col-12">
                                <div class="d-flex align-items-start">
                                    <div class="bg-light rounded p-2 me-3">
                                        <i class="fas fa-check-circle text-success"></i>
                                    </div>
                                    <div>
                                        <h6 class="text-muted mb-1">Approved By</h6>
                                        <div class="fw-semibold">
                                            @if($interDepartmentLoan->deanApprovedBy)
                                                <span class="badge bg-success me-2">Dean: {{ $interDepartmentLoan->deanApprovedBy->name }}</span>
                                            @endif
                                            @if($interDepartmentLoan->lendingApprovedBy)
                                                <span class="badge bg-primary me-2">Lending Dean: {{ $interDepartmentLoan->lendingApprovedBy->name }}</span>
                                            @endif
                                            @if($interDepartmentLoan->adminApprovedBy)
                                                <span class="badge bg-info text-dark me-2">Admin: {{ $interDepartmentLoan->adminApprovedBy->name }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if($interDepartmentLoan->approvalLogs && $interDepartmentLoan->approvalLogs->count() > 0)
                            <div class="col-12">
                                <div class="d-flex align-items-start">
                                    <div class="bg-light rounded p-2 me-3">
                                        <i class="fas fa-list-alt text-primary"></i>
                                    </div>
                                    <div class="w-100">
                                        <h6 class="text-muted mb-1">Approval History</h6>
                                        <ul class="list-group list-group-flush">
                                            @foreach($interDepartmentLoan->approvalLogs as $log)
                                                <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <span class="badge bg-secondary me-2 text-capitalize">{{ str_replace('_', ' ', $log->action) }}</span>
                                                        <span class="fw-semibold">{{ $log->approver->name ?? 'Unknown' }}</span>
                                                        @if($log->approver_role)
                                                            <small class="text-muted ms-2">({{ ucfirst(str_replace('_',' ', $log->approver_role)) }})</small>
                                                        @endif
                                                        @if($log->notes)
                                                            <div class="text-muted small mt-1">Note: {{ $log->notes }}</div>
                                                        @endif
                                                    </div>
                                                    <small class="text-muted">{{ $log->created_at->format('M d, Y h:i A') }}</small>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if($interDepartmentLoan->purpose)
                            <div class="col-12">
                                <div class="d-flex align-items-start">
                                    <div class="bg-light rounded p-2 me-3">
                                        <i class="fas fa-bullseye text-primary"></i>
                                    </div>
                                    <div>
                                        <h6 class="text-muted mb-1">Purpose</h6>
                                        <div class="fw-semibold">{{ $interDepartmentLoan->purpose }}</div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if($interDepartmentLoan->notes)
                            <div class="col-12">
                                <div class="d-flex align-items-start">
                                    <div class="bg-light rounded p-2 me-3">
                                        <i class="fas fa-sticky-note text-primary"></i>
                                    </div>
                                    <div>
                                        <h6 class="text-muted mb-1">Additional Notes</h6>
                                        <div class="fw-semibold">{{ $interDepartmentLoan->notes }}</div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Approval Actions -->
            @if(!in_array($interDepartmentLoan->status, ['declined','borrowed','completed']))
                <div class="row g-4 mt-4">
                    <div class="col-12">
                        <!-- Dean Approval -->
                        @if(auth()->user()->hasRole('dean') && auth()->user()->isDeanOf($interDepartmentLoan->borrowingDepartment) && $interDepartmentLoan->status == 'pending')
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-header bg-warning text-dark">
                                    <h6 class="mb-0">
                                        <i class="fas fa-user-tie me-2"></i>Dean Approval Required
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <p class="mb-3">This request requires dean approval before proceeding.</p>
                                    <div class="d-flex gap-2">
                                        <button onclick="showApprovalForm('dean')" 
                                                class="btn btn-success"
                                                data-bs-toggle="tooltip"
                                                title="Approve this request as dean">
                                            <i class="fas fa-check me-2"></i>Approve
                                        </button>
                                        <button onclick="showDeclineForm()" 
                                                class="btn btn-danger"
                                                data-bs-toggle="tooltip"
                                                title="Decline this request with reason">
                                            <i class="fas fa-times me-2"></i>Decline
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Lending Department Dean Approval -->
                        @if(auth()->user()->isDeanOf($interDepartmentLoan->lendingDepartment) && $interDepartmentLoan->status == 'dean_approved')
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-header bg-info text-white">
                                    <h6 class="mb-0">
                                        <i class="fas fa-building me-2"></i>Lending Department Dean Approval Required
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <p class="mb-3">As the dean of the lending department, your approval is required before this request can proceed to administration.</p>
                                    <div class="d-flex gap-2">
                                        <button onclick="showApprovalForm('lending-dean')" 
                                                class="btn btn-success"
                                                data-bs-toggle="tooltip"
                                                title="Approve as lending department dean">
                                            <i class="fas fa-check me-2"></i>Approve
                                        </button>
                                        <button onclick="showDeclineForm()" 
                                                class="btn btn-danger"
                                                data-bs-toggle="tooltip"
                                                title="Decline this request with reason">
                                            <i class="fas fa-times me-2"></i>Decline
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Admin Approval / Issue Items (Borrowed) -->
                         @if(auth()->user()->hasAdminPrivileges() && $interDepartmentLoan->status == 'lending_dean_approved')
                             <div class="card border-0 shadow-sm mb-4">
                                 <div class="card-header bg-success text-white">
                                    <h6 class="mb-0">
                                        <i class="fas fa-user-shield me-2"></i>Approve
                                    </h6>
                                 </div>
                                 <div class="card-body">
                                     <p class="mb-3">Approve this request. This action issues items and marks the status as <strong>Borrowed</strong>.</p>
                                     <div class="d-flex gap-2">
                                         <button onclick="showApprovalForm('admin')" 
                                                 class="btn btn-success"
                                                 data-bs-toggle="tooltip"
                                                 title="Approve and mark request as Borrowed">
                                             <i class="fas fa-check me-2"></i>Approve
                                         </button>
                                         <button onclick="showDeclineForm()" 
                                                 class="btn btn-danger"
                                                 data-bs-toggle="tooltip"
                                                 title="Decline this request with reason">
                                             <i class="fas fa-times me-2"></i>Decline
                                         </button>
                                     </div>
                                 </div>
                             </div>
                         @endif
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Status Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="fas fa-flag me-2"></i>Request Status
                    </h6>
                </div>
                <div class="card-body text-center">
                    @if($interDepartmentLoan->status == 'pending')
                        <div class="status-icon mb-3">
                            <i class="fas fa-clock fa-3x text-warning"></i>
                        </div>
                        <span class="badge bg-warning fs-6 px-3 py-2">Pending</span>
                    @elseif($interDepartmentLoan->status == 'borrowed')
                        <div class="status-icon mb-3">
                            <i class="fas fa-box-open fa-3x text-primary"></i>
                        </div>
                        <span class="badge bg-primary fs-6 px-3 py-2">Borrowed</span>
                    @elseif($interDepartmentLoan->status == 'completed')
                        <div class="status-icon mb-3">
                            <i class="fas fa-check-double fa-3x text-success"></i>
                        </div>
                        <span class="badge bg-success fs-6 px-3 py-2">Completed</span>
                    @elseif($interDepartmentLoan->status == 'declined')
                        <div class="status-icon mb-3">
                            <i class="fas fa-times-circle fa-3x text-danger"></i>
                        </div>
                        <span class="badge bg-danger fs-6 px-3 py-2">Declined</span>
                    @endif
                </div>
            </div>

            <!-- Timeline Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="fas fa-history me-2"></i>Request Timeline
                    </h6>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Request Created</h6>
                                <small class="text-muted">{{ \Carbon\Carbon::parse($interDepartmentLoan->created_at)->format('M d, Y g:i A') }}</small>
                            </div>
                        </div>

                        @if($interDepartmentLoan->dean_approved_at)
                             <div class="timeline-item">
                                 <div class="timeline-marker bg-info"></div>
                                 <div class="timeline-content">
                                     <h6 class="mb-1">Approved by {{ $interDepartmentLoan->borrowingDepartment->name }} Dean</h6>
                                     <small class="text-muted">{{ \Carbon\Carbon::parse($interDepartmentLoan->dean_approved_at)->format('M d, Y g:i A') }}</small>
                                 </div>
                             </div>
                         @endif

                         @if($interDepartmentLoan->lending_approved_at)
                             <div class="timeline-item">
                                 <div class="timeline-marker bg-warning"></div>
                                 <div class="timeline-content">
                                     <h6 class="mb-1">Approved by {{ $interDepartmentLoan->lendingDepartment->name }} Dean</h6>
                                     <small class="text-muted">{{ \Carbon\Carbon::parse($interDepartmentLoan->lending_approved_at)->format('M d, Y g:i A') }}</small>
                                 </div>
                             </div>
                         @endif

                         @if($interDepartmentLoan->admin_approved_at)
                             <div class="timeline-item">
                                 <div class="timeline-marker bg-success"></div>
                                 <div class="timeline-content">
                                     <h6 class="mb-1">Items Issued (Borrowed)</h6>
                                     <small class="text-muted d-block">{{ \Carbon\Carbon::parse($interDepartmentLoan->admin_approved_at)->format('M d, Y g:i A') }}</small>
                                     @if($interDepartmentLoan->adminApprovedBy)
                                         <small class="text-muted">Processed by {{ $interDepartmentLoan->adminApprovedBy->name }}</small>
                         @endif
                    </div>
                </div>
            @endif

            <!-- Return Actions -->
            @if($interDepartmentLoan->status === 'borrowed' || $interDepartmentLoan->status === 'return_pending')
                <div class="row g-4 mt-4">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">
                                    <i class="fas fa-undo me-2"></i>Return Items
                                </h6>
                                @if($interDepartmentLoan->status === 'return_pending')
                                    <span class="badge bg-warning text-dark">Return Pending Verification</span>
                                @endif
                            </div>
                            <div class="card-body">
                                @php
                                    $latestReturn = \App\Models\InterDepartmentReturnRecord::whereIn('inter_department_borrowed_item_id', $interDepartmentLoan->interDepartmentBorrowedItems->pluck('id'))
                                        ->orderBy('created_at', 'desc')
                                        ->first();
                                @endphp
                                @php
                                    $hasPendingReturn = ($latestReturn && !$latestReturn->verified_at) || ($interDepartmentLoan->status === 'return_pending');
                                    $activeReturnableCount = $interDepartmentLoan->interDepartmentBorrowedItems->whereIn('status', ['active','overdue'])->count();
                                @endphp

                                {{-- Borrower: show initiate form (only when no pending return exists) --}}
                                @if(auth()->user()->id === $interDepartmentLoan->requested_by && !$hasPendingReturn)
                                    @if($activeReturnableCount === 1)
                                        <p class="mb-3">Initiate a return. You may optionally attach a photo for verification.</p>
                                        <form method="POST" action="{{ route('loan-requests.inter-department.return', $interDepartmentLoan) }}" enctype="multipart/form-data">
                                            @csrf
                                            @method('PATCH')
                                            <div class="row g-3">
                                                <div class="col-md-8">
                                                    <label for="return-notes" class="form-label fw-semibold">
                                                        <i class="fas fa-sticky-note me-2 text-primary"></i>Notes (Optional)
                                                    </label>
                                                    <textarea name="return_notes" id="return-notes" class="form-control" rows="3" placeholder="Add any notes about the condition or reason..."></textarea>
                                                </div>
                                                <div class="col-md-4">
                                                    <label for="return-photo" class="form-label fw-semibold">
                                                        <i class="fas fa-camera me-2 text-primary"></i>Photo (Optional)
                                                    </label>
                                                    <input type="file" name="return_photo" id="return-photo" class="form-control" accept="image/*" />
                                                </div>
                                                <div class="col-md-4">
                                                    <label for="missing-count" class="form-label fw-semibold">
                                                        <i class="fas fa-question-circle me-2 text-primary"></i>Missing Items
                                                    </label>
                                                    <input type="number" min="0" name="missing_count" id="missing-count" class="form-control" placeholder="0" />
                                                </div>
                                                <div class="col-md-4">
                                                    <label for="damaged-count" class="form-label fw-semibold">
                                                        <i class="fas fa-tools me-2 text-primary"></i>Damaged Items
                                                    </label>
                                                    <input type="number" min="0" name="damaged_count" id="damaged-count" class="form-control" placeholder="0" />
                                                </div>
                                                <div class="col-md-4" id="severity-container" style="display:none;">
                                                    <label for="damage-severity" class="form-label fw-semibold">
                                                        <i class="fas fa-exclamation-triangle me-2 text-primary"></i>Damage Severity
                                                    </label>
                                                    <select name="damage_severity" id="damage-severity" class="form-select">
                                                        <option value="">Select severity</option>
                                                        <option value="minor">Minor</option>
                                                        <option value="moderate">Moderate</option>
                                                        <option value="severe">Severe</option>
                                                        <option value="total_loss">Total Loss</option>
                                                    </select>
                                                    <small class="text-muted">Only required if damaged items are reported.</small>
                                                </div>
                                            </div>
                                            <div class="mt-3 d-flex gap-2">
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="fas fa-undo me-2"></i>Initiate Return
                                                </button>
                                            </div>
                                        </form>
                                        <script>
                                            document.addEventListener('DOMContentLoaded', function() {
                                                const damagedInput = document.getElementById('damaged-count');
                                                const severitySelect = document.getElementById('damage-severity');
                                                const severityContainer = document.getElementById('severity-container');

                                                function syncSeverityVisibility() {
                                                    const val = parseInt(damagedInput.value || '0', 10);
                                                    const hide = isNaN(val) || val <= 0;
                                                    severityContainer.style.display = hide ? 'none' : '';
                                                    if (hide) {
                                                        severitySelect.value = '';
                                                    }
                                                }

                                                syncSeverityVisibility();
                                                damagedInput.addEventListener('input', syncSeverityVisibility);
                                                damagedInput.addEventListener('change', syncSeverityVisibility);
                                            });
                                        </script>
                                    @else
                                        <div class="alert alert-secondary d-flex align-items-center justify-content-between">
                                            <div>
                                                <i class="fas fa-list me-2 text-primary"></i>
                                                Multiple items are active for return. Use the multi-item form to submit per-item details.
                                            </div>
                                            <a href="{{ route('loan-requests.inter-department.return-form', $interDepartmentLoan) }}" class="btn btn-outline-primary btn-sm">
                                                Return Multiple Items
                                            </a>
                                        </div>
                                    @endif
                                @elseif(auth()->user()->id === $interDepartmentLoan->requested_by && $hasPendingReturn && $latestReturn)
                                    <div class="alert alert-info mb-3">
                                        A return request is pending verification. You can edit your submitted details below.
                                    </div>
                                    <form method="POST" action="{{ route('loan-requests.inter-department.return.update', [$interDepartmentLoan, $latestReturn]) }}" enctype="multipart/form-data">
                                        @csrf
                                        @method('PATCH')
                                        <div class="row g-3">
                                            <div class="col-md-8">
                                                <label for="edit-return-notes" class="form-label fw-semibold">
                                                    <i class="fas fa-sticky-note me-2 text-primary"></i>Notes (Optional)
                                                </label>
                                                <textarea name="return_notes" id="edit-return-notes" class="form-control" rows="3" placeholder="Update notes...">{{ old('return_notes', $latestReturn->notes) }}</textarea>
                                            </div>
                                            <div class="col-md-4">
                                                <label for="edit-return-photo" class="form-label fw-semibold">
                                                    <i class="fas fa-camera me-2 text-primary"></i>Photo (Optional)
                                                </label>
                                                <input type="file" name="return_photo" id="edit-return-photo" class="form-control" accept="image/*" />
                                                @if($latestReturn->photo_path)
                                                    <small class="text-muted d-block mt-1">A photo is already attached. Upload to replace.</small>
                                                @endif
                                            </div>
                                            <div class="col-md-4">
                                                <label for="edit-missing-count" class="form-label fw-semibold">
                                                    <i class="fas fa-question-circle me-2 text-primary"></i>Missing Items
                                                </label>
                                                <input type="number" min="0" name="missing_count" id="edit-missing-count" class="form-control" value="{{ old('missing_count', $latestReturn->missing_count) }}" placeholder="0" />
                                            </div>
                                            <div class="col-md-4">
                                                <label for="edit-damaged-count" class="form-label fw-semibold">
                                                    <i class="fas fa-tools me-2 text-primary"></i>Damaged Items
                                                </label>
                                                <input type="number" min="0" name="damaged_count" id="edit-damaged-count" class="form-control" value="{{ old('damaged_count', $latestReturn->damaged_count) }}" placeholder="0" />
                                            </div>
                                            <div class="col-md-4" id="edit-severity-container" style="display:none;">
                                                <label for="edit-damage-severity" class="form-label fw-semibold">
                                                    <i class="fas fa-exclamation-triangle me-2 text-primary"></i>Damage Severity
                                                </label>
                                                <select name="damage_severity" id="edit-damage-severity" class="form-select">
                                                    <option value="">Select severity</option>
                                                    <option value="minor" {{ old('damage_severity', $latestReturn->damage_severity) === 'minor' ? 'selected' : '' }}>Minor</option>
                                                    <option value="moderate" {{ old('damage_severity', $latestReturn->damage_severity) === 'moderate' ? 'selected' : '' }}>Moderate</option>
                                                    <option value="severe" {{ old('damage_severity', $latestReturn->damage_severity) === 'severe' ? 'selected' : '' }}>Severe</option>
                                                    <option value="total_loss" {{ old('damage_severity', $latestReturn->damage_severity) === 'total_loss' ? 'selected' : '' }}>Total Loss</option>
                                                </select>
                                                <small class="text-muted">Only required if damaged items are reported.</small>
                                            </div>
                                        </div>
                                        <div class="mt-3 d-flex gap-2">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-edit me-2"></i>Update Return Request
                                            </button>
                                        </div>
                                    </form>
                                    <script>
                                        document.addEventListener('DOMContentLoaded', function() {
                                            const damagedInput = document.getElementById('edit-damaged-count');
                                            const severitySelect = document.getElementById('edit-damage-severity');
                                            const severityContainer = document.getElementById('edit-severity-container');

                                            function syncSeverityVisibility() {
                                                const val = parseInt(damagedInput.value || '0', 10);
                                                const hide = isNaN(val) || val <= 0;
                                                severityContainer.style.display = hide ? 'none' : '';
                                                if (hide) {
                                                    severitySelect.value = '';
                                                }
                                            }

                                            syncSeverityVisibility();
                                            damagedInput.addEventListener('input', syncSeverityVisibility);
                                            damagedInput.addEventListener('change', syncSeverityVisibility);
                                        });
                                    </script>
                                @endif

                                {{-- Lending dean/admin: show return details + approve --}}
                                @if(($interDepartmentLoan->status === 'return_pending') && (auth()->user()->hasAdminPrivileges() || auth()->user()->isDeanOf($interDepartmentLoan->lendingDepartment)))
                                    <div class="border rounded p-3 bg-light mt-2">
                                        <h6 class="fw-semibold mb-2"><i class="fas fa-info-circle me-2 text-primary"></i>Return Details</h6>
                                        @if($latestReturn)
                                            <div class="mb-2">
                                                <span class="text-muted d-block">Initiated by</span>
                                                <span class="badge bg-light text-dark">{{ optional($latestReturn->initiatedBy)->name ?? 'Unknown' }}</span>
                                                <small class="text-muted ms-2">{{ $latestReturn->created_at->format('M d, Y g:i A') }}</small>
                                            </div>
                                            <div class="mb-2">
                                                <span class="text-muted d-block">Missing Items</span>
                                                <div>{{ $latestReturn->missing_count ?? 0 }}</div>
                                            </div>
                                            <div class="mb-2">
                                                <span class="text-muted d-block">Damaged Items</span>
                                                <div>{{ $latestReturn->damaged_count ?? 0 }}</div>
                                            </div>
                                            @php $severityLabel = $latestReturn->damage_severity ? ucfirst(str_replace('_', ' ', $latestReturn->damage_severity)) : null; @endphp
                                            @if($severityLabel)
                                                <div class="mb-2">
                                                    <span class="text-muted d-block">Damage Severity</span>
                                                    <div>{{ $severityLabel }}</div>
                                                </div>
                                            @endif
                                            @if($latestReturn->notes)
                                                <div class="mb-2">
                                                    <span class="text-muted d-block">Notes</span>
                                                    <div>{{ $latestReturn->notes }}</div>
                                                </div>
                                            @endif
                                            @if($latestReturn->photo_path)
                                                <div class="mb-2">
                                                    <span class="text-muted d-block">Photo</span>
                                                    <a href="#" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#returnPhotoModal">
                                                        <i class="fas fa-image me-1"></i>View Photo
                                                    </a>
                                                </div>
                                            @endif
                                        @else
                                            <div class="text-muted">No return details submitted yet.</div>
                                        @endif

                                        <div class="mt-3">
                                            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#verifyReturnModal">
                                                <i class="fas fa-check me-2"></i>Approve Return
                                            </button>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Return Records -->
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fas fa-history me-2"></i>Return Records</h6>
                            </div>
                            <div class="card-body">
                                @php
                                    $returnRecords = \App\Models\InterDepartmentReturnRecord::whereIn('inter_department_borrowed_item_id', $interDepartmentLoan->interDepartmentBorrowedItems->pluck('id'))
                                        ->with(['borrowedItem.issuedItem.supply'])
                                        ->orderBy('created_at', 'desc')
                                        ->get();
                                @endphp

                                @if($returnRecords->isEmpty())
                                    <div class="text-muted">No return records yet.</div>
                                @else
                                    <div class="list-group">
                                        @foreach($returnRecords as $record)
                                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                                <div>
                                                    <div class="fw-semibold">Initiated by {{ optional($record->initiatedBy)->name ?? 'Unknown' }}</div>
                                                    <small class="text-muted">{{ $record->created_at->format('M d, Y g:i A') }}</small>
                                                    <div class="text-muted">Item: {{ optional(optional(optional($record->borrowedItem)->issuedItem)->supply)->name ?? 'Unknown Item' }}</div>
                                                    @if($record->notes)
                                                        <div class="text-muted">Notes: {{ $record->notes }}</div>
                                                    @endif
                                                    <div class="text-muted">Missing: {{ $record->missing_count ?? 0 }} | Damaged: {{ $record->damaged_count ?? 0 }}
                                                        @if($record->damage_severity)
                                                            <span class="ms-2">Severity: {{ ucfirst(str_replace('_', ' ', $record->damage_severity)) }}</span>
                                                        @endif
                                                    </div>
                                                    @if($record->verified_at)
                                                        <div class="mt-1"><span class="badge bg-success">Verified</span> by {{ optional($record->verifiedBy)->name ?? 'Unknown' }} on {{ $record->verified_at->format('M d, Y g:i A') }}</div>
                                                    @else
                                                        <div class="mt-1"><span class="badge bg-warning text-dark">Awaiting Verification</span></div>
                                                    @endif
                                                </div>
                                                @if($record->photo_path)
                                                    <a href="{{ asset('storage/' . $record->photo_path) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                                                        <i class="fas fa-image me-1"></i>View Photo
                                                    </a>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Verify Return Modal -->
            <div class="modal fade" id="verifyReturnModal" tabindex="-1" aria-labelledby="verifyReturnModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header bg-success text-white">
                            <h5 class="modal-title" id="verifyReturnModalLabel">
                                <i class="fas fa-check-circle me-2"></i>Verify Return
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form id="verifyReturnForm" method="POST" action="{{ route('loan-requests.inter-department.verify-return', $interDepartmentLoan) }}">
                            @csrf
                            @method('PATCH')
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="verification-notes" class="form-label fw-semibold">
                                        <i class="fas fa-sticky-note me-2 text-primary"></i>Verification Notes (Optional)
                                    </label>
                                    <textarea name="verification_notes" id="verification-notes" class="form-control" rows="4" placeholder="Add any notes regarding verification..."></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    <i class="fas fa-times me-2"></i>Cancel
                                </button>
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-check me-2"></i>Confirm Return
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
                         @if($interDepartmentLoan->status == 'declined')
                             <div class="timeline-item">
                                 <div class="timeline-marker bg-danger"></div>
                                 <div class="timeline-content">
                                     <h6 class="mb-1">Request Declined</h6>
                                     <small class="text-muted">{{ \Carbon\Carbon::parse($interDepartmentLoan->updated_at)->format('M d, Y g:i A') }}</small>
                                 </div>
                             </div>
                         @endif
                    </div>
                </div>
            </div>

            <!-- Approval Notes -->
            <div class="row g-4 mt-4">
                <div class="col-12">
                    @if($interDepartmentLoan->dean_approval_notes)
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0">
                                    <i class="fas fa-user-tie me-2"></i>Dean Approval Notes
                                </h6>
                            </div>
                            <div class="card-body">
                                <p class="mb-0">{{ $interDepartmentLoan->dean_approval_notes }}</p>
                            </div>
                        </div>
                    @endif

                    @if($interDepartmentLoan->lending_approval_notes)
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-warning text-dark">
                                <h6 class="mb-0">
                                    <i class="fas fa-building me-2"></i>Lending Department Approval Notes
                                </h6>
                            </div>
                            <div class="card-body">
                                <p class="mb-0">{{ $interDepartmentLoan->lending_approval_notes }}</p>
                            </div>
                        </div>
                    @endif

                    @if($interDepartmentLoan->admin_approval_notes)
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-success text-white">
                                <h6 class="mb-0">
                                    <i class="fas fa-user-shield me-2"></i>Admin Approval Notes
                                </h6>
                            </div>
                            <div class="card-body">
                                <p class="mb-0">{{ $interDepartmentLoan->admin_approval_notes }}</p>
                            </div>
                        </div>
                    @endif

                    @if($interDepartmentLoan->decline_reason)
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-danger text-white">
                                <h6 class="mb-0">
                                    <i class="fas fa-exclamation-triangle me-2"></i>Decline Reason
                                </h6>
                            </div>
                            <div class="card-body">
                                <p class="mb-0">{{ $interDepartmentLoan->decline_reason }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
</div>
    </div>
</div>

@php
    $latestReturnForModal = \App\Models\InterDepartmentReturnRecord::whereIn('inter_department_borrowed_item_id', $interDepartmentLoan->interDepartmentBorrowedItems->pluck('id'))
        ->orderBy('created_at', 'desc')
        ->first();
@endphp
<!-- Return Photo Modal -->
<div class="modal fade" id="returnPhotoModal" tabindex="-1" aria-labelledby="returnPhotoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title" id="returnPhotoModalLabel">
                    <i class="fas fa-image me-2"></i>Return Photo
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @if($latestReturnForModal && $latestReturnForModal->photo_path)
                    <img src="{{ asset('storage/' . $latestReturnForModal->photo_path) }}" alt="Return Photo" class="img-fluid rounded border" />
                @else
                    <div class="text-muted">No photo available.</div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap Approval Modal -->
<div class="modal fade" id="approvalModal" tabindex="-1" aria-labelledby="approvalModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="approvalModalLabel">
                    <i class="fas fa-check-circle me-2"></i>Approve Request
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="approvalForm" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="approval-notes" class="form-label fw-semibold">
                            <i class="fas fa-sticky-note me-2 text-primary"></i>Notes (Optional)
                        </label>
                        <textarea name="notes" 
                                  id="approval-notes"
                                  class="form-control" 
                                  rows="4" 
                                  placeholder="Add any notes or comments..."
                                  data-bs-toggle="tooltip"
                                  title="Optional notes for this approval"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check me-2"></i>Approve
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bootstrap Decline Modal -->
<div class="modal fade" id="declineModal" tabindex="-1" aria-labelledby="declineModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="declineModalLabel">
                    <i class="fas fa-times-circle me-2"></i>Decline Request
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="declineForm" method="POST" action="{{ route('loan-requests.inter-department.decline', $interDepartmentLoan) }}">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Warning:</strong> This action cannot be undone. Please provide a clear reason for declining.
                    </div>
                    <div class="mb-3">
                        <label for="decline-reason" class="form-label fw-semibold">
                            <i class="fas fa-comment me-2 text-danger"></i>Reason for Declining <span class="text-danger">*</span>
                        </label>
                        <textarea name="decline_reason" 
                                  id="decline-reason"
                                  class="form-control" 
                                  rows="4" 
                                  placeholder="Please provide a clear reason for declining this request..." 
                                  data-bs-toggle="tooltip"
                                  title="Required: Explain why this request is being declined"
                                  required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-ban me-2"></i>Decline Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showApprovalForm(type) {
    const form = document.getElementById('approvalForm');
    
    let action = '';
    switch(type) {
        case 'dean':
            action = '{{ route("loan-requests.inter-department.dean-approve", $interDepartmentLoan) }}';
            break;
        case 'lending-dean':
            action = '{{ route("loan-requests.inter-department.lending-dean-approve", $interDepartmentLoan) }}';
            break;
        case 'admin':
            action = '{{ route("loan-requests.inter-department.admin-approve", $interDepartmentLoan) }}';
            break;
    }
    
    form.action = action;
    
    // Show Bootstrap modal
    const modal = new bootstrap.Modal(document.getElementById('approvalModal'));
    modal.show();
}

function showDeclineForm() {
    // Show Bootstrap modal
    const modal = new bootstrap.Modal(document.getElementById('declineModal'));
    modal.show();
}

// Initialize Bootstrap tooltips when document is ready
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
@endsection