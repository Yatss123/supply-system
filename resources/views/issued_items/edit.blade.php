@extends('layouts.app')

@section('title', 'Edit Issued Item')

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-edit text-primary me-2"></i>
                Edit Issued Item
            </h1>
            <p class="text-muted mb-0">Modify the details of this issued item</p>
        </div>
        <div class="col-md-4 text-end">
            <div class="btn-group" role="group">
                <a href="{{ route('issued-items.show', $issuedItem) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-eye me-1"></i>
                    View Details
                </a>
                <a href="{{ route('issued-items.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>
                    Back to List
                </a>
            </div>
        </div>
    </div>

    <form action="{{ route('issued-items.update', $issuedItem) }}" method="POST" id="editIssuedItemForm">
        @csrf
        @method('PUT')
        
        <div class="row">
            <!-- Main Form -->
            <div class="col-lg-8">
                <!-- Supply Selection Card -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-box me-1"></i>
                            Supply Information
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Category Selection -->
                            <div class="col-md-6 mb-3">
                                <label for="category_id" class="form-label">
                                    <i class="fas fa-folder text-primary me-1"></i>
                                    Category (Optional)
                                </label>
                                <select class="form-select searchable-dropdown" 
                                        id="category_id" name="category_id">
                                    <option value="">All categories...</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}">
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text">
                                    <i class="fas fa-info-circle text-info me-1"></i>
                                    Filter supply items by category
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="supply_id" class="form-label">
                                    <i class="fas fa-box text-primary me-1"></i>
                                    Supply Item <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('supply_id') is-invalid @enderror" 
                                        id="supply_id" 
                                        name="supply_id" 
                                        required>
                                    <option value="">Select a supply item...</option>
                                    @foreach($supplies as $supply)
                                        <option value="{{ $supply->id }}" 
                                                data-type="{{ $supply->supply_type }}"
                                                data-price="{{ $supply->unit_price }}"
                                                data-unit="{{ $supply->unit }}"
                                                data-stock="{{ $supply->current_stock }}"
                                                data-categories="{{ $supply->categories->pluck('id')->implode(',') }}"
                                                {{ old('supply_id', $issuedItem->supply_id) == $supply->id ? 'selected' : '' }}>
                                            {{ $supply->name }} 
                                            ({{ ucfirst($supply->supply_type) }} - Stock: {{ number_format($supply->current_stock) }} {{ $supply->unit }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('supply_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">

                            <div class="col-md-6 mb-3">
                                <label for="supply_variant_id" class="form-label">
                                    <i class="fas fa-tags text-info me-1"></i>
                                    Variant (Optional)
                                </label>
                                <select class="form-select @error('supply_variant_id') is-invalid @enderror" 
                                        id="supply_variant_id" 
                                        name="supply_variant_id">
                                    <option value="">No variant selected</option>
                                    @if($issuedItem->supplyVariant)
                                        <option value="{{ $issuedItem->supplyVariant->id }}" selected>
                                            {{ $issuedItem->supplyVariant->name }} 
                                            (Stock: {{ number_format($issuedItem->supplyVariant->current_stock) }} {{ $issuedItem->supplyVariant->unit }})
                                        </option>
                                    @endif
                                </select>
                                @error('supply_variant_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="quantity" class="form-label">
                                    <i class="fas fa-calculator text-warning me-1"></i>
                                    Quantity <span class="text-danger">*</span>
                                </label>
                                <input type="number" 
                                       class="form-control @error('quantity') is-invalid @enderror" 
                                       id="quantity" 
                                       name="quantity" 
                                       value="{{ old('quantity', $issuedItem->quantity) }}" 
                                       min="1" 
                                       step="0.01" 
                                       required>
                                @error('quantity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    <span id="availableStock">Available stock will be shown here</span>
                                </div>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="unit_display" class="form-label">
                                    <i class="fas fa-ruler text-secondary me-1"></i>
                                    Unit of Measurement
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="unit_display" 
                                       value="{{ $issuedItem->supplyVariant->unit ?? $issuedItem->supply->unit }}" 
                                       readonly>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="total_value_display" class="form-label">
                                    <i class="fas fa-money-bill text-success me-1"></i>
                                    Total Value
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="total_value_display" 
                                       value="₱{{ number_format($issuedItem->totalValue, 2) }}" 
                                       readonly>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Issue Details Card -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-info">
                            <i class="fas fa-info-circle me-1"></i>
                            Issue Details
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="department_id" class="form-label">
                                    <i class="fas fa-building text-primary me-1"></i>
                                    Department <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('department_id') is-invalid @enderror" 
                                        id="department_id" 
                                        name="department_id" 
                                        required>
                                    <option value="">Select department...</option>
                                    @foreach($departments as $department)
                                        <option value="{{ $department->id }}" 
                                                {{ old('department_id', $issuedItem->department_id) == $department->id ? 'selected' : '' }}>
                                            {{ $department->department_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('department_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="user_id" class="form-label">
                                    <i class="fas fa-user text-secondary me-1"></i>
                                    Recipient (Optional)
                                </label>
                                <select class="form-select @error('user_id') is-invalid @enderror" 
                                        id="user_id" 
                                        name="user_id">
                                    <option value="">No specific recipient</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" 
                                                {{ old('user_id', $issuedItem->user_id) == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('user_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="issued_on" class="form-label">
                                    <i class="fas fa-calendar text-warning me-1"></i>
                                    Issue Date <span class="text-danger">*</span>
                                </label>
                                <input type="date" 
                                       class="form-control @error('issued_on') is-invalid @enderror" 
                                       id="issued_on" 
                                       name="issued_on" 
                                       value="{{ old('issued_on', $issuedItem->issued_on->format('Y-m-d')) }}" 
                                       max="{{ date('Y-m-d') }}" 
                                       required>
                                @error('issued_on')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="issued_by" class="form-label">
                                    <i class="fas fa-user-check text-info me-1"></i>
                                    Issued By
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="issued_by" 
                                       value="{{ $issuedItem->issuedBy->name ?? 'System' }}" 
                                       readonly>
                                <div class="form-text">This field cannot be modified</div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">
                                <i class="fas fa-sticky-note text-secondary me-1"></i>
                                Notes (Optional)
                            </label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                      id="notes" 
                                      name="notes" 
                                      rows="3" 
                                      placeholder="Add any additional notes about this issue...">{{ old('notes', $issuedItem->notes) }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="card shadow mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="fas fa-save me-1"></i>
                                    Update Item
                                </button>
                                <button type="button" class="btn btn-secondary" onclick="resetForm()">
                                    <i class="fas fa-undo me-1"></i>
                                    Reset Changes
                                </button>
                            </div>
                            <div>
                                <a href="{{ route('issued-items.show', $issuedItem) }}" class="btn btn-outline-info me-2">
                                    <i class="fas fa-eye me-1"></i>
                                    View Details
                                </a>
                                <a href="{{ route('issued-items.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-1"></i>
                                    Cancel
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar Information -->
            <div class="col-lg-4">
                <!-- Current Item Information -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-success">
                            <i class="fas fa-info-circle me-1"></i>
                            Current Item Information
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <h5 class="text-primary">{{ $issuedItem->supply->name }}</h5>
                            @if($issuedItem->supplyVariant)
                                <p class="text-info mb-1">Variant: {{ $issuedItem->supplyVariant->name }}</p>
                            @endif
                            <p class="text-muted">{{ $issuedItem->department->department_name }}</p>
                        </div>

                        <div class="row text-center">
                            <div class="col-6">
                                <div class="border-end">
                                    <h4 class="text-warning mb-0">{{ $issuedItem->formattedQuantity }}</h4>
                                    <small class="text-muted">Current Quantity</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <h4 class="text-success mb-0">₱{{ number_format($issuedItem->totalValue, 2) }}</h4>
                                <small class="text-muted">Current Value</small>
                            </div>
                        </div>

                        <hr>

                        <div class="mb-2">
                            <strong>Issue Date:</strong><br>
                            <span class="text-muted">{{ $issuedItem->formattedIssuedOn }}</span>
                        </div>

                        <div class="mb-2">
                            <strong>Issued By:</strong><br>
                            <span class="text-muted">{{ $issuedItem->issuedBy->name ?? 'System' }}</span>
                        </div>

                        @if($issuedItem->user)
                            <div class="mb-2">
                                <strong>Recipient:</strong><br>
                                <span class="text-muted">{{ $issuedItem->user->name }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Supply Information Panel -->
                <div class="card shadow mb-4" id="supplyInfoPanel" style="display: none;">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-info">
                            <i class="fas fa-box me-1"></i>
                            Supply Information
                        </h6>
                    </div>
                    <div class="card-body" id="supplyInfoContent">
                        <!-- Dynamic content will be loaded here -->
                    </div>
                </div>

                <!-- Guidelines -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-warning">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            Important Guidelines
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-warning">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Stock Impact:</strong> Changing the quantity or supply item will adjust stock levels accordingly.
                        </div>

                        <ul class="list-unstyled mb-0">
                            <li class="mb-2">
                                <i class="fas fa-check text-success me-2"></i>
                                <small>Original quantity will be returned to stock</small>
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check text-success me-2"></i>
                                <small>New quantity will be deducted from stock</small>
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check text-success me-2"></i>
                                <small>Stock availability is checked in real-time</small>
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-exclamation text-warning me-2"></i>
                                <small>Cannot exceed available stock</small>
                            </li>
                            <li class="mb-0">
                                <i class="fas fa-clock text-info me-2"></i>
                                <small>Issue date cannot be in the future</small>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-secondary">
                            <i class="fas fa-tools me-1"></i>
                            Quick Actions
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('supplies.show', $issuedItem->supply) }}" class="btn btn-outline-info btn-sm">
                                <i class="fas fa-box me-1"></i>
                                View Supply Details
                            </a>
                            <a href="{{ route('issued-items.create') }}" class="btn btn-outline-success btn-sm">
                                <i class="fas fa-plus me-1"></i>
                                Issue New Item
                            </a>
                            @can('delete', $issuedItem)
                                <button type="button" class="btn btn-outline-danger btn-sm" onclick="confirmDelete()">
                                    <i class="fas fa-trash me-1"></i>
                                    Delete This Item
                                </button>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">
                    <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                    Confirm Deletion
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Important:</strong> Deleting this issued item will return the issued quantity back to the supply stock.
                </div>
                <p>Are you sure you want to delete this issued item instead of editing it?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>
                    Cancel
                </button>
                <form action="{{ route('issued-items.destroy', $issuedItem) }}" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-1"></i>
                        Delete Item
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const supplySelect = document.getElementById('supply_id');
    const variantSelect = document.getElementById('supply_variant_id');
    const quantityInput = document.getElementById('quantity');
    const unitDisplay = document.getElementById('unit_display');
    const totalValueDisplay = document.getElementById('total_value_display');
    const availableStockSpan = document.getElementById('availableStock');
    const supplyInfoPanel = document.getElementById('supplyInfoPanel');
    const supplyInfoContent = document.getElementById('supplyInfoContent');

    // Initialize with current supply
    if (supplySelect.value) {
        updateSupplyInfo();
    }

    // Supply selection change
    supplySelect.addEventListener('change', function() {
        updateSupplyInfo();
        loadSupplyVariants();
    });

    // Variant selection change
    variantSelect.addEventListener('change', function() {
        updateCalculations();
    });

    // Quantity change
    quantityInput.addEventListener('input', function() {
        updateCalculations();
        validateQuantity();
    });

    function updateSupplyInfo() {
        const selectedOption = supplySelect.options[supplySelect.selectedIndex];
        
        if (selectedOption.value) {
            const supplyId = selectedOption.value;
            const supplyType = selectedOption.dataset.type;
            const supplyPrice = parseFloat(selectedOption.dataset.price);
            const supplyUnit = selectedOption.dataset.unit;
            const supplyStock = parseFloat(selectedOption.dataset.stock);

            // Update unit display
            unitDisplay.value = supplyUnit;

            // Update available stock display
            availableStockSpan.innerHTML = `Available: <strong class="text-success">${supplyStock.toLocaleString()} ${supplyUnit}</strong>`;

            // Show supply info panel
            supplyInfoPanel.style.display = 'block';
            
            // Load supply information via AJAX
            fetch(`/issued-items/supply-info/${supplyId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateSupplyInfoPanel(data.supply);
                    }
                })
                .catch(error => {
                    console.error('Error loading supply info:', error);
                });

            updateCalculations();
        } else {
            unitDisplay.value = '';
            availableStockSpan.textContent = 'Select a supply item first';
            supplyInfoPanel.style.display = 'none';
            totalValueDisplay.value = '';
        }
    }

    function loadSupplyVariants() {
        const supplyId = supplySelect.value;
        
        // Clear current variants
        variantSelect.innerHTML = '<option value="">No variant selected</option>';
        
        if (supplyId) {
            fetch(`/issued-items/supply-variants/${supplyId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.variants.length > 0) {
                        data.variants.forEach(variant => {
                            const option = document.createElement('option');
                            option.value = variant.id;
                            option.textContent = `${variant.name} (Stock: ${variant.current_stock.toLocaleString()} ${variant.unit})`;
                            option.dataset.price = variant.price;
                            option.dataset.unit = variant.unit;
                            option.dataset.stock = variant.current_stock;
                            variantSelect.appendChild(option);
                        });
                    }
                })
                .catch(error => {
                    console.error('Error loading variants:', error);
                });
        }
    }

    function updateCalculations() {
        const selectedSupply = supplySelect.options[supplySelect.selectedIndex];
        const selectedVariant = variantSelect.options[variantSelect.selectedIndex];
        const quantity = parseFloat(quantityInput.value) || 0;

        if (selectedSupply.value && quantity > 0) {
            let price, unit, stock;

            if (selectedVariant.value) {
                // Use variant data
                price = parseFloat(selectedVariant.dataset.price);
                unit = selectedVariant.dataset.unit;
                stock = parseFloat(selectedVariant.dataset.stock);
                unitDisplay.value = unit;
                availableStockSpan.innerHTML = `Available: <strong class="text-success">${stock.toLocaleString()} ${unit}</strong>`;
            } else {
                // Use supply data
                price = parseFloat(selectedSupply.dataset.price);
                unit = selectedSupply.dataset.unit;
                stock = parseFloat(selectedSupply.dataset.stock);
            }

            const totalValue = quantity * price;
            totalValueDisplay.value = `₱${totalValue.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
        } else {
            totalValueDisplay.value = '';
        }
    }

    function validateQuantity() {
        const selectedSupply = supplySelect.options[supplySelect.selectedIndex];
        const selectedVariant = variantSelect.options[variantSelect.selectedIndex];
        const quantity = parseFloat(quantityInput.value) || 0;

        if (selectedSupply.value && quantity > 0) {
            let availableStock;

            if (selectedVariant.value) {
                availableStock = parseFloat(selectedVariant.dataset.stock);
            } else {
                availableStock = parseFloat(selectedSupply.dataset.stock);
            }

            if (quantity > availableStock) {
                quantityInput.setCustomValidity(`Cannot exceed available stock of ${availableStock.toLocaleString()}`);
                quantityInput.classList.add('is-invalid');
            } else {
                quantityInput.setCustomValidity('');
                quantityInput.classList.remove('is-invalid');
            }
        }
    }

    function updateSupplyInfoPanel(supply) {
        const stockStatus = supply.current_stock <= supply.minimum_stock_level ? 'danger' : 
                           supply.current_stock <= (supply.minimum_stock_level * 1.5) ? 'warning' : 'success';

        supplyInfoContent.innerHTML = `
            <div class="text-center mb-3">
                <h6 class="text-primary">${supply.name}</h6>
                <span class="badge bg-${supply.supply_type === 'consumable' ? 'warning' : 'success'}">${supply.supply_type.charAt(0).toUpperCase() + supply.supply_type.slice(1)}</span>
            </div>
            
            <div class="row text-center mb-3">
                <div class="col-6">
                    <div class="border-end">
                        <h6 class="text-${stockStatus} mb-0">${supply.current_stock.toLocaleString()}</h6>
                        <small class="text-muted">Current Stock</small>
                    </div>
                </div>
                <div class="col-6">
                    <h6 class="text-success mb-0">₱${parseFloat(supply.price).toLocaleString('en-US', {minimumFractionDigits: 2})}</h6>
                    <small class="text-muted">Unit Price</small>
                </div>
            </div>

            <div class="mb-2">
                <strong>Unit:</strong> <span class="text-muted">${supply.unit}</span>
            </div>
            <div class="mb-2">
                <strong>Minimum Level:</strong> <span class="text-muted">${supply.minimum_stock_level || 'Not set'}</span>
            </div>
            ${supply.description ? `<div class="mb-2"><strong>Description:</strong> <span class="text-muted">${supply.description}</span></div>` : ''}
        `;
    }

    // Filter supply items based on selected category
    const categorySelect = document.getElementById('category_id');
    
    categorySelect.addEventListener('change', function() {
        const selectedCategoryId = this.value;
        const supplyOptions = Array.from(supplySelect.options);
        
        // Show/hide supply options based on category
        supplyOptions.forEach(option => {
            if (option.value === '') {
                // Always show the default "Select a supply item..." option
                option.style.display = 'block';
                return;
            }
            
            const supplyCategoryIds = option.dataset.categories ? option.dataset.categories.split(',') : [];
            
            if (selectedCategoryId === '' || supplyCategoryIds.includes(selectedCategoryId)) {
                option.style.display = 'block';
            } else {
                option.style.display = 'none';
            }
        });
        
        // Reset supply selection if current selection is now hidden
        const currentSupplyOption = supplySelect.options[supplySelect.selectedIndex];
        if (currentSupplyOption && currentSupplyOption.style.display === 'none') {
            supplySelect.value = '';
            supplySelect.dispatchEvent(new Event('change'));
        }
    });
});

function resetForm() {
    if (confirm('Are you sure you want to reset all changes? This will restore the original values.')) {
        document.getElementById('editIssuedItemForm').reset();
        // Trigger change events to update displays
        document.getElementById('supply_id').dispatchEvent(new Event('change'));
    }
}

function confirmDelete() {
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    deleteModal.show();
}
</script>
@endpush
@endsection
