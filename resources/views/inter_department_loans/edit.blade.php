@extends('layouts.app')

@section('title', 'Edit Borrow Request')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/inter-department-loans.css') }}">
@endsection

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Flash Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Please fix the following errors:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Header -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <div class="bg-warning bg-opacity-10 rounded-circle p-3 me-3">
                        <i class="fas fa-edit text-warning fs-4"></i>
                    </div>
                    <div>
                        <h1 class="h3 mb-1 fw-bold text-dark">Edit Borrow Request</h1>
                        <p class="text-muted mb-0">
                            <i class="fas fa-info-circle me-1"></i>
                            Request ID: #{{ $interDepartmentLoan->id }}
                        </p>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('loan-requests.inter-department.show', $interDepartmentLoan->id) }}" 
                       class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Details
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="row">
        <!-- Edit Form -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-edit text-primary me-2"></i>
                        Edit Request Details
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('loan-requests.inter-department.update', $interDepartmentLoan->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row g-4">
                            <!-- Supply Item -->
                            <div class="col-md-6">
                                <label for="issued_item_id" class="form-label fw-medium">
                                    <i class="fas fa-box text-primary me-2"></i>Supply Item *
                                </label>
                                <select name="issued_item_id" id="issued_item_id" class="form-select form-select-lg" required>
                                    <option value="">Select Supply Item</option>
                                    @foreach($availableItems as $item)
                                        <option value="{{ $item->id }}" 
                                            data-quantity="{{ $item->quantity }}"
                                            {{ old('issued_item_id', $interDepartmentLoan->issued_item_id) == $item->id ? 'selected' : '' }}>
                                            {{ $item->supply->item_name }} - {{ $item->department->department_name }} (Available: {{ $item->quantity }})
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Select the item you want to borrow
                                </div>
                            </div>

                            <!-- Quantity -->
                            <div class="col-md-6">
                                <label for="quantity_requested" class="form-label fw-medium">
                                    <i class="fas fa-sort-numeric-up text-success me-2"></i>Quantity *
                                </label>
                                <input type="number" name="quantity_requested" id="quantity_requested" 
                                       class="form-control form-control-lg" 
                                       value="{{ old('quantity_requested', $interDepartmentLoan->quantity_requested) }}" 
                                       min="1" required>
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    <span id="available-quantity">Enter the quantity you need</span>
                                </div>
                            </div>

                            <!-- Expected Return Date -->
                            <div class="col-md-6">
                                <label for="expected_return_date" class="form-label fw-medium">
                                    <i class="fas fa-calendar-alt text-info me-2"></i>Expected Return Date *
                                </label>
                                <input type="date" name="expected_return_date" id="expected_return_date" 
                                       class="form-control form-control-lg" 
                                       value="{{ old('expected_return_date', $interDepartmentLoan->expected_return_date ? $interDepartmentLoan->expected_return_date->format('Y-m-d') : '') }}" 
                                       required>
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    When do you plan to return the item?
                                </div>
                            </div>

                            <!-- Purpose -->
                            <div class="col-12">
                                <label for="purpose" class="form-label fw-medium">
                                    <i class="fas fa-clipboard-list text-warning me-2"></i>Purpose *
                                </label>
                                <textarea name="purpose" id="purpose" class="form-control" rows="4" 
                                          placeholder="Please describe the purpose of borrowing this item..." required>{{ old('purpose', $interDepartmentLoan->purpose) }}</textarea>
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Provide a clear explanation of why you need this item
                                </div>
                            </div>

                            <!-- Additional Notes -->
                            <div class="col-12">
                                <label for="additional_notes" class="form-label fw-medium">
                                    <i class="fas fa-sticky-note text-secondary me-2"></i>Additional Notes
                                </label>
                                <textarea name="additional_notes" id="additional_notes" class="form-control" rows="3" 
                                          placeholder="Any additional information or special requirements...">{{ old('additional_notes', $interDepartmentLoan->additional_notes) }}</textarea>
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Optional: Add any special instructions or requirements
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="d-flex justify-content-end gap-3 mt-4 pt-3 border-top">
                            <a href="{{ route('loan-requests.inter-department.show', $interDepartmentLoan->id) }}" 
                               class="btn btn-outline-secondary btn-lg">
                                <i class="fas fa-times me-2"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save me-2"></i>Update Request
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Information Sidebar -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-warning bg-opacity-10 border-bottom">
                    <h5 class="card-title mb-0 text-warning">
                        <i class="fas fa-info-circle me-2"></i>
                        Edit Guidelines
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="alert alert-warning border-0 mb-4">
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Important Notice</strong>
                        </div>
                        <small>Only pending requests can be edited. Changes may require re-approval.</small>
                    </div>

                    <h6 class="fw-bold mb-3 text-dark">
                        <i class="fas fa-clipboard-check me-2 text-primary"></i>
                        Requirements
                    </h6>
                    <ul class="list-unstyled">
                        <li class="d-flex align-items-start mb-3">
                            <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-3 mt-1">
                                <i class="fas fa-clock text-primary small"></i>
                            </div>
                            <div>
                                <div class="fw-medium">Pending Status</div>
                                <small class="text-muted">Only pending requests can be edited</small>
                            </div>
                        </li>
                        <li class="d-flex align-items-start mb-3">
                            <div class="bg-success bg-opacity-10 rounded-circle p-2 me-3 mt-1">
                                <i class="fas fa-check text-success small"></i>
                            </div>
                            <div>
                                <div class="fw-medium">Re-approval Required</div>
                                <small class="text-muted">Changes may require new approvals</small>
                            </div>
                        </li>
                        <li class="d-flex align-items-start mb-3">
                            <div class="bg-info bg-opacity-10 rounded-circle p-2 me-3 mt-1">
                                <i class="fas fa-calendar text-info small"></i>
                            </div>
                            <div>
                                <div class="fw-medium">Realistic Return Date</div>
                                <small class="text-muted">Ensure the expected return date is achievable</small>
                            </div>
                        </li>
                        <li class="d-flex align-items-start mb-3">
                            <div class="bg-warning bg-opacity-10 rounded-circle p-2 me-3 mt-1">
                                <i class="fas fa-boxes text-warning small"></i>
                            </div>
                            <div>
                                <div class="fw-medium">Stock Verification</div>
                                <small class="text-muted">Verify quantity doesn't exceed available stock</small>
                            </div>
                        </li>
                        <li class="d-flex align-items-start">
                            <div class="bg-secondary bg-opacity-10 rounded-circle p-2 me-3 mt-1">
                                <i class="fas fa-handshake text-secondary small"></i>
                            </div>
                            <div>
                                <div class="fw-medium">Department Contact</div>
                                <small class="text-muted">Contact the lending department before making changes</small>
                            </div>
                        </li>
                    </ul>

                    <div class="mt-4 p-3 bg-light rounded">
                        <h6 class="fw-bold mb-2 text-dark">
                            <i class="fas fa-question-circle me-2 text-info"></i>
                            Need Help?
                        </h6>
                        <p class="small text-muted mb-2">
                            If you have questions about editing your request, contact:
                        </p>
                        <ul class="small text-muted mb-0">
                            <li>Your department dean</li>
                            <li>The lending department</li>
                            <li>System administrator</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const issuedItemSelect = document.getElementById('issued_item_id');
    const quantityInput = document.getElementById('quantity_requested');
    const availableQuantitySpan = document.getElementById('available-quantity');
    const returnDate = document.getElementById('expected_return_date');

    // Update available quantity display when item is selected
    function updateAvailableQuantity() {
        const selectedOption = issuedItemSelect.options[issuedItemSelect.selectedIndex];
        if (selectedOption && selectedOption.value) {
            const availableQty = selectedOption.getAttribute('data-quantity');
            availableQuantitySpan.textContent = `Available: ${availableQty} units`;
            quantityInput.max = availableQty;
        } else {
            availableQuantitySpan.textContent = 'Enter the quantity you need';
            quantityInput.removeAttribute('max');
        }
    }

    issuedItemSelect.addEventListener('change', updateAvailableQuantity);

    // Set minimum date to tomorrow
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    returnDate.min = tomorrow.toISOString().split('T')[0];

    // Initialize available quantity display
    updateAvailableQuantity();
});
</script>
@endsection