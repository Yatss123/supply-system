@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Edit Order Request</h4>
                    <a href="{{ route('restock-requests.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                </div>

                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('restock-requests.update', $restockRequest) }}">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="supply_id" class="form-label">Supply Item <span class="text-danger">*</span></label>
                                    <select class="form-select @error('supply_id') is-invalid @enderror" 
                                            id="supply_id" name="supply_id" required>
                                        <option value="">Select a supply item</option>
                                        @foreach($supplies as $supply)
                                            <option value="{{ $supply->id }}" 
                                                    data-current-stock="{{ $supply->hasVariants() ? $supply->getTotalVariantQuantity() : $supply->quantity }}"
                                                    {{ old('supply_id', $restockRequest->supply_id) == $supply->id ? 'selected' : '' }}>
                                                {{ $supply->name }} ({{ $supply->category->name ?? 'No Category' }})
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
                                    <label for="quantity" class="form-label">Quantity to Request <span class="text-danger">*</span></label>
                                    <input type="number" 
                                           class="form-control @error('quantity') is-invalid @enderror" 
                                           id="quantity" 
                                           name="quantity" 
                                           value="{{ old('quantity', $restockRequest->quantity) }}" 
                                           min="1" 
                                           required>
                                    @error('quantity')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Current Stock Information -->
                        <div class="alert alert-info" id="stock-info" style="display: none;">
                            <strong>Current Stock:</strong> <span id="current-stock">0</span> units
                        </div>

                        <!-- Current Request Information -->
                        <div class="card mb-3">
                            <div class="card-header">
                                <h6 class="mb-0">Current Request Details</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <strong>Status:</strong>
                                        <span class="badge 
                                            @if($restockRequest->status === 'pending') bg-warning
                                            @elseif($restockRequest->status === 'ordered') bg-info
                                            @elseif($restockRequest->status === 'delivered') bg-success
                                            @else bg-secondary
                                            @endif">
                                            {{ ucfirst($restockRequest->status) }}
                                        </span>
                                    </div>
                                    <div class="col-md-4">
                                        <strong>Current Supply:</strong> {{ $restockRequest->supply->name }}
                                    </div>
                                    <div class="col-md-4">
                                        <strong>Current Quantity:</strong> {{ $restockRequest->quantity }}
                                    </div>
                                </div>
                                @if($restockRequest->supplier)
                                    <div class="row mt-2">
                                        <div class="col-md-12">
                                            <strong>Supplier:</strong> {{ $restockRequest->supplier->name }}
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        @if($restockRequest->status === 'pending')
                            <div class="alert alert-warning">
                                <i class="fas fa-info-circle"></i>
                                <strong>Note:</strong> You can only edit order requests that are in "pending" status.
                            </div>
                        @else
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                <strong>Note:</strong> This request has status "{{ $restockRequest->status }}" and may have limited editing options.
                            </div>
                        @endif

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('restock-requests.show', $restockRequest) }}" class="btn btn-outline-primary">
                                <i class="fas fa-eye"></i> View Details
                            </a>
                            <div>
                                <a href="{{ route('restock-requests.index') }}" class="btn btn-secondary me-2">Cancel</a>
                                @if($restockRequest->status === 'pending')
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Update Request
                                    </button>
                                @else
                                    <button type="submit" class="btn btn-warning">
                                        <i class="fas fa-save"></i> Update Request (Limited)
                                    </button>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const supplySelect = document.getElementById('supply_id');
    const stockInfo = document.getElementById('stock-info');
    const currentStockSpan = document.getElementById('current-stock');

    function updateStockInfo() {
        const selectedOption = supplySelect.options[supplySelect.selectedIndex];
        if (selectedOption.value && selectedOption.dataset.currentStock !== undefined) {
            currentStockSpan.textContent = selectedOption.dataset.currentStock;
            stockInfo.style.display = 'block';
        } else {
            stockInfo.style.display = 'none';
        }
    }

    // Update stock info on page load if a supply is already selected
    updateStockInfo();

    // Update stock info when selection changes
    supplySelect.addEventListener('change', updateStockInfo);
});
</script>
@endsection