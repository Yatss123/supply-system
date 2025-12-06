@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Manual Receipts</h4>
                    <a href="{{ route('manual-receipts.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Manual Receipt
                    </a>
                </div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Search and Filter Form -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <form method="GET" action="{{ route('supplies.index', ['tab' => 'receipts']) }}" class="row g-3">
                                <div class="col-md-4">
                                    <label for="search" class="form-label">Search</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="search" 
                                           name="search" 
                                           value="{{ request('search') }}"
                                           placeholder="Search by supply, supplier, or reference number...">
                                </div>
                                <div class="col-md-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="all" {{ request('status') === 'all' ? 'selected' : '' }}>All Statuses</option>
                                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="verified" {{ request('status') === 'verified' ? 'selected' : '' }}>Verified</option>
                                        <option value="needs_review" {{ request('status') === 'needs_review' ? 'selected' : '' }}>Needs Review</option>
                                        <option value="issued" {{ request('status') === 'issued' ? 'selected' : '' }}>Issued</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label for="date_from" class="form-label">From Date</label>
                                    <input type="date" 
                                           class="form-control" 
                                           id="date_from" 
                                           name="date_from" 
                                           value="{{ request('date_from') }}">
                                </div>
                                <div class="col-md-2">
                                    <label for="date_to" class="form-label">To Date</label>
                                    <input type="date" 
                                           class="form-control" 
                                           id="date_to" 
                                           name="date_to" 
                                           value="{{ request('date_to') }}">
                                </div>
                                <div class="col-md-1 d-flex align-items-end">
                                    <div class="btn-group" role="group">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-search"></i>
                                        </button>
                                        <a href="{{ route('supplies.index', ['tab' => 'receipts']) }}" class="btn btn-outline-secondary">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    @if($receipts->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Receipt Date</th>
                                        <th>Supply</th>
                                        <th>Quantity</th>
                                        <th>Supplier</th>
                                        <th>Reference #</th>
                                        <th>Cost/Unit</th>
                                        <th>Status</th>
                                        <th>Added By</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($receipts as $receipt)
                                        <tr>
                                            <td>{{ $receipt->receipt_date->format('M d, Y') }}</td>
                                            <td>{{ $receipt->supply->name }}</td>
                                            <td>{{ number_format($receipt->quantity) }}</td>
                                            <td>{{ $receipt->supplier->name ?? 'N/A' }}</td>
                                            <td>{{ $receipt->reference_number ?? 'N/A' }}</td>
                                            <td>
                                                @if($receipt->cost_per_unit)
                                                    ${{ number_format($receipt->cost_per_unit, 2) }}
                                                @else
                                                    N/A
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge 
                                                    @if($receipt->status === 'verified') bg-success
                                                    @elseif($receipt->status === 'needs_review') bg-warning
                                                    @else bg-secondary
                                                    @endif">
                                                    {{ ucfirst(str_replace('_', ' ', $receipt->status)) }}
                                                </span>
                                            </td>
                                            <td>{{ $receipt->addedBy->name }}</td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('manual-receipts.show', $receipt) }}" 
                                                       class="btn btn-sm btn-outline-info">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('manual-receipts.edit', $receipt) }}" 
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('manual-receipts.destroy', $receipt) }}" 
                                                          method="POST" class="d-inline"
                                                          onsubmit="return confirm('Are you sure you want to delete this receipt? This will adjust the inventory.')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-center">
                            {{ $receipts->links() }}
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No manual receipts found</h5>
                            <p class="text-muted">Start by adding your first manual receipt.</p>
                            <a href="{{ route('manual-receipts.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Add Manual Receipt
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection