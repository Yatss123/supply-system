@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Edit Manual Receipt</h4>
                    <div class="btn-group">
                        <a href="{{ route('manual-receipts.show', $manualReceipt) }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Details
                        </a>
                        <a href="{{ route('supplies.index', ['tab' => 'receipts']) }}" class="btn btn-outline-secondary">
                            <i class="fas fa-list"></i> All Receipts
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <form action="{{ route('manual-receipts.update', $manualReceipt) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="supply_id" class="form-label">Supply <span class="text-danger">*</span></label>
                                    <select name="supply_id" id="supply_id" class="form-select @error('supply_id') is-invalid @enderror" required>
                                        <option value="">Select a supply</option>
                                        @foreach($supplies as $supply)
                                            <option value="{{ $supply->id }}" 
                                                {{ (old('supply_id', $manualReceipt->supply_id) == $supply->id) ? 'selected' : '' }}>
                                                {{ $supply->name }} (Current: {{ $supply->hasVariants() ? $supply->getTotalVariantQuantity() : $supply->quantity }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('supply_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="quantity" class="form-label">Quantity Received <span class="text-danger">*</span></label>
                                    <input type="number" name="quantity" id="quantity" 
                                           class="form-control @error('quantity') is-invalid @enderror" 
                                           value="{{ old('quantity', $manualReceipt->quantity) }}" min="1" required>
                                    @error('quantity')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">
                                        <small class="text-warning">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            Changing quantity will adjust inventory accordingly.
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="supplier_id" class="form-label">Supplier</label>
                                    <select name="supplier_id" id="supplier_id" class="form-select @error('supplier_id') is-invalid @enderror">
                                        <option value="">Select a supplier (optional)</option>
                                        @foreach($suppliers as $supplier)
                                            <option value="{{ $supplier->id }}" 
                                                {{ (old('supplier_id', $manualReceipt->supplier_id) == $supplier->id) ? 'selected' : '' }}>
                                                {{ $supplier->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('supplier_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="receipt_date" class="form-label">Receipt Date <span class="text-danger">*</span></label>
                                    <input type="date" name="receipt_date" id="receipt_date" 
                                           class="form-control @error('receipt_date') is-invalid @enderror" 
                                           value="{{ old('receipt_date', $manualReceipt->receipt_date->format('Y-m-d')) }}" 
                                           max="{{ date('Y-m-d') }}" required>
                                    @error('receipt_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="reference_number" class="form-label">Reference Number</label>
                                    <input type="text" name="reference_number" id="reference_number" 
                                           class="form-control @error('reference_number') is-invalid @enderror" 
                                           value="{{ old('reference_number', $manualReceipt->reference_number) }}" 
                                           placeholder="Invoice #, PO #, etc.">
                                    @error('reference_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="cost_per_unit" class="form-label">Cost per Unit</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" name="cost_per_unit" id="cost_per_unit" 
                                               class="form-control @error('cost_per_unit') is-invalid @enderror" 
                                               value="{{ old('cost_per_unit', $manualReceipt->cost_per_unit) }}" 
                                               step="0.01" min="0" placeholder="0.00">
                                    </div>
                                    @error('cost_per_unit')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                    <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                                        <option value="pending" {{ old('status', $manualReceipt->status) === 'pending' ? 'selected' : '' }}>
                                            Pending
                                        </option>
                                        <option value="verified" {{ old('status', $manualReceipt->status) === 'verified' ? 'selected' : '' }}>
                                            Verified
                                        </option>
                                        <option value="needs_review" {{ old('status', $manualReceipt->status) === 'needs_review' ? 'selected' : '' }}>
                                            Needs Review
                                        </option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea name="notes" id="notes" rows="3" 
                                      class="form-control @error('notes') is-invalid @enderror" 
                                      placeholder="Additional notes about this receipt...">{{ old('notes', $manualReceipt->notes) }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Important:</strong> 
                            <ul class="mb-0 mt-2">
                                <li>Changing the quantity will automatically adjust the supply inventory.</li>
                                <li>If you decrease the quantity, make sure there's enough inventory to support the change.</li>
                                <li>Changes to this receipt may affect fulfilled order requests.</li>
                            </ul>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('manual-receipts.show', $manualReceipt) }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Receipt
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Receipt History Card -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Receipt Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Originally Added:</strong> {{ $manualReceipt->created_at->format('F d, Y g:i A') }}</p>
                            <p><strong>Added By:</strong> {{ $manualReceipt->addedBy->name }}</p>
                        </div>
                        <div class="col-md-6">
                            @if($manualReceipt->updated_at != $manualReceipt->created_at)
                                <p><strong>Last Modified:</strong> {{ $manualReceipt->updated_at->format('F d, Y g:i A') }}</p>
                            @endif
                            <p><strong>Current Status:</strong> 
                                <span class="badge 
                                    @if($manualReceipt->status === 'verified') bg-success
                                    @elseif($manualReceipt->status === 'needs_review') bg-warning
                                    @else bg-secondary
                                    @endif">
                                    {{ ucfirst(str_replace('_', ' ', $manualReceipt->status)) }}
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-calculate total cost when quantity or cost per unit changes
    const quantityInput = document.getElementById('quantity');
    const costPerUnitInput = document.getElementById('cost_per_unit');
    
    function updateTotalCost() {
        const quantity = parseFloat(quantityInput.value) || 0;
        const costPerUnit = parseFloat(costPerUnitInput.value) || 0;
        const totalCost = quantity * costPerUnit;
        
        // You can add a total cost display here if needed
        console.log('Total Cost:', totalCost.toFixed(2));
    }
    
    quantityInput.addEventListener('input', updateTotalCost);
    costPerUnitInput.addEventListener('input', updateTotalCost);
});
</script>
@endsection