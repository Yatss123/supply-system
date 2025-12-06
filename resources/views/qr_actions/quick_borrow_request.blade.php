@extends('layouts.app')

@section('content')
<div class="container-fluid px-2">
    <!-- Mobile-optimized header -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <button type="button" class="btn btn-link p-0 me-3" onclick="window.history.back()">
                            <i class="fas fa-arrow-left fs-4"></i>
                        </button>
                        <div class="flex-grow-1">
                            <h5 class="mb-1 fw-bold">Quick Borrow Request</h5>
                            <p class="mb-0 text-muted small">{{ $supply->name }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Supply Information -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-3">
                    <div class="row g-2">
                        <div class="col-4">
                            <div class="text-center p-2 bg-light rounded">
                                <div class="fw-bold text-primary fs-5">{{ $supply->availableQuantity() }}</div>
                                <small class="text-muted">Currently Available</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="text-center p-2 bg-light rounded">
                                @php($borrowedNow = max(0, (int)($supply->quantity ?? 0) - (int)$supply->availableQuantity()))
                                <div class="fw-bold text-info fs-5">{{ $borrowedNow }}</div>
                                <small class="text-muted">Borrowed (Regular + Inter-Dept)</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="text-center p-2 bg-light rounded">
                                <span class="badge bg-{{ $supply->status === 'active' ? 'success' : ($supply->status === 'inactive' ? 'secondary' : 'danger') }}">
                                    {{ ucfirst($supply->status) }}
                                </span>
                                <div><small class="text-muted">Status</small></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(in_array(auth()->user()->role, ['admin', 'super_admin']))
        <!-- Admin View: Pending Requests -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent border-0">
                        <h6 class="fw-bold mb-0">
                            <i class="fas fa-clock me-2 text-warning"></i>Pending Loan Requests
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        @forelse($pendingRequests as $request)
                        <div class="border-bottom p-3">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div class="flex-grow-1">
                                    <div class="fw-medium">{{ $request->user->name }}</div>
                                    <small class="text-muted">
                                        {{ $request->user->department->department_name ?? 'No Department' }}
                                    </small>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-{{ $request->type === 'interdepartment' ? 'info' : 'primary' }}">
                                        {{ ucfirst($request->type) }}
                                    </span>
                                </div>
                            </div>
                            
                            <div class="mb-2">
                                <strong>Quantity:</strong> {{ $request->quantity }}
                                @if($request->purpose)
                                <br><strong>Purpose:</strong> {{ $request->purpose }}
                                @endif
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">{{ $request->created_at->diffForHumans() }}</small>
                                <div class="btn-group btn-group-sm">
                                    <form action="{{ route('qr.borrow.approve', [$supply, $request]) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-sm">
                                            <i class="fas fa-check me-1"></i>Approve
                                        </button>
                                    </form>
                                    <form action="{{ route('qr.borrow.reject', [$supply, $request]) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-danger btn-sm">
                                            <i class="fas fa-times me-1"></i>Reject
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="p-4 text-center text-muted">
                            <i class="fas fa-inbox fs-1 mb-3"></i>
                            <div>No pending loan requests for this supply.</div>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Admin: Create New Request -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent border-0">
                        <h6 class="fw-bold mb-0">
                            <i class="fas fa-plus me-2 text-success"></i>Create New Borrow Request
                        </h6>
                    </div>
                    <div class="card-body">
                        <button type="button" class="btn btn-outline-primary w-100" onclick="toggleNewRequestForm()">
                            <i class="fas fa-plus me-2"></i>Add New Loan Request
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Loan Request Form -->
    <div class="row mb-3" id="loanRequestForm" @if(in_array(auth()->user()->role, ['admin', 'super_admin'])) style="display: none;" @endif>
        <div class="col-12">
            <form action="{{ route('qr.borrow', $supply) }}" method="POST" id="borrowForm">
                @csrf
                
                <!-- Loan Type Selection -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-transparent border-0 pb-0">
                        <h6 class="fw-bold mb-0">
                            <i class="fas fa-exchange-alt me-2 text-primary"></i>Loan Type
                        </h6>
                    </div>
                    <div class="card-body pt-2">
                        <div class="row g-2">
                            <div class="col-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="loan_type" 
                                           id="regular_loan" value="regular" checked>
                                    <label class="form-check-label w-100 text-center p-3 border rounded" for="regular_loan">
                                        <i class="fas fa-user fs-3 d-block mb-2 text-primary"></i>
                                        <div class="fw-medium">Regular Loan</div>
                                        <small class="text-muted">Within department</small>
                                    </label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="loan_type" 
                                           id="interdepartment_loan" value="interdepartment">
                                    <label class="form-check-label w-100 text-center p-3 border rounded" for="interdepartment_loan">
                                        <i class="fas fa-building fs-3 d-block mb-2 text-info"></i>
                                        <div class="fw-medium">Inter-Department</div>
                                        <small class="text-muted">Between departments</small>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Borrower Selection (for admins) -->
                @if(in_array(auth()->user()->role, ['admin', 'super_admin']))
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body p-3">
                        <label for="borrower_id" class="form-label fw-medium">
                            <i class="fas fa-user me-2"></i>Select Borrower
                        </label>
                        <select name="borrower_id" id="borrower_id" class="form-select form-select-lg" required>
                            <option value="">Choose a borrower...</option>
                            @foreach($users as $user)
                            <option value="{{ $user->id }}" data-department="{{ $user->department->department_name ?? '' }}">
                                {{ $user->name }} - {{ $user->department->department_name ?? 'No Department' }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                @endif

                <!-- Department Information (Auto-assigned) -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body p-3">
                        <label class="form-label fw-medium">
                            <i class="fas fa-building me-2"></i>Department Assignment
                        </label>
                        <div class="alert alert-info mb-0">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-info-circle me-2"></i>
                                <div>
                                    <strong>Auto-assigned:</strong> {{ Auth::user()->department->department_name ?? 'Your Department' }}
                                    <br>
                                    <small class="text-muted">
                                        For regular loans: Items will be assigned to your department.<br>
                                        For inter-department loans: You're borrowing from the owning department.
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quantity -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body p-3">
                        <label for="quantity" class="form-label fw-medium">
                            <i class="fas fa-hashtag me-2"></i>Quantity to Borrow
                        </label>
                        <div class="input-group input-group-lg">
                            <button type="button" class="btn btn-outline-secondary" id="decrease_qty">
                                <i class="fas fa-minus"></i>
                            </button>
                            <input type="number" name="quantity_requested" id="quantity" class="form-control text-center" 
                                   value="1" min="1" max="{{ $supply->availableQuantity() }}" required>
                            <button type="button" class="btn btn-outline-secondary" id="increase_qty">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                        <small class="text-muted">
                            Maximum available: {{ $supply->availableQuantity() }}
                        </small>
                    </div>
                </div>

                <!-- Borrow Duration -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body p-3">
                        <label for="borrow_duration" class="form-label fw-medium">
                            <i class="fas fa-calendar me-2"></i>Borrow Duration
                        </label>
                        <select name="borrow_duration" id="borrow_duration" class="form-select form-select-lg" required>
                            <option value="">Select duration...</option>
                            <option value="1">1 Day</option>
                            <option value="3">3 Days</option>
                            <option value="7" selected>1 Week</option>
                            <option value="14">2 Weeks</option>
                            <option value="30">1 Month</option>
                            <option value="custom">Custom Duration</option>
                        </select>
                    </div>
                </div>

                <!-- Custom Duration -->
                <div class="card border-0 shadow-sm mb-3" id="custom_duration_section" style="display: none;">
                    <div class="card-body p-3">
                        <label for="return_date" class="form-label fw-medium">
                            <i class="fas fa-calendar-alt me-2"></i>Expected Return Date
                        </label>
                        <input type="date" name="expected_return_date" id="return_date" class="form-control form-control-lg" 
                               min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                    </div>
                </div>

                <!-- Purpose -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body p-3">
                        <label for="purpose" class="form-label fw-medium">
                            <i class="fas fa-comment me-2"></i>Purpose of Borrowing
                        </label>
                        <textarea name="purpose" id="purpose" class="form-control" rows="3" 
                                  placeholder="Please describe why you need to borrow this item..." required></textarea>
                    </div>
                </div>

                <!-- Priority Level -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body p-3">
                        <label for="priority" class="form-label fw-medium">
                            <i class="fas fa-flag me-2"></i>Priority Level
                        </label>
                        <select name="priority" id="priority" class="form-select form-select-lg" required>
                            <option value="low">Low - Regular request</option>
                            <option value="medium" selected>Medium - Moderate urgency</option>
                            <option value="high">High - Urgent need</option>
                            <option value="urgent">Urgent - Critical requirement</option>
                        </select>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="row mb-4">
                    <div class="col-12">
                        @if(in_array(auth()->user()->role, ['admin', 'super_admin']))
                        <button type="submit" class="btn btn-success btn-lg w-100" id="submit_btn">
                            <i class="fas fa-check me-2"></i>Create & Approve Loan
                        </button>
                        @else
                        <button type="submit" class="btn btn-primary btn-lg w-100" id="submit_btn">
                            <i class="fas fa-paper-plane me-2"></i>Submit Loan Request
                        </button>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Mobile-specific styles -->
<style>
@media (max-width: 768px) {
    .container-fluid {
        padding-left: 10px !important;
        padding-right: 10px !important;
    }
    
    .form-select-lg, .form-control {
        font-size: 1rem;
        padding: 0.75rem 1rem;
    }
    
    .card {
        border-radius: 12px;
    }
    
    .btn {
        border-radius: 8px;
    }
    
    /* Touch-friendly form elements */
    .form-check-input {
        width: 1.25rem;
        height: 1.25rem;
    }
    
    .form-check-label {
        cursor: pointer;
    }
    
    /* Improve tap targets */
    .btn, .form-control, .form-select {
        min-height: 48px;
    }
    
    /* Better spacing for mobile */
    .input-group-lg .btn {
        padding: 0.75rem 1rem;
    }
    
    .btn-group-sm .btn {
        min-height: 32px;
        font-size: 0.875rem;
    }
}

/* Loading states */
.btn.loading {
    position: relative;
    color: transparent;
}

.btn.loading::after {
    content: "";
    position: absolute;
    width: 16px;
    height: 16px;
    top: 50%;
    left: 50%;
    margin-left: -8px;
    margin-top: -8px;
    border: 2px solid #ffffff;
    border-radius: 50%;
    border-top-color: transparent;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

/* Form validation styles */
.is-invalid {
    border-color: #dc3545;
}

.is-valid {
    border-color: #198754;
}
</style>

<!-- Mobile-specific JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const loanTypeRadios = document.querySelectorAll('input[name="loan_type"]');
    const borrowDurationSelect = document.getElementById('borrow_duration');
    const customDurationSection = document.getElementById('custom_duration_section');
    const returnDateInput = document.getElementById('return_date');
    const quantityInput = document.getElementById('quantity');
    const decreaseBtn = document.getElementById('decrease_qty');
    const increaseBtn = document.getElementById('increase_qty');
    const submitBtn = document.getElementById('submit_btn');
    const form = document.getElementById('borrowForm');

    // Handle borrow duration change
    borrowDurationSelect.addEventListener('change', function() {
        if (this.value === 'custom') {
            customDurationSection.style.display = 'block';
            returnDateInput.required = true;
        } else {
            customDurationSection.style.display = 'none';
            returnDateInput.required = false;
        }
    });

    // Quantity controls
    decreaseBtn.addEventListener('click', function() {
        const currentValue = parseInt(quantityInput.value);
        if (currentValue > 1) {
            quantityInput.value = currentValue - 1;
        }
    });

    increaseBtn.addEventListener('click', function() {
        const currentValue = parseInt(quantityInput.value);
        const maxValue = parseInt(quantityInput.max);
        if (currentValue < maxValue) {
            quantityInput.value = currentValue + 1;
        }
    });

    // Form submission
    form.addEventListener('submit', function(e) {
        // Calculate expected_return_date from borrow_duration if not using custom duration
        const borrowDuration = borrowDurationSelect.value;
        const returnDateInput = document.getElementById('return_date');
        
        if (borrowDuration && borrowDuration !== 'custom') {
            const days = parseInt(borrowDuration);
            const returnDate = new Date();
            returnDate.setDate(returnDate.getDate() + days);
            
            // Create a hidden input for expected_return_date
            const hiddenDateInput = document.createElement('input');
            hiddenDateInput.type = 'hidden';
            hiddenDateInput.name = 'expected_return_date';
            hiddenDateInput.value = returnDate.toISOString().split('T')[0];
            form.appendChild(hiddenDateInput);
        }
        
        submitBtn.classList.add('loading');
        submitBtn.disabled = true;
    });

    // Add haptic feedback for mobile devices
    if ('vibrate' in navigator) {
        const interactiveElements = document.querySelectorAll('button, input, select, textarea');
        interactiveElements.forEach(element => {
            element.addEventListener('touchstart', function() {
                navigator.vibrate(10);
            });
        });
    }

    // Auto-resize textarea
    const purposeTextarea = document.getElementById('purpose');
    purposeTextarea.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = (this.scrollHeight) + 'px';
    });
});

// Toggle new request form for admins
function toggleNewRequestForm() {
    const form = document.getElementById('loanRequestForm');
    if (form.style.display === 'none') {
        form.style.display = 'block';
        form.scrollIntoView({ behavior: 'smooth' });
    } else {
        form.style.display = 'none';
    }
}
</script>
@endsection