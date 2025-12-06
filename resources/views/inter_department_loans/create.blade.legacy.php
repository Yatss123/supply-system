@extends('layouts.app')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/inter-department-loans.css') }}">
@endsection

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-semibold">New Inter-Department Loan Request</h1>
        <a href="{{ route('inter-department-loans.index') }}" class="text-blue-600 hover:underline">Back to Requests</a>
    </div>

    @if ($errors->any())
        <div class="mb-4 bg-red-50 border border-red-200 text-red-700 rounded p-3">
            <ul class="list-disc list-inside text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('inter-department-loans.store') }}" class="bg-white shadow rounded p-4 space-y-4" id="interDeptLoanForm">
        @csrf

        <!-- Lending Department Filter -->
        <div>
            <label for="department_filter" class="block text-sm font-medium text-gray-700">Lending Department Filter</label>
            <select id="department_filter" class="mt-1 block w-full border rounded px-3 py-2">
                <option value="">All departments</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                @endforeach
            </select>
        </div>

        <!-- Cart-style item selection (Issued Items from other departments) -->
        <div class="space-y-2">
            <label class="block text-sm font-medium text-gray-700">Select Item (from other departments)</label>
            <input type="text" id="issued_item_search" class="mt-1 block w-full border rounded px-3 py-2" placeholder="Search items..." />
            <select id="issued_item_select" class="mt-2 block w-full border rounded px-3 py-2">
                <option value="">Select an available item</option>
                @foreach($availableItems as $item)
                    <option value="{{ $item->id }}"
                            data-name="{{ $item->supply->name }}"
                            data-available="{{ $item->quantity }}"
                            data-dept-id="{{ $item->department->id }}"
                            data-dept-name="{{ $item->department->name }}">
                        {{ $item->supply->name }} — {{ $item->department->name }} (Qty: {{ $item->quantity }})
                    </option>
                @endforeach
            </select>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 items-end">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Available</label>
                    <div id="issued_available" class="mt-1 px-3 py-2 border rounded bg-gray-50">—</div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Quantity</label>
                    <input type="number" id="issued_quantity" min="1" class="mt-1 block w-full border rounded px-3 py-2" placeholder="Enter quantity" />
                </div>
                <div class="flex gap-2 md:justify-end">
                    <button type="button" id="add_issued_to_list" class="px-3 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Add to List</button>
                    <span class="inline-block px-3 py-2 text-sm text-gray-600">Total Items: <span id="running_total">0</span></span>
                </div>
            </div>

            <div class="mt-3">
                <table class="min-w-full text-sm border">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-2 text-left border">Item</th>
                            <th class="px-3 py-2 text-left border">Lending Dept</th>
                            <th class="px-3 py-2 text-left border">Available</th>
                            <th class="px-3 py-2 text-left border">Quantity</th>
                            <th class="px-3 py-2 text-left border">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="cart_table_body">
                        <tr id="cart_empty_row"><td colspan="5" class="px-3 py-3 text-center text-gray-500">No items added yet.</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="mt-3 flex gap-2">
                <button type="button" id="clear_cart" class="px-3 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300">Clear List</button>
                <a href="{{ route('inter-department-loans.index') }}" class="px-3 py-2 bg-white border text-gray-800 rounded hover:bg-gray-50">Cancel</a>
            </div>
        </div>

        <div>
            <label for="purpose" class="block text-sm font-medium text-gray-700">Purpose</label>
            <textarea id="purpose" name="purpose" rows="3" class="mt-1 block w-full border rounded px-3 py-2" placeholder="Describe the purpose of this inter-department request" required>{{ old('purpose') }}</textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="planned_start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                <input id="planned_start_date" name="planned_start_date" type="date" value="{{ old('planned_start_date') }}" class="mt-1 block w-full border rounded px-3 py-2" required />
                <small class="text-gray-500">Choose when borrowing should begin.</small>
            </div>
            <div>
                <label for="expected_return_date" class="block text-sm font-medium text-gray-700">Expected Return Date</label>
                <input id="expected_return_date" name="expected_return_date" type="date" value="{{ old('expected_return_date') }}" class="mt-1 block w-full border rounded px-3 py-2" required />
            </div>
        </div>

        <div>
            <label for="notes" class="block text-sm font-medium text-gray-700">Additional Notes (optional)</label>
            <textarea id="notes" name="notes" rows="3" class="mt-1 block w-full border rounded px-3 py-2" placeholder="Any extra information or special requirements">{{ old('notes') }}</textarea>
        </div>

        <input type="hidden" name="items_payload" id="items_payload" />

        <div class="pt-2">
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Submit Request</button>
        </div>
    </form>

                        <!-- Available Items Selection -->
                        <div class="mb-4">
                            <label for="issued_item_id" class="form-label fw-semibold">
                                <i class="fas fa-box me-2 text-info"></i>Available Item <span class="text-danger">*</span>
                            </label>
                            <select name="issued_item_id" id="issued_item_id" class="form-select form-select-lg @error('issued_item_id') is-invalid @enderror" required data-bs-toggle="tooltip" title="Select the item you want to borrow">
                                <option value="">Select an available item...</option>
                                @foreach($availableItems as $item)
                                    <option value="{{ $item->id }}" 
                                            data-quantity="{{ $item->quantity }}"
                                            {{ old('issued_item_id') == $item->id ? 'selected' : '' }}
                                            data-bs-toggle="tooltip" 
                                            title="Available: {{ $item->quantity }} units from {{ $item->department->name }}">
                                        {{ $item->supply->name }} - {{ $item->department->name }} (Qty: {{ $item->quantity }})
                                    </option>
                                @endforeach
                            </select>
                            @error('issued_item_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Quantity -->
                        <div class="mb-4">
                            <label for="quantity_requested" class="form-label fw-semibold">
                                <i class="fas fa-sort-numeric-up me-2 text-success"></i>Quantity Requested <span class="text-danger">*</span>
                            </label>
                            <input type="number" 
                                   name="quantity_requested" 
                                   id="quantity_requested" 
                                   class="form-control form-control-lg @error('quantity_requested') is-invalid @enderror" 
                                   value="{{ old('quantity_requested') }}" 
                                   min="1" 
                                   placeholder="Enter quantity needed"
                                   data-bs-toggle="tooltip" 
                                   title="Enter the number of items you need"
                                   required>
                            @error('quantity_requested')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Expected Return Date -->
                        <div class="mb-4">
                            <label for="expected_return_date" class="form-label fw-semibold">
                                <i class="fas fa-calendar-alt me-2 text-warning"></i>Expected Return Date <span class="text-danger">*</span>
                            </label>
                            <input type="date" 
                                   name="expected_return_date" 
                                   id="expected_return_date" 
                                   class="form-control form-control-lg @error('expected_return_date') is-invalid @enderror" 
                                   value="{{ old('expected_return_date') }}" 
                                   min="{{ date('Y-m-d') }}" 
                                   required>
                            @error('expected_return_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Purpose -->
                        <div class="mb-4">
                            <label for="purpose" class="form-label fw-semibold">
                                <i class="fas fa-clipboard-list me-2 text-primary"></i>Purpose <span class="text-danger">*</span>
                            </label>
                            <textarea name="purpose" 
                                      id="purpose" 
                                      class="form-control @error('purpose') is-invalid @enderror" 
                                      rows="4" 
                                      placeholder="Describe the purpose of this loan request in detail..." 
                                      required>{{ old('purpose') }}</textarea>
                            @error('purpose')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Multi-Item Selection (Optional) -->
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label fw-semibold mb-0">
                                    <i class="fas fa-list me-2 text-info"></i>Multi-Item Selection (Optional)
                                </label>
                                <small class="text-muted">Use checkboxes to select multiple items; quantities per row.</small>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle" id="multiItemTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width:50px">Select</th>
                                            <th>Item</th>
                                            <th>Department</th>
                                            <th class="text-center" style="width:140px">Available</th>
                                            <th style="width:180px">Request Qty</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($availableItems as $item)
                                            <tr data-dept-id="{{ $item->department->id }}">
                                                <td>
                                                    <input type="checkbox" class="form-check-input item-checkbox" data-item-id="{{ $item->id }}">
                                                </td>
                                                <td>
                                                    <div class="fw-semibold">{{ $item->supply->name }}</div>
                                                    <small class="text-muted">SKU: {{ $item->supply->sku ?? 'N/A' }}</small>
                                                </td>
                                                <td>{{ $item->department->name }}</td>
                                                <td class="text-center">
                                                    <span class="badge bg-info">{{ $item->quantity }}</span>
                                                </td>
                                                <td>
                                                    <div class="input-group">
                                                        <input type="number" class="form-control form-control-sm item-qty" min="1" max="{{ $item->quantity }}" placeholder="Qty" data-available="{{ $item->quantity }}">
                                                        <span class="input-group-text">/ {{ $item->quantity }}</span>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Selected Items Summary -->
                            <div class="mt-3 p-3 border rounded">
                                <h6 class="fw-bold mb-2"><i class="fas fa-check me-2 text-success"></i>Selected Items Summary</h6>
                                <div id="selectedItemsSummary" class="small text-muted">No items selected.</div>
                            </div>

                            <input type="hidden" name="items_payload" id="items_payload" />
                        </div>

                        <!-- Additional Notes -->
                        <div class="mb-4">
                            <label for="notes" class="form-label fw-semibold">
                                <i class="fas fa-sticky-note me-2 text-secondary"></i>Additional Notes
                            </label>
                            <textarea name="notes" 
                                      id="notes" 
                                      class="form-control @error('notes') is-invalid @enderror" 
                                      rows="3" 
                                      placeholder="Any additional information or special requirements...">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Form Actions -->
                        <div class="d-flex gap-3 pt-3 border-top">
                            <a href="{{ route('inter-department-loans.index') }}" 
                               class="btn btn-outline-secondary btn-lg flex-fill"
                               data-bs-toggle="tooltip" 
                               title="Return to borrow requests list">
                                <i class="fas fa-times me-2"></i>Cancel
                            </a>
                            <button type="submit" 
                                    class="btn btn-primary btn-lg flex-fill"
                                    data-bs-toggle="tooltip" 
                                    title="Submit your borrow request for approval">
                                <i class="fas fa-paper-plane me-2"></i>Submit Request
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Information Sidebar -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-info-circle me-2"></i>Important Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <h6 class="fw-bold text-primary mb-3">
                            <i class="fas fa-check-circle me-2"></i>Approval Process
                        </h6>
                        <div class="timeline">
                            <div class="timeline-item">
                                <div class="timeline-marker bg-primary"></div>
                                <div class="timeline-content">
                                    <h6 class="text-primary">Approved by Borrowing Department Dean</h6>
                                    <small class="text-muted">Your request will first require dean approval</small>
                                </div>
                            </div>
                            <div class="timeline-item">
                                <div class="timeline-marker bg-info"></div>
                                <div class="timeline-content">
                                    <h6 class="text-info">Approved by Lending Department Dean</h6>
                                    <small class="text-muted">Then reviewed by the lending department</small>
                                </div>
                            </div>
                            <div class="timeline-item">
                                <div class="timeline-marker bg-success"></div>
                                <div class="timeline-content">
                                    <h6 class="text-success">Admin Approval</h6>
                                    <small class="text-muted">Finally requires admin approval</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-bell me-2"></i>
                        <small>You will be notified at each step of the process</small>
                    </div>
                    
                    <div>
                        <h6 class="fw-bold text-warning mb-3">
                            <i class="fas fa-exclamation-triangle me-2"></i>Requirements
                        </h6>
                        <ul class="list-unstyled">
                            <li class="mb-2">
                                <i class="fas fa-asterisk text-danger me-2" style="font-size: 0.6rem;"></i>
                                <small>All fields marked with * are required</small>
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-calendar text-warning me-2"></i>
                                <small>Expected return date must be in the future</small>
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-sort-numeric-up text-success me-2"></i>
                                <small>Quantity must be available in the lending department</small>
                            </li>
                            <li class="mb-0">
                                <i class="fas fa-clipboard-list text-primary me-2"></i>
                                <small>Purpose must clearly explain the need for the loan</small>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Update quantity display when item is selected
document.getElementById('issued_item_id').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const quantityInput = document.getElementById('quantity_requested');
    
    if (selectedOption.value) {
        const availableQty = selectedOption.dataset.quantity || 0;
        quantityInput.max = availableQty;
        quantityInput.placeholder = `Max available: ${availableQty}`;
    } else {
        quantityInput.max = '';
        quantityInput.placeholder = '';
    }
});

// Set minimum date to today
document.addEventListener('DOMContentLoaded', function() {
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('expected_return_date').setAttribute('min', today);
    
    // Initialize Bootstrap tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>

<script>
// Multi-select, filtering, validation, and summary logic
document.addEventListener('DOMContentLoaded', function() {
    const deptFilter = document.getElementById('department_filter');
    const table = document.getElementById('multiItemTable');
    const summaryEl = document.getElementById('selectedItemsSummary');
    const form = document.getElementById('interDeptLoanForm');
    const itemsPayloadInput = document.getElementById('items_payload');
    const singleSelect = document.getElementById('issued_item_id');
    const singleQty = document.getElementById('quantity_requested');
    const startDate = document.getElementById('planned_start_date');
    const returnDate = document.getElementById('expected_return_date');

    function updateReturnMin() {
        if (startDate && returnDate && startDate.value) {
            returnDate.min = startDate.value;
            if (returnDate.value && returnDate.value < startDate.value) {
                returnDate.value = startDate.value;
            }
        }
    }
    if (startDate) {
        startDate.addEventListener('change', updateReturnMin);
        updateReturnMin();
    }

    function updateRowState(row, checked) {
        const qtyInput = row.querySelector('.item-qty');
        if (checked) {
            row.classList.add('table-active');
            qtyInput.disabled = false;
            qtyInput.focus();
        } else {
            row.classList.remove('table-active');
            qtyInput.disabled = true;
            qtyInput.value = '';
        }
    }

    // Initialize all qty inputs disabled
    table.querySelectorAll('tbody tr').forEach(row => {
        updateRowState(row, false);
        const checkbox = row.querySelector('.item-checkbox');
        checkbox.addEventListener('change', () => updateRowState(row, checkbox.checked));
    });

    // Filter by department
    if (deptFilter) {
        deptFilter.addEventListener('change', function() {
            const filterVal = this.value;
            table.querySelectorAll('tbody tr').forEach(row => {
                const deptId = row.getAttribute('data-dept-id');
                row.style.display = (!filterVal || filterVal === deptId) ? '' : 'none';
            });
        });
    }

    function buildSummaryAndPayload() {
        const selections = [];
        table.querySelectorAll('tbody tr').forEach(row => {
            const checkbox = row.querySelector('.item-checkbox');
            if (!checkbox.checked) return;
            const qtyInput = row.querySelector('.item-qty');
            const available = parseInt(qtyInput.getAttribute('data-available'), 10) || 0;
            const qty = parseInt(qtyInput.value, 10) || 0;
            if (qty <= 0 || qty > available) return;
            const itemId = checkbox.getAttribute('data-item-id');
            const name = row.querySelector('td:nth-child(2) .fw-semibold').textContent.trim();
            const dept = row.querySelector('td:nth-child(3)').textContent.trim();
            selections.push({ issued_item_id: itemId, quantity: qty, name, dept });
        });

        if (selections.length === 0) {
            summaryEl.textContent = 'No items selected.';
            itemsPayloadInput.value = '';
        } else {
            itemsPayloadInput.value = JSON.stringify(selections.map(s => ({ issued_item_id: s.issued_item_id, quantity: s.quantity })));
            summaryEl.innerHTML = selections.map(s => `${s.name} — ${s.dept} — Qty: <span class="fw-semibold">${s.quantity}</span>`).join('<br>');
        }
        return selections;
    }

    // Live summary update
    table.addEventListener('input', function(e) {
        if (e.target.classList.contains('item-qty')) {
            const available = parseInt(e.target.getAttribute('data-available'), 10) || 0;
            if (parseInt(e.target.value, 10) > available) {
                e.target.value = available;
            }
            buildSummaryAndPayload();
        }
    });
    table.addEventListener('change', function(e) {
        if (e.target.classList.contains('item-checkbox')) {
            buildSummaryAndPayload();
        }
    });

    // Form submit validation and payload assembly
    form.addEventListener('submit', function(e) {
        const selections = buildSummaryAndPayload();
        const usingMulti = selections.length > 0;

        if (usingMulti) {
            // Disable single-select requirements
            singleSelect.removeAttribute('required');
            singleQty.removeAttribute('required');
            // Basic validation: ensure start date and return date are valid
            if (!startDate.value) {
                e.preventDefault();
                alert('Please select a Start Date.');
                startDate.focus();
                return;
            }
            if (!returnDate.value) {
                e.preventDefault();
                alert('Please select an Expected Return Date.');
                returnDate.focus();
                return;
            }
            if (returnDate.value < startDate.value) {
                e.preventDefault();
                alert('Expected Return Date cannot be earlier than Start Date.');
                return;
            }
        } else {
            // Enforce single-select validation
            singleSelect.setAttribute('required', 'required');
            singleQty.setAttribute('required', 'required');
            itemsPayloadInput.value = '';
            const selectedOption = singleSelect.options[singleSelect.selectedIndex];
            const availableQty = selectedOption && (selectedOption.dataset.quantity || 0);
            if (availableQty && singleQty.value && parseInt(singleQty.value, 10) > parseInt(availableQty, 10)) {
                e.preventDefault();
                alert('Requested quantity exceeds available quantity for the selected item.');
                singleQty.focus();
                return;
            }
        }
    });
});
</script>
@endsection