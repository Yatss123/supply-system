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
                            <h5 class="mb-1 fw-bold">Quick Status Change</h5>
                            <p class="mb-0 text-muted small">{{ $supply->name }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Current Status -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-3">
                    <div class="text-center">
                        <h6 class="text-muted mb-2">Current Status</h6>
                        <span class="badge bg-{{ $supply->status === 'active' ? 'success' : ($supply->status === 'inactive' ? 'secondary' : 'danger') }} fs-4 px-4 py-2">
                            {{ ucfirst($supply->status) }}
                        </span>
                        <div class="mt-2">
                            <small class="text-muted">
                                Last updated: {{ $supply->updated_at->format('M d, Y h:i A') }}
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Role-based Action Info -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="alert alert-info border-0 rounded-3">
                <div class="d-flex align-items-start">
                    <i class="fas fa-info-circle me-2 mt-1"></i>
                    <div>
                        @if(in_array(auth()->user()->role, ['admin', 'super_admin']))
                            <strong>Admin Access:</strong> Your status changes will be applied immediately.
                        @else
                            <strong>Request Mode:</strong> Your status change will be submitted for admin approval.
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Change Options -->
    <form action="{{ route('qr.process-status-change', $supply) }}" method="POST" id="statusChangeForm">
        @csrf
        
        <div class="row mb-3">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent border-0 pb-0">
                        <h6 class="fw-bold mb-0">
                            <i class="fas fa-exchange-alt me-2 text-primary"></i>Select New Status
                        </h6>
                    </div>
                    <div class="card-body pt-2">
                        <div class="row g-3">
                            <!-- Active Status -->
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="new_status" 
                                           id="status_active" value="active" 
                                           {{ $supply->status === 'active' ? 'disabled' : '' }}>
                                    <label class="form-check-label w-100" for="status_active">
                                        <div class="card border {{ $supply->status === 'active' ? 'border-success bg-light' : 'border-light' }} h-100">
                                            <div class="card-body p-3">
                                                <div class="d-flex align-items-center">
                                                    <div class="me-3">
                                                        <div class="bg-success rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                            <i class="fas fa-check text-white"></i>
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <h6 class="mb-1 fw-bold text-success">Active</h6>
                                                        <small class="text-muted">Item is available and functional</small>
                                                        @if($supply->status === 'active')
                                                        <div><small class="text-success"><i class="fas fa-check-circle me-1"></i>Current Status</small></div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- Inactive Status -->
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="new_status" 
                                           id="status_inactive" value="inactive"
                                           {{ $supply->status === 'inactive' ? 'disabled' : '' }}>
                                    <label class="form-check-label w-100" for="status_inactive">
                                        <div class="card border {{ $supply->status === 'inactive' ? 'border-secondary bg-light' : 'border-light' }} h-100">
                                            <div class="card-body p-3">
                                                <div class="d-flex align-items-center">
                                                    <div class="me-3">
                                                        <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                            <i class="fas fa-pause text-white"></i>
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <h6 class="mb-1 fw-bold text-secondary">Inactive</h6>
                                                        <small class="text-muted">Item is temporarily unavailable</small>
                                                        @if($supply->status === 'inactive')
                                                        <div><small class="text-secondary"><i class="fas fa-check-circle me-1"></i>Current Status</small></div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- Damaged Status -->
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="new_status" 
                                           id="status_damaged" value="damaged"
                                           {{ $supply->status === 'damaged' ? 'disabled' : '' }}>
                                    <label class="form-check-label w-100" for="status_damaged">
                                        <div class="card border {{ $supply->status === 'damaged' ? 'border-danger bg-light' : 'border-light' }} h-100">
                                            <div class="card-body p-3">
                                                <div class="d-flex align-items-center">
                                                    <div class="me-3">
                                                        <div class="bg-danger rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                            <i class="fas fa-exclamation-triangle text-white"></i>
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <h6 class="mb-1 fw-bold text-danger">Damaged</h6>
                                                        <small class="text-muted">Item needs repair or replacement</small>
                                                        @if($supply->status === 'damaged')
                                                        <div><small class="text-danger"><i class="fas fa-check-circle me-1"></i>Current Status</small></div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-3">
                        <label class="form-label fw-bold">
                            <i class="fas fa-comment-alt me-2"></i>Reason/Notes <span class="text-danger">*</span>
                        </label>
                        <textarea name="reason" class="form-control" rows="3" 
                                  placeholder="Please provide a reason for this status change..." required></textarea>
                        <div class="form-text">
                            <small class="text-muted">Explain why this status change is needed</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @unless($canDirectChange)
        <!-- Priority Level (for non-admin users) -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-3">
                        <label class="form-label fw-bold">
                            <i class="fas fa-exclamation-triangle me-2"></i>Priority Level
                        </label>
                        <div class="row g-2">
                            <div class="col-6">
                                <input type="radio" class="btn-check" name="priority" id="priority_low" value="low" checked>
                                <label class="btn btn-outline-secondary w-100" for="priority_low">
                                    <i class="fas fa-circle text-secondary me-2"></i>Low
                                </label>
                            </div>
                            <div class="col-6">
                                <input type="radio" class="btn-check" name="priority" id="priority_medium" value="medium">
                                <label class="btn btn-outline-info w-100" for="priority_medium">
                                    <i class="fas fa-circle text-info me-2"></i>Medium
                                </label>
                            </div>
                            <div class="col-6">
                                <input type="radio" class="btn-check" name="priority" id="priority_high" value="high">
                                <label class="btn btn-outline-warning w-100" for="priority_high">
                                    <i class="fas fa-circle text-warning me-2"></i>High
                                </label>
                            </div>
                            <div class="col-6">
                                <input type="radio" class="btn-check" name="priority" id="priority_urgent" value="urgent">
                                <label class="btn btn-outline-danger w-100" for="priority_urgent">
                                    <i class="fas fa-circle text-danger me-2"></i>Urgent
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endunless

        <!-- Submit Button -->
        <div class="row mb-4">
            <div class="col-12">
                @if(in_array(auth()->user()->role, ['admin', 'super_admin']))
                <button type="submit" class="btn btn-primary btn-lg w-100" id="submit_btn">
                    <i class="fas fa-check me-2"></i>Apply Status Change
                </button>
                @else
                <button type="submit" class="btn btn-warning btn-lg w-100" id="submit_btn">
                    <i class="fas fa-paper-plane me-2"></i>Submit for Approval
                </button>
                @endif
            </div>
        </div>
    </form>

    <!-- Recent Status Change Requests (for admins) -->
    @if(in_array(auth()->user()->role, ['admin', 'super_admin']) && isset($recentRequests) && $recentRequests->count() > 0)
    <div class="row mb-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h6 class="fw-bold mb-0">
                        <i class="fas fa-clock me-2 text-info"></i>Recent Status Change Requests
                    </h6>
                </div>
                <div class="card-body p-0">
                    @foreach($recentRequests as $request)
                    <div class="border-bottom p-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <div class="fw-medium">{{ $request->user->name }}</div>
                                <small class="text-muted">
                                    Requested: {{ ucfirst($request->requested_status) }} 
                                    <span class="badge bg-{{ $request->priority === 'urgent' ? 'danger' : ($request->priority === 'high' ? 'warning' : 'secondary') }} ms-2">
                                        {{ ucfirst($request->priority) }}
                                    </span>
                                </small>
                                <div class="mt-1">
                                    <small class="text-muted">{{ $request->reason }}</small>
                                </div>
                            </div>
                            <div class="text-end">
                                <small class="text-muted">{{ $request->created_at->diffForHumans() }}</small>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif
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
    
    /* Status cards hover effect */
    .form-check-label .card {
        transition: all 0.2s ease;
    }
    
    .form-check-input:checked + .form-check-label .card {
        border-width: 2px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
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

/* Disabled status cards */
.form-check-input:disabled + .form-check-label .card {
    opacity: 0.6;
    cursor: not-allowed;
}
</style>

<!-- Mobile-specific JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('statusChangeForm');
    const submitBtn = document.getElementById('submit_btn');
    const statusRadios = document.querySelectorAll('input[name="new_status"]');
    const reasonTextarea = document.getElementById('reason');

    // Handle status selection
    statusRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            // Update submit button state
            submitBtn.disabled = false;
            
            // Auto-focus reason textarea
            setTimeout(() => {
                reasonTextarea.focus();
            }, 100);
        });
    });

    // Form validation
    form.addEventListener('submit', function(e) {
        const selectedStatus = document.querySelector('input[name="new_status"]:checked');
        
        if (!selectedStatus) {
            e.preventDefault();
            alert('Please select a new status.');
            return;
        }
        
        if (!reasonTextarea.value.trim()) {
            e.preventDefault();
            reasonTextarea.focus();
            alert('Please provide a reason for the status change.');
            return;
        }
        
        // Show loading state
        submitBtn.classList.add('loading');
        submitBtn.disabled = true;
    });

    // Add haptic feedback for mobile devices
    if ('vibrate' in navigator) {
        const interactiveElements = document.querySelectorAll('button, input[type="radio"], select, textarea');
        interactiveElements.forEach(element => {
            element.addEventListener('touchstart', function() {
                navigator.vibrate(10);
            });
        });
    }

    // Auto-resize textarea
    reasonTextarea.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = (this.scrollHeight) + 'px';
    });

    // Initialize submit button state
    submitBtn.disabled = true;
});
</script>
@endsection