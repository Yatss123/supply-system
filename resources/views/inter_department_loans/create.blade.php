@extends('layouts.app')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/inter-department-loans.css') }}">
@endsection

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-semibold">New Inter-Department Borrow Request</h1>
        <a href="{{ route('loan-requests.index', ['tab' => 'inter']) }}" class="text-blue-600 hover:underline">Back to Requests</a>
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

    <form method="POST" action="{{ route('loan-requests.store') }}" class="bg-white shadow rounded p-4 space-y-4" id="interDeptLoanForm">
        @csrf
        <input type="hidden" name="request_type" value="inter_department" />

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

        @if(auth()->user()->hasAdminPrivileges())
        <!-- Admin-Only Department Selection -->
        <div class="border-2 border-red-500 bg-red-50 rounded p-4">
            <div class="flex items-center mb-3">
                <span class="inline-flex items-center text-red-700 font-semibold">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 1.104-.896 2-2 2s-2-.896-2-2 .896-2 2-2 2 .896 2 2z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 21v-2a4 4 0 00-3-3.87M4 21v-2a4 4 0 013-3.87m9-7.26a4 4 0 10-8 0 4 4 0 008 0z" />
                    </svg>
                    Admin-Only: Department Selection
                </span>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="admin_lending_department_id" class="block text-sm font-medium text-gray-700">Lending Department (source)</label>
                    <select name="admin_lending_department_id" id="admin_lending_department_id" class="mt-1 block w-full border rounded px-3 py-2">
                        <option value="">Select Lending Department</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="admin_receiving_department_id" class="block text-sm font-medium text-gray-700">Receiving Department (destination)</label>
                    <select name="admin_receiving_department_id" id="admin_receiving_department_id" class="mt-1 block w-full border rounded px-3 py-2">
                        <option value="">Select Receiving Department</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <p id="admin_dept_warning" class="mt-2 text-sm text-red-700" style="display:none;">Lending and Receiving must be different.</p>
        </div>
        @endif

        <!-- Cart-style item selection (Issued Items from other departments) -->
        <div class="space-y-2">
            <label class="block text sm font-medium text-gray-700">Select Item</label>
            <input type="text" id="issued_item_search" class="mt-1 block w-full border rounded px-3 py-2" placeholder="Search items..." />

            <!-- Searchable list of top 5 matching items (click to select row) -->
            <div id="issued_item_list_container" class="mt-2">
                <ul id="issued_item_list" class="border rounded divide-y"></ul>
                <div id="issued_item_list_meta" class="text-xs text-gray-500 mt-1"></div>
            </div>
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
                <a href="{{ route('loan-requests.index', ['tab' => 'inter']) }}" class="px-3 py-2 bg-white border text-gray-800 rounded hover:bg-gray-50">Cancel</a>
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
            <button type="submit" id="submit_btn" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Submit Request</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const deptFilter = document.getElementById('department_filter');
    const searchInput = document.getElementById('issued_item_search');
    const listEl = document.getElementById('issued_item_list');
    const availableEl = document.getElementById('issued_available');
    const qtyInput = document.getElementById('issued_quantity');
    const addBtn = document.getElementById('add_issued_to_list');
    const clearBtn = document.getElementById('clear_cart');
    const cartBody = document.getElementById('cart_table_body');
    const emptyRow = document.getElementById('cart_empty_row');
    const totalEl = document.getElementById('running_total');
    const form = document.getElementById('interDeptLoanForm');
    const payloadInput = document.getElementById('items_payload');
    const startDate = document.getElementById('planned_start_date');
    const returnDate = document.getElementById('expected_return_date');
    const adminLending = document.getElementById('admin_lending_department_id');
    const adminReceiving = document.getElementById('admin_receiving_department_id');
    const adminWarning = document.getElementById('admin_dept_warning');
    const submitBtn = document.getElementById('submit_btn');
    const listMetaEl = document.getElementById('issued_item_list_meta');

    let cartItems = [];

    // Prepare data source for list rendering (compute in PHP, then JSON-encode)
    @php
        $itemsForJs = $availableItems->map(function($item){
            return [
                'id' => $item->id,
                'name' => $item->supply->name,
                // Use dynamic available quantity that accounts for active/overdue inter-department borrows
                'available' => (int) $item->availableQuantity,
                'dept_id' => $item->department->id,
                'dept_name' => $item->department->name,
            ];
        })->values()->toArray();
    @endphp
    const allItems = @json($itemsForJs);

    let selectedItemId = null;

    function renderList() {
        const term = (searchInput.value || '').toLowerCase();
        const deptId = deptFilter.value || '';
        // Filter by department and search term (name or dept name)
        const fullResults = allItems.filter(it => {
            const matchesDept = !deptId || String(it.dept_id) === String(deptId);
            const text = `${it.name} ${it.dept_name}`.toLowerCase();
            const matchesText = !term || text.includes(term);
            return matchesDept && matchesText;
        });
        const filtered = fullResults.slice(0, 5);

        listEl.innerHTML = '';
        if (!filtered.length) {
            const li = document.createElement('li');
            li.className = 'px-3 py-2 text-gray-500';
            li.textContent = 'No matching items';
            listEl.appendChild(li);
            if (listMetaEl) listMetaEl.textContent = '';
            return;
        }
        filtered.forEach(it => {
            const li = document.createElement('li');
            const unavailable = (parseInt(it.available || 0, 10) === 0);
            li.className = 'px-3 py-2 cursor-pointer hover:bg-blue-50';
            if (unavailable) {
                li.classList.add('opacity-60');
            }
            li.dataset.id = it.id;
            li.dataset.name = it.name;
            li.dataset.available = it.available;
            const statusHtml = unavailable
                ? '<span class="ml-2 inline-flex px-2 py-1 text-xs font-semibold rounded bg-gray-200 text-gray-700">Fully Borrowed</span>'
                : '';
            li.innerHTML = `<div class="flex items-center justify-between"><span class="font-medium text-gray-800">${it.name}</span><span class="text-xs text-gray-600">${it.dept_name} • Available: ${it.available} ${statusHtml}</span></div>`;
            if (String(selectedItemId) === String(it.id)) {
                li.classList.add('bg-blue-100', 'border', 'border-blue-300');
            }
            listEl.appendChild(li);
        });
        const more = Math.max(0, fullResults.length - filtered.length);
        if (listMetaEl) listMetaEl.textContent = more > 0 ? `Showing ${filtered.length} of ${fullResults.length} items` : `Showing ${filtered.length} items`;
    }
    function filterOptions() { renderList(); }
    if (deptFilter) deptFilter.addEventListener('change', filterOptions);
    if (searchInput) searchInput.addEventListener('input', filterOptions);
    renderList();

    function syncAdminDeptState() {
        if (!adminLending || !adminReceiving) return;
        const lend = adminLending.value;
        const recv = adminReceiving.value;
        const same = lend && recv && lend === recv;
        if (adminWarning) adminWarning.style.display = same ? '' : 'none';
        if (submitBtn) submitBtn.disabled = same;
    }

    if (adminLending) {
        adminLending.addEventListener('change', function() {
            if (deptFilter) {
                deptFilter.value = adminLending.value;
                filterOptions();
            }
            syncAdminDeptState();
        });
    }
    if (adminReceiving) {
        adminReceiving.addEventListener('change', syncAdminDeptState);
    }
    syncAdminDeptState();

    function updateAvailableDisplay() {
        const item = allItems.find(i => String(i.id) === String(selectedItemId));
        if (item) {
            const available = parseInt(item.available || 0, 10);
            if (available <= 0) {
                availableEl.textContent = 'Fully Borrowed';
                qtyInput.value = 0;
                qtyInput.max = 0;
                qtyInput.disabled = true;
                addBtn.disabled = true;
                addBtn.classList.add('opacity-60', 'cursor-not-allowed');
            } else {
                availableEl.textContent = available;
                qtyInput.max = available;
                qtyInput.disabled = false;
                addBtn.disabled = false;
                addBtn.classList.remove('opacity-60', 'cursor-not-allowed');
                if (!qtyInput.value || parseInt(qtyInput.value, 10) > available) {
                    qtyInput.value = 1;
                }
            }
        } else {
            availableEl.textContent = '—';
            qtyInput.value = '';
            qtyInput.max = '';
            qtyInput.disabled = false;
            addBtn.disabled = false;
            addBtn.classList.remove('opacity-60', 'cursor-not-allowed');
        }
    }
    updateAvailableDisplay();

    // Handle selection clicks (click row)
    listEl.addEventListener('click', function(e) {
        const li = e.target.closest('li[data-id]');
        if (!li) return;
        selectedItemId = li.dataset.id;
        updateAvailableDisplay();
        renderList();
    });

    function renderCart() {
        cartBody.innerHTML = '';
        if (!cartItems.length) {
            emptyRow.style.display = '';
        } else {
            emptyRow.style.display = 'none';
            cartItems.forEach((item, idx) => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td class=\"px-3 py-2 border\">${item.name}</td>
                    <td class=\"px-3 py-2 border\">${item.dept_name}</td>
                    <td class=\"px-3 py-2 border\">${item.available}</td>
                    <td class=\"px-3 py-2 border\">\n                        <input type=\"number\" min=\"1\" max=\"${item.available}\" value=\"${item.quantity}\" class=\"border rounded px-2 py-1 w-24\" data-idx=\"${idx}\" />\n                    </td>
                    <td class=\"px-3 py-2 border\">\n                        <button type=\"button\" class=\"px-2 py-1 bg-red-500 text-white rounded\" data-remove=\"${idx}\">Remove</button>\n                    </td>`;
                cartBody.appendChild(tr);
            });
        }
        totalEl.textContent = cartItems.length;
    }

    addBtn.addEventListener('click', function() {
        if (!selectedItemId) { alert('Please select an item from the list.'); return; }
        const item = allItems.find(i => String(i.id) === String(selectedItemId));
        if (!item) { alert('Selected item not found.'); return; }
        const available = parseInt(item.available || 0, 10);
        if (available <= 0) { alert('This item is fully borrowed and unavailable.'); return; }
        let qty = parseInt(qtyInput.value || '0', 10);
        if (!qty || qty < 1) qty = 1;
        if (qty > available) qty = available;
        const existing = cartItems.find(ci => String(ci.issued_item_id) === String(item.id));
        if (existing) {
            existing.quantity = Math.min(existing.quantity + qty, available);
        } else {
            cartItems.push({
                issued_item_id: item.id,
                name: item.name,
                available,
                dept_id: item.dept_id,
                dept_name: item.dept_name,
                quantity: qty
            });
        }
        renderCart();
    });

    cartBody.addEventListener('input', function(e) {
        const input = e.target;
        if (input.tagName.toLowerCase() !== 'input') return;
        const idx = parseInt(input.dataset.idx, 10);
        let val = parseInt(input.value || '0', 10);
        if (!Number.isFinite(idx)) return;
        const item = cartItems[idx];
        if (!item) return;
        if (val < 1) val = 1;
        if (val > item.available) val = item.available;
        item.quantity = val;
        renderCart();
    });

    cartBody.addEventListener('click', function(e) {
        const btn = e.target;
        if (btn.dataset.remove !== undefined) {
            const idx = parseInt(btn.dataset.remove, 10);
            cartItems.splice(idx, 1);
            renderCart();
        }
    });

    clearBtn.addEventListener('click', function() {
        cartItems = [];
        renderCart();
    });

    function setDateMins() {
        const today = new Date().toISOString().split('T')[0];
        if (startDate) startDate.min = today;
        const minForReturn = startDate && startDate.value ? startDate.value : today;
        if (returnDate) {
            returnDate.min = minForReturn;
            if (returnDate.value && returnDate.value < minForReturn) returnDate.value = minForReturn;
        }
    }
    setDateMins();
    if (startDate) startDate.addEventListener('change', setDateMins);

    form.addEventListener('submit', function(e) {
        // Admin-only validation: require different lending/receiving and matching item departments
        if (adminLending && adminReceiving) {
            const lend = adminLending.value;
            const recv = adminReceiving.value;
            if (!lend || !recv || lend === recv) {
                e.preventDefault();
                alert('Please select different lending and receiving departments.');
                return;
            }
            const mismatch = cartItems.some(ci => String(ci.dept_id) !== String(lend));
            if (mismatch) {
                e.preventDefault();
                alert('All items must be from the selected lending department.');
                return;
            }
        }
        if (!cartItems.length) {
            e.preventDefault();
            alert('Please add at least one item to the list.');
            return;
        }
        const payload = cartItems.map(ci => ({
            issued_item_id: ci.issued_item_id,
            quantity: ci.quantity
        }));
        payloadInput.value = JSON.stringify(payload);
    });
});
</script>
@endsection