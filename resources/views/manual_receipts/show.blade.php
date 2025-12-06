@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Manual Receipt Details</h4>
                    <div class="btn-group">
                        <a href="{{ route('supplies.index', ['tab' => 'receipts']) }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                        <a href="{{ route('manual-receipts.edit', $manualReceipt) }}" class="btn btn-primary">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5 class="text-muted mb-3">Receipt Information</h5>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Receipt Date:</label>
                                <p class="mb-0">{{ $manualReceipt->receipt_date->format('F d, Y') }}</p>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Reference Number:</label>
                                <p class="mb-0">{{ $manualReceipt->reference_number ?? 'N/A' }}</p>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Status:</label>
                                <p class="mb-0">
                                    <span class="badge 
                                        @if($manualReceipt->status === 'verified') bg-success
                                        @elseif($manualReceipt->status === 'needs_review') bg-warning
                                        @else bg-secondary
                                        @endif">
                                        {{ ucfirst(str_replace('_', ' ', $manualReceipt->status)) }}
                                    </span>
                                </p>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Added By:</label>
                                <p class="mb-0">{{ $manualReceipt->addedBy->name }}</p>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Created:</label>
                                <p class="mb-0">{{ $manualReceipt->created_at->format('F d, Y g:i A') }}</p>
                            </div>

                            @if($manualReceipt->updated_at != $manualReceipt->created_at)
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Last Updated:</label>
                                    <p class="mb-0">{{ $manualReceipt->updated_at->format('F d, Y g:i A') }}</p>
                                </div>
                            @endif
                        </div>

                        <div class="col-md-6">
                            <h5 class="text-muted mb-3">Supply & Quantity</h5>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Supply:</label>
                                <p class="mb-0">
                                    <a href="{{ route('supplies.show', $manualReceipt->supply) }}" class="text-decoration-none">
                                        {{ $manualReceipt->supply->name }}
                                    </a>
                                </p>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Quantity Received:</label>
                                <p class="mb-0 fs-4 text-primary fw-bold">{{ number_format($manualReceipt->quantity) }}</p>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Supplier:</label>
                                <p class="mb-0">
                                    @if($manualReceipt->supplier)
                                        {{ $manualReceipt->supplier->name }}
                                    @else
                                        <span class="text-muted">Not specified</span>
                                    @endif
                                </p>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Cost per Unit:</label>
                                <p class="mb-0">
                                    @if($manualReceipt->cost_per_unit)
                                        ${{ number_format($manualReceipt->cost_per_unit, 2) }}
                                    @else
                                        <span class="text-muted">Not specified</span>
                                    @endif
                                </p>
                            </div>

                            @if($manualReceipt->cost_per_unit)
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Total Cost:</label>
                                    <p class="mb-0 fs-5 text-success fw-bold">
                                        ${{ number_format($manualReceipt->quantity * $manualReceipt->cost_per_unit, 2) }}
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>

                    @if($manualReceipt->notes)
                        <hr>
                        <div class="mb-3">
                            <h5 class="text-muted mb-3">Notes</h5>
                            <div class="bg-light p-3 rounded">
                                {{ $manualReceipt->notes }}
                            </div>
                        </div>
                    @endif

                    <hr>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <form action="{{ route('manual-receipts.destroy', $manualReceipt) }}" 
                                  method="POST" class="d-inline"
                                  onsubmit="return confirm('Are you sure you want to delete this receipt? This will adjust the inventory and cannot be undone.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger">
                                    <i class="fas fa-trash"></i> Delete Receipt
                                </button>
                            </form>
                        </div>
                        
                        <div class="btn-group">
                            <a href="{{ route('manual-receipts.edit', $manualReceipt) }}" class="btn btn-primary">
                                <i class="fas fa-edit"></i> Edit Receipt
                            </a>
                            @if(auth()->user()->hasRole('student'))
                                <a href="{{ route('student.supplies.show', $manualReceipt->supply) }}" class="btn btn-outline-info">
                            @elseif(auth()->user()->hasRole('adviser'))
                                <a href="{{ route('adviser.supplies.show', $manualReceipt->supply) }}" class="btn btn-outline-info">
                            @elseif(auth()->user()->hasRole('dean'))
                                <a href="{{ route('dean.supplies.show', $manualReceipt->supply) }}" class="btn btn-outline-info">
                            @else
                                <a href="{{ route('supplies.show', $manualReceipt->supply) }}" class="btn btn-outline-info">
                            @endif
                                <i class="fas fa-box"></i> View Supply
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection