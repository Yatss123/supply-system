@extends('layouts.app')

@push('styles')
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
@endpush

@push('scripts')
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
@endpush

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
                            <h5 class="mb-1 fw-bold">Quick Issue Item</h5>
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
                                <div class="fw-bold text-success fs-5">{{ $supply->issued_quantity ?? 0 }}</div>
                                <small class="text-muted">Issued</small>
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
                    
                    @if($supply->description)
                    <div class="mt-3">
                        <small class="text-muted">{{ $supply->description }}</small>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Issue Form -->
    <form id="quickIssueForm" action="{{ route('qr.process-issue', $supply) }}" method="POST">
        @csrf
        
        <!-- Variant Selection (if applicable) -->
        @if($supply->variants && $supply->variants->count() > 0)
        <div class="row mb-3">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-3">
                        <label class="form-label fw-bold">
                            <i class="fas fa-tags me-2"></i>Select Variant
                        </label>
                        <select name="supply_variant_id" class="form-select form-select-lg" required>
                            <option value="">Choose variant...</option>
                            @foreach($supply->variants as $variant)
                            <option value="{{ $variant->id }}" data-quantity="{{ $variant->quantity }}">
                                {{ $variant->display_name }} ({{ $variant->quantity }} available)
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Recipient Type -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-3">
                        <label class="form-label fw-bold">
                            <i class="fas fa-user-tag me-2"></i>Issue To
                        </label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="recipient_type" id="recipient_user" value="user" checked>
                            <label class="btn btn-outline-primary" for="recipient_user">
                                <i class="fas fa-user me-2"></i>User
                            </label>
                            
                            <input type="radio" class="btn-check" name="recipient_type" id="recipient_department" value="department">
                            <label class="btn btn-outline-primary" for="recipient_department">
                                <i class="fas fa-building me-2"></i>Department
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Department Filter (for user issuance) -->
        <div class="row mb-3" id="userDepartmentFilterRow">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-3">
                        <label class="form-label fw-bold">
                            <i class="fas fa-filter me-2"></i>Filter by Department
                        </label>
                        <select class="form-select form-select-lg" id="userDepartmentFilter">
                            <option value="">All departments</option>
                            @foreach($departments as $department)
                            <option value="{{ $department->id }}">{{ $department->name }}</option>
                            @endforeach
                        </select>
                        <div class="mt-2">
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Narrow the user list to a specific department
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- User Selection -->
        <div class="row mb-3" id="userSelection">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-3">
                        <label class="form-label fw-bold">
                            <i class="fas fa-user me-2"></i>Select User
                        </label>
                        <select name="user_id" class="form-select form-select-lg" id="userSelect">
                            <option value="">Choose user...</option>
                            @foreach($users as $user)
                            <option value="{{ $user->id }}" data-department-id="{{ $user->department_id ?? '' }}" data-department="{{ $user->department->name ?? 'N/A' }}">
                                {{ $user->name }} ({{ $user->department->name ?? 'N/A' }})
                            </option>
                            @endforeach
                        </select>
                        <div class="mt-2">
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Type to search for users by name or department; use the filter above to limit to one department
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Department Selection -->
        <div class="row mb-3" id="departmentSelection" style="display: none;">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-3">
                        <label class="form-label fw-bold">
                            <i class="fas fa-building me-2"></i>Select Department
                        </label>
                        <select name="department_id" class="form-select form-select-lg" id="departmentSelect">
                            <option value="">Choose department...</option>
                            @foreach($departments as $department)
                            <option value="{{ $department->id }}">{{ $department->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quantity -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-3">
                        <label class="form-label fw-bold">
                            <i class="fas fa-sort-numeric-up me-2"></i>Quantity
                        </label>
                        <div class="input-group input-group-lg">
                            <button type="button" class="btn btn-outline-secondary" id="decreaseQty">
                                <i class="fas fa-minus"></i>
                            </button>
                            <input type="number" name="quantity" class="form-control text-center" 
                                   id="quantityInput" value="1" min="1" max="{{ $supply->availableQuantity() }}" required>
                            <button type="button" class="btn btn-outline-secondary" id="increaseQty">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                        <div class="mt-2">
                            <small class="text-muted">
                                Maximum available: <span id="maxQuantity">{{ $supply->availableQuantity() }}</span>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Issue Date -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-3">
                        <label class="form-label fw-bold">
                            <i class="fas fa-calendar-day me-2"></i>Issue Date
                        </label>
                        <input type="date" name="issued_on" class="form-control form-control-lg" value="{{ now()->toDateString() }}" max="{{ now()->toDateString() }}" required>
                        <div class="form-text">
                            <small class="text-muted">Issue date cannot be in the future</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notes -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-3">
                        <label class="form-label fw-bold">
                            <i class="fas fa-clipboard-list me-2"></i>Notes (optional)
                        </label>
                        <textarea name="notes" class="form-control" rows="3" 
                                  placeholder="Optional: add context for why this item is issued..."></textarea>
                        <div class="form-text">
                            <small class="text-muted">Up to 1000 characters</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="row mb-4">
            <div class="col-12">
                <button type="submit" class="btn btn-primary btn-lg w-100" id="submitBtn">
                    <i class="fas fa-paper-plane me-2"></i>
                    <span id="submitText">Issue Item</span>
                    <div class="spinner-border spinner-border-sm ms-2 d-none" id="submitSpinner"></div>
                </button>
            </div>
        </div>
    </form>
</div>

<style>
/* Mobile-first responsive design */
@media (max-width: 768px) {
    .container-fluid {
        padding-left: 8px !important;
        padding-right: 8px !important;
    }
    
    .card {
        border-radius: 12px !important;
    }
    
    .btn-lg {
        padding: 12px 20px;
        font-size: 1.1rem;
    }
    
    .form-select-lg {
        padding: 12px 16px;
        font-size: 1rem;
    }
    
    .input-group-lg .form-control {
        padding: 12px 16px;
        font-size: 1.1rem;
    }
    
    /* Touch-friendly buttons */
    .btn {
        min-height: 44px;
    }
    
    /* Better spacing for mobile */
    .mb-3 {
        margin-bottom: 1rem !important;
    }
}

/* Loading states */
.btn:disabled {
    opacity: 0.6;
}

/* Haptic feedback simulation */
.btn:active {
    transform: scale(0.98);
    transition: transform 0.1s;
}

/* Form validation styles */
.is-invalid {
    border-color: #dc3545 !important;
}

.invalid-feedback {
    display: block;
    color: #dc3545;
    font-size: 0.875rem;
    margin-top: 0.25rem;
}

/* Select2 custom styles for mobile */
.select2-container--bootstrap-5 .select2-selection {
    min-height: 48px !important;
    padding: 8px 12px !important;
    font-size: 1rem !important;
}

.select2-container--bootstrap-5 .select2-selection__rendered {
    line-height: 32px !important;
}

.select2-container--bootstrap-5 .select2-selection__arrow {
    height: 46px !important;
}

.select2-dropdown {
    border-radius: 8px !important;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06) !important;
}

.select2-results__option {
    padding: 12px !important;
}

@media (max-width: 768px) {
    .select2-container--bootstrap-5 .select2-selection {
        min-height: 52px !important;
        font-size: 1.1rem !important;
    }
    
    .select2-container--bootstrap-5 .select2-selection__rendered {
        line-height: 36px !important;
    }
    
    .select2-container--bootstrap-5 .select2-selection__arrow {
        height: 50px !important;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('quickIssueForm');
    const submitBtn = document.getElementById('submitBtn');
    const submitText = document.getElementById('submitText');
    const submitSpinner = document.getElementById('submitSpinner');
    const quantityInput = document.getElementById('quantityInput');
    const decreaseBtn = document.getElementById('decreaseQty');
    const increaseBtn = document.getElementById('increaseQty');
    const maxQuantitySpan = document.getElementById('maxQuantity');
    const recipientTypeRadios = document.querySelectorAll('input[name="recipient_type"]');
    const userSelection = document.getElementById('userSelection');
    const departmentSelection = document.getElementById('departmentSelection');
    const userSelect = document.getElementById('userSelect');
    const departmentSelect = document.getElementById('departmentSelect');
    const userDepartmentFilter = document.getElementById('userDepartmentFilter');
    const userDepartmentFilterRow = document.getElementById('userDepartmentFilterRow');
    let selectedDepartmentId = userDepartmentFilter ? userDepartmentFilter.value : '';
    // Preserve the full list of users so we can rebuild options when filtering
    const allUserOptionsHTML = userSelect ? userSelect.innerHTML : '';
    // Use a relative path to avoid APP_URL/port mismatches in dev
    const usersByDeptBaseUrl = '/qr/departments';

    function applyDepartmentFilter() {
        const deptId = selectedDepartmentId;
        const options = userSelect.querySelectorAll('option');
        options.forEach(opt => {
            if (!opt.value) return; // keep placeholder
            const optDeptId = opt.getAttribute('data-department-id') || '';
            const disable = !!deptId && optDeptId !== deptId;
            opt.disabled = disable;
        });
        // Clear any selection that no longer matches
        if ($('#userSelect').val()) {
            const currentOption = userSelect.querySelector(`option[value="${$('#userSelect').val()}"]`);
            if (currentOption && currentOption.disabled) {
                $('#userSelect').val(null).trigger('change');
            }
        }
        // Refresh dropdown results
        $('#userSelect').trigger('change.select2');
    }
    const variantSelect = document.querySelector('select[name="supply_variant_id"]');

    function initUserSelect2() {
        $('#userSelect').select2({
            theme: 'bootstrap-5',
            placeholder: 'Search by name or department...',
            allowClear: true,
            width: '100%',
            matcher: function(params, data) {
                if ($.trim(params.term) === '' && !selectedDepartmentId) {
                    return data;
                }

                // Access underlying option element
                const optionEl = data.element;
                const deptId = optionEl ? optionEl.getAttribute('data-department-id') : '';
                const deptName = optionEl ? (optionEl.getAttribute('data-department') || '') : '';

                // Filter by selected department first
                if (selectedDepartmentId && deptId !== selectedDepartmentId) {
                    return null;
                }

                // Term search across text and department name
                if (params.term) {
                    const term = params.term.toLowerCase();
                    const text = (data.text || '').toLowerCase();
                    if (text.indexOf(term) > -1 || deptName.toLowerCase().indexOf(term) > -1) {
                        return data;
                    }
                    return null;
                }

                return data;
            },
            templateResult: function(user) {
                if (!user.id) {
                    return user.text;
                }
                const optionElement = document.querySelector(`option[value="${user.id}"]`);
                const department = optionElement ? optionElement.getAttribute('data-department') : 'N/A';
                const $result = $(
                    '<div class="d-flex justify-content-between align-items-center">' +
                        '<div>' +
                            '<div class="fw-bold">' + user.text.split(' (')[0] + '</div>' +
                            '<small class="text-muted">' + department + '</small>' +
                        '</div>' +
                    '</div>'
                );
                return $result;
            },
            templateSelection: function(user) {
                if (!user.id) {
                    return user.text;
                }
                return user.text.split(' (')[0];
            }
        });
    }

    async function rebuildUserOptionsForDept(deptId) {
        if (!userSelect) return;
        // If no department selected, restore full list
        if (!deptId) {
            userSelect.innerHTML = allUserOptionsHTML;
            return;
        }

        // Fetch users strictly for the selected department
        try {
            const endpoint = `${usersByDeptBaseUrl}/${deptId}/users`;
            // Clear current options immediately to prevent stale entries
            userSelect.innerHTML = '<option value="">Choose user...</option>';
            const response = await fetch(endpoint, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            if (!response.ok) {
                throw new Error('Failed to fetch users for department');
            }
            const data = await response.json();
            const users = Array.isArray(data.users) ? data.users : [];

            // Build options strictly from server response
            let optionsHTML = '<option value="">Choose user...</option>';
            users.forEach(u => {
                const deptName = u.department_name || 'N/A';
                const deptIdAttr = u.department_id ?? '';
                optionsHTML += `\n<option value="${u.id}" data-department-id="${deptIdAttr}" data-department="${deptName}">${u.name} (${deptName})</option>`;
            });
            userSelect.innerHTML = optionsHTML;
        } catch (err) {
            // Fallback: restore original and apply client-side filter
            userSelect.innerHTML = allUserOptionsHTML;
            // Remove any option that does not match the selected department
            $('#userSelect option').not(`[data-department-id="${deptId}"]`).not('[value=""]').remove();
        }
    }

    // Initialize on load
    rebuildUserOptionsForDept(selectedDepartmentId);
    initUserSelect2();

    // React to department filter changes
    if (userDepartmentFilter) {
        userDepartmentFilter.addEventListener('change', async function() {
            selectedDepartmentId = this.value;
            $('#userSelect').select2('destroy');
            // Clear any existing selection when filter changes
            $('#userSelect').val(null).trigger('change');
            await rebuildUserOptionsForDept(selectedDepartmentId);
            initUserSelect2();
        });
    }

    // Quantity controls
    decreaseBtn.addEventListener('click', function() {
        const currentValue = parseInt(quantityInput.value);
        if (currentValue > 1) {
            quantityInput.value = currentValue - 1;
            triggerHapticFeedback();
        }
    });

    increaseBtn.addEventListener('click', function() {
        const currentValue = parseInt(quantityInput.value);
        const maxValue = parseInt(quantityInput.max);
        if (currentValue < maxValue) {
            quantityInput.value = currentValue + 1;
            triggerHapticFeedback();
        }
    });

    // Recipient type toggle
    recipientTypeRadios.forEach(radio => {
        radio.addEventListener('change', async function() {
            if (this.value === 'user') {
                userSelection.style.display = 'block';
                userDepartmentFilterRow.style.display = 'block';
                departmentSelection.style.display = 'none';
                userSelect.required = true;
                departmentSelect.required = false;
                // Re-initialize Select2 when showing with strict department filter
                $('#userSelect').select2('destroy');
                // Clear selection to avoid stale cross-department user
                $('#userSelect').val(null);
                await rebuildUserOptionsForDept(selectedDepartmentId);
                initUserSelect2();
            } else {
                userSelection.style.display = 'none';
                userDepartmentFilterRow.style.display = 'none';
                departmentSelection.style.display = 'block';
                userSelect.required = false;
                departmentSelect.required = true;
                // Destroy Select2 when hiding to prevent issues
                $('#userSelect').select2('destroy');
            }
            triggerHapticFeedback();
        });
    });

    // Variant selection updates max quantity
    if (variantSelect) {
        variantSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption.dataset.quantity) {
                const maxQty = parseInt(selectedOption.dataset.quantity);
                quantityInput.max = maxQty;
                maxQuantitySpan.textContent = maxQty;
                
                // Reset quantity if it exceeds new max
                if (parseInt(quantityInput.value) > maxQty) {
                    quantityInput.value = Math.min(1, maxQty);
                }
            }
        });
    }

    // Form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Show loading state
        submitBtn.disabled = true;
        submitText.textContent = 'Processing...';
        submitSpinner.classList.remove('d-none');
        
        // Simulate haptic feedback
        triggerHapticFeedback();
        
        // Submit form
        const formData = new FormData(form);
        
        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Success feedback
                submitText.textContent = 'Success!';
                submitBtn.classList.remove('btn-primary');
                submitBtn.classList.add('btn-success');
                
                // Redirect after short delay
                setTimeout(() => {
                    window.location.href = "{{ route('qr.actions', $supply) }}";
                }, 1500);
            } else {
                throw new Error(data.error || 'An error occurred');
            }
        })
        .catch(error => {
            // Error handling
            submitText.textContent = 'Try Again';
            submitBtn.classList.remove('btn-primary');
            submitBtn.classList.add('btn-danger');
            
            // Show error message
            alert('Error: ' + error.message);
            
            // Reset button after delay
            setTimeout(() => {
                submitBtn.disabled = false;
                submitText.textContent = 'Issue Item';
                submitBtn.classList.remove('btn-danger');
                submitBtn.classList.add('btn-primary');
                submitSpinner.classList.add('d-none');
            }, 2000);
        });
    });

    // Haptic feedback simulation
    function triggerHapticFeedback() {
        if ('vibrate' in navigator) {
            navigator.vibrate(50);
        }
    }

    // Auto-resize textarea
    const textarea = document.querySelector('textarea[name="notes"]');
    if (textarea) {
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = this.scrollHeight + 'px';
        });
    }
});
</script>
@endsection