@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <!-- Header Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4 class="card-title mb-1">
                                <i class="fas fa-plus-circle text-success me-2"></i>
                                Quick Supply Request
                            </h4>
                            <p class="text-muted mb-0">Create a supply request for: <strong>{{ $supply->name }}</strong></p>
                        </div>
                        <div class="col-md-4 text-end">
                            <span class="badge bg-{{ $supply->status === 'active' ? 'success' : ($supply->status === 'inactive' ? 'danger' : 'warning') }} fs-6">
                                {{ ucfirst($supply->status) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Supply Information Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="fas fa-box me-1"></i>
                        Supply Information
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Name:</strong> {{ $supply->name }}</p>
                            <p><strong>Category:</strong> {{ $supply->categories->pluck('name')->join(', ') ?: 'No category' }}</p>
                            <p><strong>Type:</strong> {{ ucfirst($supply->supply_type) }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Current Stock:</strong> {{ $supply->quantity }}</p>
                            <p><strong>Unit:</strong> {{ $supply->unit }}</p>
                            @if($supply->description)
                            <p><strong>Description:</strong> {{ $supply->description }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Supply Request Form -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-edit me-1"></i>
                        Request Details
                    </h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('qr.process-supply-request', $supply) }}" method="POST" id="supplyRequestForm">
                        @csrf
                        
                        <div class="row">
                            <!-- Quantity -->
                            <div class="col-md-6 mb-3">
                                <label for="quantity" class="form-label">
                                    <i class="fas fa-hashtag me-1"></i>
                                    Quantity Requested <span class="text-danger">*</span>
                                </label>
                                <input type="number" 
                                       class="form-control @error('quantity') is-invalid @enderror" 
                                       id="quantity" 
                                       name="quantity" 
                                       value="{{ old('quantity', 1) }}" 
                                       min="1" 
                                       required>
                                @error('quantity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Priority -->
                            <div class="col-md-6 mb-3">
                                <label for="priority" class="form-label">
                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                    Priority <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('priority') is-invalid @enderror" 
                                        id="priority" 
                                        name="priority" 
                                        required>
                                    <option value="">Select Priority</option>
                                    <option value="low" {{ old('priority') === 'low' ? 'selected' : '' }}>Low</option>
                                    <option value="medium" {{ old('priority') === 'medium' ? 'selected' : '' }}>Medium</option>
                                    <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>High</option>
                                </select>
                                @error('priority')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Supply Variant (if available) -->
                        @if($supply->variants && $supply->variants->count() > 0)
                        <div class="mb-3">
                            <label for="supply_variant_id" class="form-label">
                                <i class="fas fa-list me-1"></i>
                                Variant (Optional)
                            </label>
                            <select class="form-select @error('supply_variant_id') is-invalid @enderror" 
                                    id="supply_variant_id" 
                                    name="supply_variant_id">
                                <option value="">No specific variant</option>
                                @foreach($supply->variants as $variant)
                                    <option value="{{ $variant->id }}" {{ old('supply_variant_id') == $variant->id ? 'selected' : '' }}>
                                        {{ $variant->name }} 
                                        @if($variant->description)
                                            - {{ $variant->description }}
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('supply_variant_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        @endif

                        <!-- Purpose -->
                        <div class="mb-4">
                            <label for="purpose" class="form-label">
                                <i class="fas fa-comment me-1"></i>
                                Purpose/Justification <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control @error('purpose') is-invalid @enderror" 
                                      id="purpose" 
                                      name="purpose" 
                                      rows="4" 
                                      placeholder="Please explain why you need this supply and how it will be used..."
                                      maxlength="500" 
                                      required>{{ old('purpose') }}</textarea>
                            <div class="form-text">
                                <span id="purposeCount">0</span>/500 characters
                            </div>
                            @error('purpose')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('qr.actions', $supply) }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i>
                                Back to QR Actions
                            </a>
                            <button type="submit" class="btn btn-success" id="submitBtn">
                                <i class="fas fa-paper-plane me-1"></i>
                                Submit Request
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Character counter for purpose field
    const purposeTextarea = document.getElementById('purpose');
    const purposeCount = document.getElementById('purposeCount');
    
    function updateCharacterCount() {
        const count = purposeTextarea.value.length;
        purposeCount.textContent = count;
        
        if (count > 450) {
            purposeCount.classList.add('text-warning');
        } else {
            purposeCount.classList.remove('text-warning');
        }
        
        if (count >= 500) {
            purposeCount.classList.add('text-danger');
            purposeCount.classList.remove('text-warning');
        } else {
            purposeCount.classList.remove('text-danger');
        }
    }
    
    purposeTextarea.addEventListener('input', updateCharacterCount);
    updateCharacterCount(); // Initial count
    
    // Auto-resize textarea
    purposeTextarea.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = (this.scrollHeight) + 'px';
    });
    
    // Form submission handling
    const form = document.getElementById('supplyRequestForm');
    const submitBtn = document.getElementById('submitBtn');
    
    form.addEventListener('submit', function(e) {
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Creating Request...';
        submitBtn.disabled = true;
    });
    
    // Haptic feedback for mobile devices
    if ('vibrate' in navigator) {
        const interactiveElements = document.querySelectorAll('button, input, select, textarea');
        interactiveElements.forEach(element => {
            element.addEventListener('touchstart', function() {
                navigator.vibrate(10);
            });
        });
    }
});
</script>
@endsection