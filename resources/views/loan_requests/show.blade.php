@extends('layouts.app')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/inter-department-loans.css') }}">
@endsection

@section('content')
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
                <i class="fas fa-file-alt text-primary me-2"></i>
                Borrow Request Details
            </h1>
            <p class="text-muted mb-0">Request #{{ $loanRequest->id }} - {{ \Carbon\Carbon::parse($loanRequest->created_at)->format('M d, Y') }}</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            @php
                $status = $loanRequest->status ?? 'pending';
                $bi = $loanRequest->borrowedItem;
                $displayStatus = $status;
                if ($bi) {
                    if (is_null($bi->returned_at) && !is_null($bi->return_pending_at)) {
                        $displayStatus = 'return_pending';
                    } elseif (is_null($bi->returned_at)) {
                        $displayStatus = 'borrowed';
                    }
                }
                $statusBadgeClassMap = [
                    'approved' => 'badge bg-success',
                    'return_pending' => 'badge bg-warning',
                    'borrowed' => 'badge bg-primary',
                    'declined' => 'badge bg-danger',
                    'completed' => 'badge bg-info',
                    'pending' => 'badge bg-warning',
                ];
                $statusBadgeClass = $statusBadgeClassMap[$displayStatus] ?? 'badge bg-warning';
            @endphp
            <span class="{{ $statusBadgeClass }}">{{ ucfirst(str_replace('_', ' ', $displayStatus)) }}</span>
            <a href="#" onclick="window.history.back(); return false;" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <!-- Summary + Return Info -->
    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-light">
                    <h6 class="mb-0 fw-semibold">Request Summary</h6>
                </div>
                <div class="card-body">
                    <div class="row mb-2">
                        <div class="col-4 text-muted"><i class="fas fa-user me-2"></i>Requested By</div>
                        <div class="col-8">{{ $loanRequest->requestedBy?->name ?? ($loanRequest->user?->name ?? 'N/A') }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4 text-muted"><i class="fas fa-building me-2"></i>Department</div>
                        <div class="col-8">{{ $loanRequest->department?->department_name ?? $loanRequest->department?->name ?? 'N/A' }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4 text-muted"><i class="fas fa-boxes me-2"></i>Items</div>
                        <div class="col-8">
                            @php
                                $items = null;
                                if ($loanRequest->batch && $loanRequest->batch->loanRequests && $loanRequest->batch->loanRequests->count() > 0) {
                                    $items = $loanRequest->batch->loanRequests;
                                }
                            @endphp
                            <div class="table-responsive">
                                <table class="table table-sm table-striped align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-muted">Supply</th>
                                            <th class="text-muted">Variant</th>
                                            <th class="text-end text-muted">Quantity Requested</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if($items)
                                            @foreach($items as $item)
                                                <tr>
                                                    <td>{{ $item->supply?->name ?? 'N/A' }}</td>
                                                    <td>
                                                        @if($item->variant)
                                                            <span class="badge bg-info text-dark">{{ $item->variant->display_name ?? $item->variant->variant_name ?? ('#'.$item->variant->id) }}</span>
                                                        @elseif($item->supply?->hasVariants())
                                                            <span class="text-muted">—</span>
                                                        @else
                                                            <span class="text-muted">N/A</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-end">{{ $item->quantity_requested ?? '—' }}</td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td>{{ $loanRequest->supply?->name ?? 'N/A' }}</td>
                                                <td>
                                                    @if($loanRequest->variant)
                                                        <span class="badge bg-info text-dark">{{ $loanRequest->variant->display_name ?? $loanRequest->variant->variant_name ?? ('#'.$loanRequest->variant->id) }}</span>
                                                    @elseif($loanRequest->supply?->hasVariants())
                                                        <span class="text-muted">—</span>
                                                    @else
                                                        <span class="text-muted">N/A</span>
                                                    @endif
                                                </td>
                                                <td class="text-end">{{ $loanRequest->quantity_requested ?? '—' }}</td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4 text-muted"><i class="fas fa-clipboard-list me-2"></i>Purpose</div>
                        <div class="col-8">{{ $loanRequest->purpose ?? '—' }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4 text-muted"><i class="fas fa-calendar-plus me-2"></i>Starting Date</div>
                        <div class="col-8">{{ $loanRequest->needed_from_date ? (is_string($loanRequest->needed_from_date) ? \Carbon\Carbon::parse($loanRequest->needed_from_date)->format('F j, Y') : $loanRequest->needed_from_date->format('F j, Y')) : 'N/A' }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4 text-muted"><i class="fas fa-calendar-check me-2"></i>Expected Return</div>
                        <div class="col-8">{{ $loanRequest->expected_return_date ? (is_string($loanRequest->expected_return_date) ? \Carbon\Carbon::parse($loanRequest->expected_return_date)->format('F j, Y') : $loanRequest->expected_return_date->format('F j, Y')) : 'N/A' }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4 text-muted"><i class="fas fa-user-check me-2"></i>Approved By</div>
                        <div class="col-8">
                            @if($loanRequest->approvedBy)
                                {{ $loanRequest->approvedBy->name }}
                            @elseif($loanRequest->deanApprovedBy)
                                {{ $loanRequest->deanApprovedBy->name }} <span class="badge bg-info ms-2">Dean</span>
                            @else
                                <span class="text-muted">Not approved</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-light">
                    <h6 class="mb-0 fw-semibold">Return Information</h6>
                </div>
                <div class="card-body">
                    @php
                        // Build batch-aware items list
                        $itemsForSummary = null;
                        if ($loanRequest->batch && $loanRequest->batch->loanRequests && $loanRequest->batch->loanRequests->count() > 0) {
                            $itemsForSummary = $loanRequest->batch->loanRequests()->with(['supply','variant','borrowedItem'])->get();
                        } else {
                            $itemsForSummary = collect([$loanRequest->loadMissing(['supply','variant','borrowedItem'])]);
                        }

                        // Collect borrowed item ids and details
                        $borrowedIds = [];
                        foreach ($itemsForSummary as $itm) {
                            if ($itm->borrowedItem) {
                                $borrowedIds[] = $itm->borrowedItem->id;
                            } elseif (!empty($itm->borrowed_item_id)) {
                                $borrowedIds[] = $itm->borrowed_item_id;
                            }
                        }
                        $borrowedIds = array_values(array_unique(array_filter($borrowedIds)));

                        $borrowedItemsList = collect();
                        if (!empty($borrowedIds)) {
                            $borrowedItemsList = \App\Models\BorrowedItem::with(['supply'])->whereIn('id', $borrowedIds)->get();
                        }

                        // Aggregate summary data
                        $earliestBorrowed = null;
                        $latestVerifiedReturn = null;
                        $statusLabel = 'Not borrowed';
                        $anyPending = false; $anyBorrowed = false; $allReturned = true;
                        $totalBorrowedQty = 0; $totalVerifiedReturned = 0;

                        foreach ($borrowedItemsList as $bi) {
                            $totalBorrowedQty += (int) ($bi->quantity ?? 0);
                            if ($bi->borrowed_at ?? $bi->borrowed_on ?? null) {
                                $dt = \Carbon\Carbon::parse($bi->borrowed_at ?? $bi->borrowed_on);
                                $earliestBorrowed = $earliestBorrowed ? $earliestBorrowed->min($dt) : $dt;
                            }
                            if (is_null($bi->returned_at)) {
                                $allReturned = false;
                                if (!is_null($bi->return_pending_at)) { $anyPending = true; }
                                else { $anyBorrowed = true; }
                            }
                        }

                        // Combined return logs (pending + verified)
                        $returnLogs = collect();
                        if (!empty($borrowedIds)) {
                            $returnLogs = \App\Models\BorrowedItemLog::with(['user','borrowedItem.supply'])
                                ->whereIn('borrowed_item_id', $borrowedIds)
                                ->orderBy('created_at','desc')
                                ->get();
                            $totalVerifiedReturned = $returnLogs->where('action','verified_return')->sum(function($log){ return (int) ($log->quantity ?? 0); });
                            $lastVerified = $returnLogs->where('action','verified_return')->first();
                            if ($lastVerified) { $latestVerifiedReturn = \Carbon\Carbon::parse($lastVerified->created_at); }
                        }

                        if ($anyPending) { $statusLabel = 'Return Pending Verification'; }
                        elseif ($anyBorrowed) { $statusLabel = 'Borrowed'; }
                        elseif ($allReturned && !empty($borrowedIds)) { $statusLabel = 'Returned'; }
                    @endphp

                    <div class="row mb-2">
                        <div class="col-4 text-muted"><i class="fas fa-calendar-day me-2"></i>Borrowed Date</div>
                        <div class="col-8">
                            @if($earliestBorrowed)
                                {{ $earliestBorrowed->format('F j, Y') }}
                            @else
                                <span class="text-muted">Not yet borrowed</span>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4 text-muted"><i class="fas fa-undo me-2"></i>Returned Date</div>
                        <div class="col-8">
                            @if($latestVerifiedReturn)
                                {{ $latestVerifiedReturn->format('F j, Y') }}
                            @else
                                <span class="text-muted">Not returned</span>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4 text-muted"><i class="fas fa-hourglass-half me-2"></i>Due Date</div>
                        <div class="col-8">
                            @if($loanRequest->expected_return_date)
                                {{ $loanRequest->expected_return_date->format('F j, Y') }}
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4 text-muted"><i class="fas fa-info-circle me-2"></i>Status</div>
                        <div class="col-8">
                            @if(!empty($borrowedIds))
                                @if($statusLabel === 'Returned')
                                    <span class="badge bg-success">Returned</span>
                                @elseif($statusLabel === 'Return Pending Verification')
                                    <span class="badge bg-warning">Return Pending Verification</span>
                                @elseif($statusLabel === 'Borrowed')
                                    <span class="badge bg-primary">Borrowed</span>
                                @else
                                    <span class="text-muted">Not borrowed</span>
                                @endif
                            @else
                                <span class="text-muted">Not borrowed</span>
                            @endif
                        </div>
                    </div>

                    <hr class="my-3" />
                    <h6 class="fw-semibold mb-2"><i class="fas fa-box-open me-2"></i>Returned Items</h6>
                    @php
                        $logsToDisplay = $returnLogs->whereIn('action', ['return_pending', 'verified_return']);
                        $hasAnyMissing = $logsToDisplay->contains(function($log){
                            $notesLower = strtolower($log->notes ?? '');
                            return $notesLower && (\Illuminate\Support\Str::contains($notesLower, ['missing', 'lost', 'not returned']));
                        });
                        $hasAnyDamaged = $logsToDisplay->contains(function($log){
                            $notesLower = strtolower($log->notes ?? '');
                            return $notesLower && (\Illuminate\Support\Str::contains($notesLower, ['damaged', 'broken', 'defect', 'crack']));
                        });
                    @endphp
                    @if($logsToDisplay->count() > 0)
                        <div class="mb-2 text-muted">
                            Total borrowed: <span class="fw-semibold">{{ $totalBorrowedQty }}</span>
                            &middot; Verified returned: <span class="fw-semibold">{{ $totalVerifiedReturned }}</span>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm table-striped align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-muted" style="width: 160px;">Date</th>
                                        <th class="text-muted">Item</th>
                                        <th class="text-muted" style="width: 160px;">Status</th>
                                        <th class="text-end text-muted" style="width: 140px;">Quantity</th>
                                        @if($hasAnyMissing)
                                            <th class="text-muted" style="width: 160px;">Missing</th>
                                        @endif
                                        @if($hasAnyDamaged)
                                            <th class="text-muted" style="width: 160px;">Damaged</th>
                                        @endif
                                        <th class="text-muted">Notes</th>
                                        <th class="text-muted" style="width: 120px;">Photo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($logsToDisplay as $log)
                                        @php
                                            $notesLower = strtolower($log->notes ?? '');
                                            $missingFlag = $notesLower && (\Illuminate\Support\Str::contains($notesLower, ['missing', 'lost', 'not returned']));
                                            $damagedFlag = $notesLower && (\Illuminate\Support\Str::contains($notesLower, ['damaged', 'broken', 'defect', 'crack']));
                                            $isVerified = $log->action === 'verified_return';
                                            $itemName = optional(optional($log->borrowedItem)->supply)->name ?? 'N/A';
                                        @endphp
                                        <tr>
                                            <td>{{ \Carbon\Carbon::parse($log->created_at)->format('M d, Y h:i A') }}</td>
                                            <td>{{ $itemName }}</td>
                                            <td>
                                                @if($isVerified)
                                                    <span class="badge bg-success">Verified Return</span>
                                                @else
                                                    <span class="badge bg-warning">Return Pending</span>
                                                @endif
                                            </td>
                                            <td class="text-end">{{ $log->quantity ?? '—' }}</td>
                                            @if($hasAnyMissing)
                                                <td>
                                                    @if($missingFlag)
                                                        <span class="badge bg-danger">Missing Reported</span>
                                                    @endif
                                                </td>
                                            @endif
                                            @if($hasAnyDamaged)
                                                <td>
                                                    @if($damagedFlag)
                                                        <span class="badge bg-danger">Damaged Reported</span>
                                                    @endif
                                                </td>
                                            @endif
                                            <td>{{ $log->notes ?? '—' }}</td>
                                            <td>
                                                @if($log->photo_path)
                                                    <a href="#" class="link-primary"
                                                       data-bs-toggle="modal"
                                                       data-bs-target="#returnPhotoModal"
                                                       data-photo-url="{{ asset('storage/'.$log->photo_path) }}"
                                                       data-photo-title="Return Photo — {{ $itemName }} — {{ \Carbon\Carbon::parse($log->created_at)->format('M d, Y h:i A') }}">
                                                        View
                                                    </a>
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info mb-0">No return records yet.</div>
                    @endif

                </div>
            </div>
        </div>
    </div>

    <!-- All Request Items -->
    @php
        $allItems = null;
        if ($loanRequest->batch && $loanRequest->batch->loanRequests && $loanRequest->batch->loanRequests->count() > 0) {
            $allItems = $loanRequest->batch->loanRequests()->with(['supply','variant','borrowedItem','approvedBy'])->get();
        } else {
            $allItems = collect([$loanRequest->loadMissing(['supply','variant','borrowedItem','approvedBy'])]);
        }
        $statusBadgeClassMap = [
            'approved' => 'badge bg-success',
            'return_pending' => 'badge bg-warning',
            'borrowed' => 'badge bg-primary',
            'declined' => 'badge bg-danger',
            'completed' => 'badge bg-info',
            'pending' => 'badge bg-secondary',
        ];
    @endphp
    @if($allItems && $allItems->count() > 0)
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-header bg-light">
                <h6 class="mb-0 fw-semibold">Items in this Request</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-striped align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-muted">Supply</th>
                                <th class="text-muted">Variant</th>
                                <th class="text-end text-muted" style="width: 160px;">Quantity Requested</th>
                                <th class="text-muted" style="width: 160px;">Status</th>
                                <th class="text-muted">Details / Reason</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($allItems as $item)
                                @php
                                    $bi = $item->borrowedItem;
                                    $displayStatus = $item->status ?? 'pending';
                                    if ($bi) {
                                        if (is_null($bi->returned_at) && !is_null($bi->return_pending_at)) {
                                            $displayStatus = 'return_pending';
                                        } elseif (is_null($bi->returned_at)) {
                                            $displayStatus = 'borrowed';
                                        } elseif (!is_null($bi->returned_at)) {
                                            $displayStatus = 'completed';
                                        }
                                    }
                                    $badgeClass = $statusBadgeClassMap[$displayStatus] ?? 'badge bg-secondary';
                                @endphp
                                <tr>
                                    <td>{{ $item->supply?->name ?? 'N/A' }}</td>
                                    <td>
                                        @if($item->variant)
                                            <span class="badge bg-info text-dark">{{ $item->variant->display_name ?? $item->variant->variant_name ?? ('#'.$item->variant->id) }}</span>
                                        @elseif($item->supply?->hasVariants())
                                            <span class="text-muted">—</span>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td class="text-end">{{ $item->quantity_requested ?? '—' }}</td>
                                    <td>
                                        <span class="{{ $badgeClass }}">{{ ucfirst(str_replace('_', ' ', $displayStatus)) }}</span>
                                    </td>
                                    <td>
                                        @if($displayStatus === 'declined')
                                            <span class="text-danger">{{ $item->decline_reason ?? 'Declined' }}</span>
                                        @elseif($displayStatus === 'approved')
                                            @if($item->approvedBy)
                                                <span class="text-muted">Approved by {{ $item->approvedBy->name }}{{ $item->approved_at ? ' on ' . \Carbon\Carbon::parse($item->approved_at)->format('M d, Y') : '' }}</span>
                                            @elseif(!empty($item->approval_notes))
                                                <span class="text-muted">{{ $item->approval_notes }}</span>
                                            @else
                                                <span class="text-muted">Approved</span>
                                            @endif
                                        @elseif($displayStatus === 'borrowed' && $bi)
                                            <span class="text-muted">Borrowed {{ $bi->quantity ?? '—' }}{{ ($bi->borrowed_at ?? $bi->borrowed_on) ? ' on ' . \Carbon\Carbon::parse($bi->borrowed_at ?? $bi->borrowed_on)->format('M d, Y') : '' }}</span>
                                        @elseif($displayStatus === 'return_pending' && $bi)
                                            <span class="text-muted">Return pending since {{ $bi->return_pending_at ? \Carbon\Carbon::parse($bi->return_pending_at)->format('M d, Y') : '—' }}</span>
                                        @elseif($displayStatus === 'completed' && $bi)
                                            <span class="text-muted">Returned on {{ $bi->returned_at ? \Carbon\Carbon::parse($bi->returned_at)->format('M d, Y') : '—' }}</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <!-- Unified Actions -->
    <div class="card border-0 shadow-sm mt-4">
        <div class="card-header bg-light">
            <h6 class="mb-0 fw-semibold"><i class="fas fa-tasks me-2"></i>Actions</h6>
        </div>
        <div class="card-body">
            @php
                $canBeApproved = method_exists($loanRequest, 'canBeApproved') ? $loanRequest->canBeApproved() : false;
                $borrowedItem = $loanRequest->borrowedItem ?? null;
                // Compute active returnable items count across the batch (or single item)
                $activeReturnableCount = 0;
                if ($loanRequest->batch && $loanRequest->batch->loanRequests) {
                    $borrowedIds = $loanRequest->batch->loanRequests->pluck('borrowed_item_id')->filter()->all();
                    if (!empty($borrowedIds)) {
                        $activeReturnableCount = \App\Models\BorrowedItem::whereIn('id', $borrowedIds)
                            ->whereNull('returned_at')
                            ->whereNull('return_pending_at')
                            ->count();
                    }
                } else {
                    if ($borrowedItem && is_null($borrowedItem->returned_at) && is_null($borrowedItem->return_pending_at)) {
                        $activeReturnableCount = 1;
                    }
                }
            @endphp

            {{-- Borrower: Submit Return --}}
            @if(auth()->check() && auth()->id() === ($borrowedItem->user_id ?? $loanRequest->requested_by))
                @if($activeReturnableCount > 1)
                <div class="mb-4">
                    <h6 class="fw-semibold mb-2"><i class="fas fa-undo-alt me-2"></i>Submit Bulk Return for Verification</h6>
                    <div class="alert alert-info mb-3">Multiple items in this request are eligible for return. Use the bulk return form to submit all at once.</div>
                    <a href="{{ route('loan-requests.return-form', $loanRequest) }}" class="btn btn-primary">
                        <i class="fas fa-layer-group me-2"></i>Return Multiple Items
                    </a>
                </div>
                <hr class="my-3" />
                @elseif($borrowedItem && is_null($borrowedItem->returned_at) && is_null($borrowedItem->return_pending_at))
                <div class="mb-4">
                    <h6 class="fw-semibold mb-2"><i class="fas fa-undo-alt me-2"></i>Submit Return for Verification</h6>
                    <form action="{{ route('borrowed-items.return', $borrowedItem) }}" method="POST" enctype="multipart/form-data" onsubmit="return confirm('Submit return for admin verification?');">
                        @csrf
                        @method('PATCH')
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label"><i class="fas fa-sticky-note me-2"></i>Return Note (optional)</label>
                                <textarea name="note" rows="3" class="form-control" placeholder="Add any notes about the return"></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><i class="fas fa-camera me-2"></i>Return Photo (optional)</label>
                                <input type="file" name="photo" accept="image/*" class="form-control" />
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Submit for Verification</button>
                    </form>
                </div>
                <hr class="my-3" />
                @endif
            @endif

            {{-- Admin: Verify Return (single item) --}}
            @if($borrowedItem && !$borrowedItem->returned_at && !is_null($borrowedItem->return_pending_at) && auth()->user() && auth()->user()->hasAdminPrivileges())
                <div class="mb-4">
                    <h6 class="fw-semibold mb-2"><i class="fas fa-clipboard-check me-2"></i>Verify Return</h6>
                    <div class="alert alert-warning mb-2">Return is pending admin verification.</div>
                    <form method="POST" action="{{ route('borrowed-items.verify-return', $borrowedItem) }}" onsubmit="return confirm('Verify this return and mark item as returned?');">
                        @csrf
                        @method('PATCH')
                        <div class="mb-3">
                            <label class="form-label"><i class="fas fa-comment-dots me-2"></i>Verification Notes (optional)</label>
                            <textarea name="verification_notes" rows="2" class="form-control" placeholder="Notes for this verification"></textarea>
                        </div>
                        <button type="submit" class="btn btn-success">Verify Return</button>
                    </form>
                </div>
                <hr class="my-3" />
            @endif

            {{-- Admin: Verify All Pending Returns (batch) --}}
            @if(auth()->check() && auth()->user()->hasAdminPrivileges())
                @php
                    $pendingReturnCount = 0;
                    if ($loanRequest->batch && $loanRequest->batch->loanRequests) {
                        $borrowedIds = $loanRequest->batch->loanRequests->pluck('borrowed_item_id')->filter()->all();
                        if (!empty($borrowedIds)) {
                            $pendingReturnCount = \App\Models\BorrowedItem::whereIn('id', $borrowedIds)
                                ->whereNull('returned_at')
                                ->whereNotNull('return_pending_at')
                                ->count();
                        }
                    } else {
                        if ($borrowedItem && is_null($borrowedItem->returned_at) && !is_null($borrowedItem->return_pending_at)) {
                            $pendingReturnCount = 1;
                        }
                    }
                @endphp
                @if($pendingReturnCount > 1)
                    <div class="mb-4">
                        <h6 class="fw-semibold mb-2"><i class="fas fa-clipboard-list me-2"></i>Verify All Pending Returns</h6>
                        <div class="alert alert-info mb-2">Multiple items in this batch are pending verification. Verify them all at once.</div>
                        <form method="POST" action="{{ route('loan-requests.verify-return', $loanRequest) }}" onsubmit="return confirm('Verify all pending returns and mark items as returned?');">
                            @csrf
                            @method('PATCH')
                            <div class="mb-3">
                                <label class="form-label"><i class="fas fa-comment-dots me-2"></i>Verification Notes (optional)</label>
                                <textarea name="verification_notes" rows="2" class="form-control" placeholder="Notes applied to all verifications"></textarea>
                            </div>
                            <button type="submit" class="btn btn-success"><i class="fas fa-check-double me-2"></i>Verify All Pending Returns</button>
                        </form>
                    </div>
                    <hr class="my-3" />
                @endif
            @endif

            {{-- Admin: Verify Selected Returns with Statuses --}}
            @if(auth()->check() && auth()->user()->hasAdminPrivileges())
                @php
                    $pendingItems = collect();
                    if ($loanRequest->batch && $loanRequest->batch->loanRequests) {
                        $borrowedIds = $loanRequest->batch->loanRequests->pluck('borrowed_item_id')->filter()->all();
                        if (!empty($borrowedIds)) {
                            $pendingItems = \App\Models\BorrowedItem::whereIn('id', $borrowedIds)
                                ->whereNull('returned_at')
                                ->whereNotNull('return_pending_at')
                                ->get();
                        }
                    } else {
                        if ($borrowedItem && is_null($borrowedItem->returned_at) && !is_null($borrowedItem->return_pending_at)) {
                            $pendingItems = collect([$borrowedItem]);
                        }
                    }
                @endphp
                @if($pendingItems->count() > 0)
                    <div class="mb-4">
                        <h6 class="fw-semibold mb-2"><i class="fas fa-tasks me-2"></i>Verify Selected Returns</h6>
                        <div class="alert alert-warning mb-2">Select which items to mark returned and specify missing/damaged status if applicable.</div>
                        <form method="POST" action="{{ route('loan-requests.verify-return-selected', $loanRequest) }}" onsubmit="return confirm('Verify selected returns with chosen statuses?');">
                            @csrf
                            @method('PATCH')
                            <div class="mb-3">
                                <label class="form-label"><i class="fas fa-comment-dots me-2"></i>Verification Notes (optional)</label>
                                <textarea name="verification_notes" rows="2" class="form-control" placeholder="Notes applied to selected verifications"></textarea>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 60px;">Select</th>
                                            <th>Item</th>
                                            <th class="text-center" style="width: 140px;">Borrowed Qty</th>
                                            <th style="width: 220px;">Returned Status</th>
                                            <th style="width: 160px;">Missing Count</th>
                                            <th style="width: 160px;">Damaged Count</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($pendingItems as $idx => $pi)
                                            <tr>
                                                <td class="text-center">
                                                    <input type="checkbox" class="form-check-input" name="items[{{ $idx }}][selected]" value="1">
                                                    <input type="hidden" name="items[{{ $idx }}][borrowed_item_id]" value="{{ $pi->id }}">
                                                </td>
                                                <td>
                                                    <div class="fw-semibold">{{ $pi->supply->name }}</div>
                                                    @php
                                                        $lr = $pi->loanRequest ?? null;
                                                    @endphp
                                                    @if($lr && $lr->variant)
                                                        <div class="small mt-1"><span class="badge bg-info text-dark">{{ $lr->variant->display_name ?? $lr->variant->variant_name ?? ('Variant #'.$lr->variant->id) }}</span></div>
                                                    @endif
                                                </td>
                                                <td class="text-center">{{ $pi->quantity }}</td>
                                                <td>
                                                    <select name="items[{{ $idx }}][status]" class="form-select">
                                                        <option value="returned">Returned</option>
                                                        <option value="returned_with_missing">Returned with Missing</option>
                                                        <option value="returned_with_damage">Returned with Damage</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <input type="number" name="items[{{ $idx }}][missing_count]" class="form-control" min="0" max="{{ $pi->quantity }}" value="0" />
                                                </td>
                                                <td>
                                                    <input type="number" name="items[{{ $idx }}][damaged_count]" class="form-control" min="0" max="{{ $pi->quantity }}" value="0" />
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <button type="submit" class="btn btn-success"><i class="fas fa-check me-2"></i>Verify Selected</button>
                        </form>
                    </div>
                    <hr class="my-3" />
                @endif
                @endif

            {{-- Dean: Approval --}}
            @if(auth()->check() && auth()->user()->hasRole('dean') && auth()->user()->isDeanOf($loanRequest->department) && $loanRequest->needsDeanApproval())
                <div class="mb-4">
                    <h6 class="fw-semibold mb-2"><i class="fas fa-user-tie me-2"></i>Dean Approval</h6>
                    <form method="POST" action="{{ route('loan-requests.dean-approve', $loanRequest) }}" onsubmit="return confirm('Approve this borrow request as dean?');">
                        @csrf
                        @method('PATCH')
                        <div class="mb-3">
                            <label class="form-label"><i class="fas fa-comment-alt me-2"></i>Approval Notes (optional)</label>
                            <textarea name="dean_approval_notes" rows="3" class="form-control" placeholder="Add notes for this approval"></textarea>
                        </div>
                        <button type="submit" class="btn btn-success">Approve</button>
                    </form>
                </div>
                <hr class="my-3" />
            @endif

            {{-- Admin: Approvals --}}
            @if(auth()->check() && auth()->user()->hasAdminPrivileges())
                <div class="mb-4">
                    <h6 class="fw-semibold mb-2"><i class="fas fa-user-shield me-2"></i>Admin Actions</h6>
                    {{-- Only show the consolidated batch approval UI for admins --}}

                    @if($loanRequest->batch && $loanRequest->batch->loanRequests && $loanRequest->batch->loanRequests->count() > 0)
                        <div class="border rounded p-3 mt-3">
                            <h6 class="fw-semibold mb-2"><i class="fas fa-list-check me-2"></i>Approve Selected & Decline Others</h6>
                            @php
                                $pendingBatchItems = $loanRequest->batch->items()->with('supply')->where('status', 'pending')->get();
                            @endphp
                            @if($pendingBatchItems->count() > 0)
                                <form method="POST" action="{{ route('loan-request-batches.approve-selected', $loanRequest->batch) }}" onsubmit="return confirmApproveSelected(event)">
                                    @csrf
                                    <div class="table-responsive">
                                        <table class="table table-sm align-middle">
                                            <thead class="table-light">
                                                <tr>
                                                    <th class="text-muted">Supply</th>
                                                    <th class="text-end text-muted">Quantity Requested</th>
                                                    <th class="text-center">
                                                        <div class="form-check d-inline-flex align-items-center">
                                                            <input type="checkbox" class="form-check-input" id="selectAllPending" />
                                                            <label class="form-check-label ms-2" for="selectAllPending">Select All</label>
                                                        </div>
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody id="approveDeclineTbody">
                                                @foreach($pendingBatchItems as $item)
                                                    <tr class="table-warning" data-item-row>
                                                        <td>{{ $item->supply?->name ?? 'N/A' }}</td>
                                                        <td class="text-end">{{ $item->quantity_requested ?? '—' }}</td>
                                                        <td class="text-center">
                                                            <input type="checkbox" name="selected[]" value="{{ $item->id }}" class="form-check-input approve-checkbox" />
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label"><i class="fas fa-comment-alt me-2"></i>Approval Notes (optional)</label>
                                            <textarea name="approval_notes" rows="2" class="form-control" placeholder="Notes for approved items"></textarea>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label"><i class="fas fa-comment-slash me-2"></i>Decline Reason (optional)</label>
                                            <textarea name="decline_reason" rows="2" class="form-control" placeholder="Reason applied to the declined items"></textarea>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between mt-3">
                                        <div class="text-muted" id="selectionSummary">Selected 0 to approve; 0 to decline.</div>
                                        <button type="submit" class="btn btn-primary"><i class="fas fa-check-double me-2"></i>Approve Selected & Decline Others</button>
                                    </div>
                                </form>
                            @else
                                <div class="alert alert-info mb-0">No pending items in this batch to process.</div>
                            @endif
                        </div>
                    @endif

                    @if($loanRequest->isPending())
                        <div class="mt-3">
                            <h6 class="fw-semibold mb-2"><i class="fas fa-ban me-2"></i>Decline Request</h6>
                            <form method="POST" action="{{ route('loan-requests.decline', $loanRequest) }}" onsubmit="return confirm('Decline this borrow request?');">
                                @csrf
                                @method('PATCH')
                                <div class="mb-3">
                                    <label class="form-label"><i class="fas fa-comment-slash me-2"></i>Decline Reason</label>
                                    <textarea name="decline_reason" rows="3" class="form-control" placeholder="Explain why this request is being declined" required minlength="10" maxlength="1000"></textarea>
                                    @error('decline_reason')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                <button type="submit" class="btn btn-danger">Decline</button>
                            </form>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

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
                <div id="returnPhotoContainer" class="text-center">
                    <img id="returnPhotoImage" src="" alt="Return Photo" class="img-fluid rounded border" style="max-height: 70vh; transform-origin: center center;" />
                </div>
            </div>
            <div class="modal-footer d-flex justify-content-between">
                <div class="btn-group" role="group" aria-label="Image controls">
                    <button type="button" class="btn btn-outline-secondary" id="zoomOutBtn" title="Zoom out"><i class="fas fa-search-minus"></i></button>
                    <button type="button" class="btn btn-outline-secondary" id="zoomInBtn" title="Zoom in"><i class="fas fa-search-plus"></i></button>
                    <button type="button" class="btn btn-outline-secondary" id="rotateBtn" title="Rotate"><i class="fas fa-redo-alt"></i></button>
                    <button type="button" class="btn btn-outline-secondary" id="resetBtn" title="Reset"><i class="fas fa-undo"></i></button>
                </div>
                <div>
                    <a class="btn btn-primary" id="openNewTabBtn" target="_blank"><i class="fas fa-external-link-alt me-2"></i>Open full image</a>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var modalEl = document.getElementById('returnPhotoModal');
            var imgEl = document.getElementById('returnPhotoImage');
            var titleEl = document.getElementById('returnPhotoModalLabel');
            var openBtn = document.getElementById('openNewTabBtn');
            var zoomInBtn = document.getElementById('zoomInBtn');
            var zoomOutBtn = document.getElementById('zoomOutBtn');
            var rotateBtn = document.getElementById('rotateBtn');
            var resetBtn = document.getElementById('resetBtn');

            if (!modalEl || !imgEl) return;

            var scale = 1;
            var rotate = 0;

            function applyTransform() {
                imgEl.style.transform = 'scale(' + scale + ') rotate(' + rotate + 'deg)';
            }

            function resetTransform() {
                scale = 1;
                rotate = 0;
                applyTransform();
            }

            modalEl.addEventListener('show.bs.modal', function (event) {
                var link = event.relatedTarget;
                if (!link) return;
                var url = link.getAttribute('data-photo-url');
                var title = link.getAttribute('data-photo-title') || 'Return Photo';
                if (imgEl) imgEl.src = url || '';
                if (titleEl) titleEl.textContent = title;
                if (openBtn) openBtn.href = url || '#';
                resetTransform();
            });

            modalEl.addEventListener('hidden.bs.modal', function () {
                if (imgEl) imgEl.src = '';
                resetTransform();
            });

            if (zoomInBtn) zoomInBtn.addEventListener('click', function () {
                scale = Math.min(scale + 0.2, 5);
                applyTransform();
            });
            if (zoomOutBtn) zoomOutBtn.addEventListener('click', function () {
                scale = Math.max(scale - 0.2, 0.2);
                applyTransform();
            });
            if (rotateBtn) rotateBtn.addEventListener('click', function () {
                rotate = (rotate + 90) % 360;
                applyTransform();
            });
            if (resetBtn) resetBtn.addEventListener('click', function () {
                resetTransform();
            });
        });
    </script>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tbody = document.getElementById('approveDeclineTbody');
        const selectAll = document.getElementById('selectAllPending');
        const summary = document.getElementById('selectionSummary');

        if (!tbody) return;

        function updateRowState(row, checked) {
            row.classList.toggle('table-success', checked);
            row.classList.toggle('table-warning', !checked);
        }

        function updateSummary() {
            const checkboxes = tbody.querySelectorAll('.approve-checkbox');
            let selected = 0;
            checkboxes.forEach(cb => { if (cb.checked) selected++; });
            const total = checkboxes.length;
            const declined = total - selected;
            if (summary) summary.textContent = `Selected ${selected} to approve; ${declined} to decline.`;
        }

        tbody.querySelectorAll('.approve-checkbox').forEach(cb => {
            const row = cb.closest('tr');
            updateRowState(row, cb.checked);
            cb.addEventListener('change', function() {
                updateRowState(row, cb.checked);
                updateSummary();
            });
        });

        if (selectAll) {
            selectAll.addEventListener('change', function() {
                tbody.querySelectorAll('.approve-checkbox').forEach(cb => {
                    cb.checked = selectAll.checked;
                    updateRowState(cb.closest('tr'), cb.checked);
                });
                updateSummary();
            });
        }

        updateSummary();
    });

    function confirmApproveSelected(e) {
        const tbody = document.getElementById('approveDeclineTbody');
        if (!tbody) return true;
        const checkboxes = tbody.querySelectorAll('.approve-checkbox');
        let selected = 0;
        checkboxes.forEach(cb => { if (cb.checked) selected++; });
        const total = checkboxes.length;
        const declined = total - selected;
        const msg = declined > 0 
            ? `Approve ${selected} selected item(s) and decline ${declined} unselected item(s)?`
            : `Approve ${selected} selected item(s)?`;
        return confirm(msg);
    }
</script>
@endpush
