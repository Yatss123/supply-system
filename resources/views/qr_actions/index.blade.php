@extends('layouts.app')

@section('content')
<div class="container-fluid px-2">
    <!-- Mobile-optimized header -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="fas fa-qrcode text-white fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="mb-1 fw-bold">{{ $supply->name }}</h5>
                            <p class="mb-0 text-muted small">
                                <i class="fas fa-box me-1"></i>{{ ucfirst($supply->supply_type) }} Supply
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Supply Information Card -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-3">
                    <!-- Stock Warning Alert -->
                    @if(isset($stockWarning))
                    <div class="alert alert-{{ $supply->quantity <= 0 ? 'danger' : 'warning' }} mb-3 py-2">
                        <i class="fas fa-{{ $supply->quantity <= 0 ? 'exclamation-triangle' : 'exclamation-circle' }} me-2"></i>
                        <strong>{{ $stockWarning }}</strong>
                    </div>
                    @endif
                    
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="text-center p-2 bg-light rounded">
                                <div class="fw-bold text-{{ $supply->isBorrowable() ? 'primary' : ($supply->quantity <= 0 ? 'danger' : ($supply->quantity <= $supply->minimum_stock_level ? 'warning' : 'primary')) }} fs-5">{{ $supply->quantity }}</div>
                                <small class="text-muted">Available</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center p-2 bg-light rounded">
                                <span class="badge bg-{{ $supply->status === 'active' ? 'success' : ($supply->status === 'inactive' ? 'secondary' : 'danger') }} fs-6">
                                    {{ ucfirst($supply->status) }}
                                </span>
                                <div><small class="text-muted">Status</small></div>
                            </div>
                        </div>
                    </div>
                    
                    @if($supply->hasVariants())
                    <div class="mt-3">
                        <small class="text-muted">
                            <i class="fas fa-layer-group me-1"></i>
                            This supply has {{ $supply->variants->count() }} variants
                        </small>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-3">
        <div class="col-12">
            <h6 class="fw-bold mb-3">
                <i class="fas fa-bolt me-2 text-warning"></i>Quick Actions
            </h6>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="row g-3">
        @forelse($availableActions as $actionKey => $action)
        <div class="col-12">
            @if(isset($action['disabled']) && $action['disabled'])
                <!-- Disabled Action -->
                <div class="btn btn-{{ $action['color'] }} btn-lg w-100 text-start d-flex align-items-center p-3 border-0 shadow-sm disabled opacity-50" 
                     style="cursor: not-allowed;">
                    <div class="me-3">
                        <i class="{{ $action['icon'] }} fs-3"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-bold">{{ $action['title'] }}</div>
                        <small class="opacity-75">{{ $action['description'] }}</small>
                    </div>
                    <div>
                        <i class="fas fa-ban"></i>
                    </div>
                </div>
            @else
                <!-- Active Action -->
                <a href="{{
                    $actionKey === 'quick_issue'
                        ? route('issued-items.create', ['supply_id' => $supply->id])
                        : (
                            $actionKey === 'quick_return'
                                ? route('qr.return', $supply)
                                : (
                                    $actionKey === 'view_borrowing_info'
                                        ? route('qr.borrowing-info', $supply)
                                        : (
                                            $actionKey === 'quick_status_change'
                                                ? route('qr.status-change', $supply)
                                                : route('qr.' . str_replace('_', '-', $actionKey), $supply)
                                          )
                                  )
                          )
                }}" 
                   class="btn btn-{{ $action['color'] }} btn-lg w-100 text-start d-flex align-items-center p-3 border-0 shadow-sm">
                    <div class="me-3">
                        <i class="{{ $action['icon'] }} fs-3"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-bold">{{ $action['title'] }}</div>
                        <small class="opacity-75">{{ $action['description'] }}</small>
                    </div>
                    <div>
                        <i class="fas fa-chevron-right"></i>
                    </div>
                </a>
            @endif
        </div>
        @empty
        <div class="col-12">
            <div class="card border-0 bg-light">
                <div class="card-body text-center p-4">
                    <i class="fas fa-info-circle fs-1 text-muted mb-3"></i>
                    <h6 class="text-muted">No Quick Actions Available</h6>
                    <p class="text-muted small mb-0">
                        No quick actions are available for this supply based on your role and the supply's current status.
                    </p>
                </div>
            </div>
        </div>
        @endforelse
    </div>

    <!-- Back to Supply Details -->
    <div class="row mt-4">
        <div class="col-12">
            <a href="{{ route('supplies.show', $supply) }}" class="btn btn-outline-secondary w-100">
                <i class="fas fa-arrow-left me-2"></i>View Full Supply Details
            </a>
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
    
    .btn-lg {
        font-size: 1rem;
        padding: 1rem 1.5rem;
    }
    
    .card {
        border-radius: 12px;
    }
    
    .btn {
        border-radius: 10px;
    }
    
    /* Touch-friendly spacing */
    .row.g-3 > * {
        padding-bottom: 0.75rem;
    }
    
    /* Improve tap targets */
    .btn {
        min-height: 48px;
    }
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
    .bg-light {
        background-color: #2d3748 !important;
        color: #e2e8f0 !important;
    }
    
    .text-muted {
        color: #a0aec0 !important;
    }
}

/* Accessibility improvements */
.btn:focus {
    box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.25);
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
</style>

<!-- Mobile-specific JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add loading states to buttons
    const actionButtons = document.querySelectorAll('.btn[href*="qr/"]');
    actionButtons.forEach(button => {
        button.addEventListener('click', function() {
            this.classList.add('loading');
            this.style.pointerEvents = 'none';
        });
    });
    
    // Handle back button for mobile
    if (window.history.length > 1) {
        const backButton = document.querySelector('.btn[href*="supplies.show"]');
        if (backButton) {
            backButton.addEventListener('click', function(e) {
                e.preventDefault();
                window.history.back();
            });
        }
    }
    
    // Add haptic feedback for mobile devices
    if ('vibrate' in navigator) {
        actionButtons.forEach(button => {
            button.addEventListener('touchstart', function() {
                navigator.vibrate(10);
            });
        });
    }
});
</script>
@endsection