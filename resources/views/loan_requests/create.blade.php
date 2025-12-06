@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-semibold">New Borrow Request</h1>
        <a href="{{ route('loan-requests.index', ['tab' => 'standard']) }}" class="text-blue-600 hover:underline">Back to Borrow Requests</a>
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

    <form method="POST" action="{{ route('loan-requests.store') }}" class="bg-white shadow rounded p-4 space-y-4" id="loanRequestForm">
        @csrf

        <!-- Cart-style item selection -->
        <div class="space-y-2">
            <label class="block text-sm font-medium text-gray-700">Select Item</label>
            <input type="text" id="supply_search" class="mt-1 block w-full border rounded px-3 py-2" placeholder="Search supplies..." />
            <div id="supply_list_container" class="mt-2">
                <ul id="supply_list" class="border rounded divide-y"></ul>
                <div id="supply_list_meta" class="text-xs text-gray-500 mt-1"></div>
            </div>
            <script id="supplies_json" type="application/json">{!! $supplies->map(function($s){ return [
                'id' => $s->id,
                'name' => $s->name,
                'available' => $s->availableQuantity(),
                'has_variants' => $s->hasVariants(),
                'unit' => $s->unit,
            ]; })->toJson(JSON_UNESCAPED_UNICODE) !!}</script>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 items-end">
                <div>
                    <label class="block text-sm text-gray-600">Available</label>
                    <div id="selected_available" class="mt-1 px-3 py-2 border rounded bg-gray-50">—</div>
                </div>
                <div>
                    <label for="selected_quantity" class="block text-sm font-medium text-gray-700">Desired Quantity</label>
                    <input id="selected_quantity" type="number" min="1" step="1" class="mt-1 block w-full border rounded px-3 py-2" placeholder="Enter quantity" />
                </div>
                <div class="pt-6">
                    <button type="button" id="add_to_list" class="w-full px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">Add to List</button>
                </div>
            </div>
            <!-- Variant selector (shown when selected item has variants) -->
            <div id="variant_selector" class="mt-3 hidden">
                <label for="variant_select" class="block text-sm font-medium text-gray-700">Variant</label>
                <select id="variant_select" class="mt-1 block w-full border rounded px-3 py-2">
                    <option value="">Select a variant</option>
                </select>
                <small id="variant_help" class="text-gray-500">Choose a specific variant for items that have multiple variants.</small>
            </div>
            <small class="text-gray-500">Add items one by one; quantities can be adjusted below before submitting.</small>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Department</label>
            @php($defaultDeptId = old('department_id', optional($currentUser)->department_id))
            @php($dept = isset($departments) ? $departments->firstWhere('id', $defaultDeptId) : null)
            @if($currentUser && method_exists($currentUser, 'hasAdminPrivileges') && $currentUser->hasAdminPrivileges())
                <select id="department_id" name="department_id" class="mt-1 block w-full border rounded px-3 py-2" required>
                    <option value="">Select a department</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}" {{ $defaultDeptId == $department->id ? 'selected' : '' }}>
                            {{ $department->department_name ?? $department->name }}
                        </option>
                    @endforeach
                </select>
            @else
                <input type="text" class="mt-1 block w-full border rounded px-3 py-2 bg-gray-100" value="{{ $dept->department_name ?? $dept->name ?? 'N/A' }}" disabled />
                <input type="hidden" name="department_id" value="{{ $defaultDeptId }}" />
            @endif
        </div>

        <!-- Temporary list / cart -->
        <div class="mt-4">
            <div class="flex items-center justify-between mb-2">
                <h2 class="text-lg font-semibold">Selected Items</h2>
                <div class="text-sm text-gray-600">Running total: <span id="running_total">0</span></div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm border">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-2 text-left border">Item</th>
                            <th class="px-3 py-2 text-left border">Available</th>
                            <th class="px-3 py-2 text-left border">Quantity</th>
                            <th class="px-3 py-2 text-left border">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="cart_table_body">
                        <tr id="cart_empty_row"><td colspan="4" class="px-3 py-3 text-center text-gray-500">No items added yet.</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="mt-3 flex gap-2">
                <button type="button" id="clear_cart" class="px-3 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300">Clear List</button>
                <a href="{{ route('loan-requests.index', ['tab' => 'standard']) }}" class="px-3 py-2 bg-white border text-gray-800 rounded hover:bg-gray-50">Cancel</a>
            </div>
        </div>

        <div>
            <label for="purpose" class="block text-sm font-medium text-gray-700">Purpose</label>
            <textarea id="purpose" name="purpose" rows="3" class="mt-1 block w-full border rounded px-3 py-2" placeholder="Describe the purpose of borrowing" required>{{ old('purpose') }}</textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="needed_from_date" class="block text-sm font-medium text-gray-700">Start Date (Needed From)</label>
                <input id="needed_from_date" name="needed_from_date" type="date" value="{{ old('needed_from_date') }}" class="mt-1 block w-full border rounded px-3 py-2" required />
                <small class="text-gray-500">You can request items in advance by choosing a future start date.</small>
            </div>
            <div>
                <label for="expected_return_date" class="block text-sm font-medium text-gray-700">Expected Return Date</label>
                <input id="expected_return_date" name="expected_return_date" type="date" value="{{ old('expected_return_date') }}" class="mt-1 block w-full border rounded px-3 py-2" required />
            </div>
        </div>

        <div class="pt-2">
            <input type="hidden" name="request" id="request_payload" />
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Proceed to Checkout</button>
        </div>
    </form>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // Date logic: needed_from_date min = today; expected_return_date min = needed_from_date
        const neededFrom = document.getElementById('needed_from_date');
        const expectedReturn = document.getElementById('expected_return_date');
        const todayStr = new Date().toISOString().split('T')[0];
        if (neededFrom) neededFrom.min = todayStr;
        function syncReturnMin() {
            if (expectedReturn && neededFrom && neededFrom.value) {
                expectedReturn.min = neededFrom.value;
                if (expectedReturn.value && expectedReturn.value < neededFrom.value) {
                    expectedReturn.value = neededFrom.value;
                }
            }
        }
        if (neededFrom) {
            neededFrom.addEventListener('change', syncReturnMin);
            // initialize
            syncReturnMin();
        }

        // Searchable list logic (show up to 5 items)
        const suppliesJsonEl = document.getElementById('supplies_json');
        const searchInput = document.getElementById('supply_search');
        const listEl = document.getElementById('supply_list');
        const listMetaEl = document.getElementById('supply_list_meta');
        const availableEl = document.getElementById('selected_available');
        const qtyInput = document.getElementById('selected_quantity');
        let supplies = [];
        try { supplies = JSON.parse(suppliesJsonEl?.textContent || '[]'); } catch (e) { supplies = []; }
        supplies = supplies.sort((a,b) => a.name.localeCompare(b.name));
        let filteredSupplies = supplies.slice();
        let selectedSupply = null;
        let selectedVariant = null;
        const variantSelectorEl = document.getElementById('variant_selector');
        const variantSelectEl = document.getElementById('variant_select');
        const VARIANTS_ENDPOINT_PREFIX = '{{ url('/issued-items/supply-variants') }}';

        function renderSupplyList() {
            listEl.innerHTML = '';
            const itemsToShow = filteredSupplies.slice(0, 5);
            if (itemsToShow.length === 0) {
                const li = document.createElement('li');
                li.className = 'px-3 py-2 text-gray-500';
                li.textContent = 'No matching items';
                listEl.appendChild(li);
                listMetaEl.textContent = '';
                return;
            }
            itemsToShow.forEach(s => {
                const li = document.createElement('li');
                li.className = 'px-3 py-2 cursor-pointer hover:bg-blue-50';
                li.setAttribute('data-id', s.id);
                li.setAttribute('data-name', s.name);
                li.setAttribute('data-available', s.available);
                li.innerHTML = `<div class="flex items-center justify-between"><span class="font-medium text-gray-800">${s.name}</span><span class="text-xs text-gray-600">Available: ${s.available}</span></div>`;
                li.addEventListener('click', function(){ onSupplySelected(s, li); });
                listEl.appendChild(li);
            });
            const more = Math.max(0, filteredSupplies.length - itemsToShow.length);
            listMetaEl.textContent = more > 0 ? `Showing ${itemsToShow.length} of ${filteredSupplies.length} items` : `Showing ${itemsToShow.length} items`;
        }

        function filterSupplies() {
            const q = (searchInput?.value || '').toLowerCase();
            filteredSupplies = supplies.filter(s => s.name.toLowerCase().includes(q));
            renderSupplyList();
        }

        async function onSupplySelected(s, liEl) {
            selectedSupply = s;
            selectedVariant = null;
            Array.from(listEl.children).forEach(el => el.classList.remove('bg-blue-100', 'border', 'border-blue-300'));
            if (liEl) liEl.classList.add('bg-blue-100', 'border', 'border-blue-300');
            // Update availability panel and quantity max
            if (s.has_variants) {
                // Load variants and show selector
                await loadVariantsForSupply(s.id);
                // Availability will update upon variant selection
                if (availableEl) availableEl.textContent = '—';
                if (qtyInput) { qtyInput.value = ''; qtyInput.removeAttribute('max'); }
            } else {
                if (availableEl) availableEl.textContent = s.available;
                if (qtyInput) { qtyInput.max = String(s.available); if (!qtyInput.value) qtyInput.value = 1; }
                hideVariantSelector();
            }
            // Expose selection to other scripts
            window.SuppliesUI = window.SuppliesUI || {};
            window.SuppliesUI.selectedSupply = selectedSupply;
            window.SuppliesUI.selectedVariant = selectedVariant;
            if (typeof window.updateSelectedSupplyUI === 'function') { window.updateSelectedSupplyUI(); }
        }

        function showVariantSelector() {
            if (variantSelectorEl) variantSelectorEl.classList.remove('hidden');
        }
        function hideVariantSelector() {
            if (variantSelectorEl) variantSelectorEl.classList.add('hidden');
            if (variantSelectEl) { variantSelectEl.innerHTML = '<option value="">Select a variant</option>'; }
        }

        async function loadVariantsForSupply(supplyId) {
            try {
                const resp = await fetch(`${VARIANTS_ENDPOINT_PREFIX}/${supplyId}`);
                const data = await resp.json();
                const variants = (data && data.variants) ? data.variants : [];
                if (variants.length) {
                    if (variantSelectEl) {
                        variantSelectEl.innerHTML = '<option value="">Select a variant</option>' + variants.map(v => {
                            const av = Number((v.available !== undefined ? v.available : v.quantity) || 0);
                            const disabled = av <= 0 ? 'disabled' : '';
                            const label = `${v.display_name} — Currently Available: ${av}${v.unit ? ' ' + v.unit : ''}`;
                            return `<option value="${v.id}" ${disabled} data-available="${av}" data-name="${label}">${label}</option>`;
                        }).join('');
                        variantSelectEl.value = '';
                    }
                    showVariantSelector();
                } else {
                    hideVariantSelector();
                }
            } catch (e) {
                hideVariantSelector();
            }
        }

        if (variantSelectEl) {
            variantSelectEl.addEventListener('change', function() {
                const opt = variantSelectEl.selectedOptions[0];
                if (!opt || !opt.value) { selectedVariant = null; availableEl.textContent = '—'; qtyInput.value = ''; qtyInput.removeAttribute('max'); return; }
                selectedVariant = {
                    id: Number(opt.value),
                    name: opt.dataset.name || 'Selected variant',
                    available: Number(opt.dataset.available || 0)
                };
                if (availableEl) availableEl.textContent = selectedVariant.available;
                if (qtyInput) { qtyInput.max = String(selectedVariant.available); if (!qtyInput.value) qtyInput.value = 1; }
                window.SuppliesUI = window.SuppliesUI || {};
                window.SuppliesUI.selectedVariant = selectedVariant;
            });
        }

        // Expose helper for other scripts
        window.SuppliesUI = window.SuppliesUI || {};
        window.SuppliesUI.supplies = supplies;
        window.SuppliesUI.filteredSupplies = filteredSupplies;
        window.SuppliesUI.renderSupplyList = renderSupplyList;
        window.SuppliesUI.filterSupplies = filterSupplies;
        window.SuppliesUI.onSupplySelected = onSupplySelected;
        window.SuppliesUI.loadVariantsForSupply = loadVariantsForSupply;

        if (searchInput) searchInput.addEventListener('input', filterSupplies);
        renderSupplyList();
    });
    </script>
    <script>
    // Cart UI logic
    document.addEventListener('DOMContentLoaded', function () {
        const availableEl = document.getElementById('selected_available');
        const qtyInput = document.getElementById('selected_quantity');
        const addBtn = document.getElementById('add_to_list');
        const cartBody = document.getElementById('cart_table_body');
        const emptyRow = document.getElementById('cart_empty_row');
        const runningTotalEl = document.getElementById('running_total');
        const form = document.getElementById('loanRequestForm');
        const requestPayloadEl = document.getElementById('request_payload');

        const CART_KEY = 'loanRequestCart';
        let cart = [];
        try {
            const saved = localStorage.getItem(CART_KEY);
            if (saved) cart = JSON.parse(saved) || [];
        } catch (e) {}

        function saveCart() { localStorage.setItem(CART_KEY, JSON.stringify(cart)); }
        function updateTotals() { runningTotalEl.textContent = cart.reduce((s, it) => s + Number(it.quantity || 0), 0); }
        function renderCart() {
            cartBody.innerHTML = '';
            if (!cart.length) { emptyRow.style.display = ''; }
            else {
                emptyRow.style.display = 'none';
                cart.forEach((item, idx) => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td class="px-3 py-2 border">${item.name}</td>
                        <td class="px-3 py-2 border">${item.available}</td>
                        <td class="px-3 py-2 border">
                            <input type="number" min="1" max="${item.available}" step="1" value="${item.quantity}" class="w-24 border rounded px-2 py-1" data-idx="${idx}" />
                        </td>
                        <td class="px-3 py-2 border">
                            <button type="button" class="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700" data-remove="${idx}">Remove</button>
                        </td>`;
                    cartBody.appendChild(tr);
                });
            }
            updateTotals();
        }

        function setAvailableFromSelection() {
            const s = (window.SuppliesUI && window.SuppliesUI.selectedSupply) ? window.SuppliesUI.selectedSupply : null;
            const available = s ? Number(s.available) : null;
            availableEl.textContent = available != null ? available : '—';
            if (available != null) { qtyInput.max = String(available); if (!qtyInput.value) qtyInput.value = 1; }
        }

        // Allow the list selection to update availability panel
        window.updateSelectedSupplyUI = setAvailableFromSelection;
        setAvailableFromSelection();

        addBtn.addEventListener('click', function () {
            const s = (window.SuppliesUI && window.SuppliesUI.selectedSupply) ? window.SuppliesUI.selectedSupply : null;
            const v = (window.SuppliesUI && window.SuppliesUI.selectedVariant) ? window.SuppliesUI.selectedVariant : null;
            if (!s) { alert('Please select an item from the list.'); return; }
            const supplyId = Number(s.id);
            const name = s.name;
            let available = Number(s.available || 0);
            const qty = Number(qtyInput.value || 0);
            if (!qty || qty < 1) { alert('Please enter a valid quantity.'); return; }
            // If the supply has variants, require a selected variant and use its availability
            if (s.has_variants) {
                if (!v || !v.id) { alert('Please select a variant for this item.'); return; }
                available = Number(v.available || 0);
                if (qty > available) { alert('Quantity exceeds available stock for the selected variant.'); return; }
                const existingIdx = cart.findIndex(it => Number(it.supply_id) === supplyId && Number(it.variant_id || 0) === Number(v.id));
                if (existingIdx >= 0) { cart[existingIdx].quantity = Math.min(available, Number(cart[existingIdx].quantity) + qty); }
                else { cart.push({ supply_id: supplyId, variant_id: Number(v.id), name: name + ' — ' + (v.name || 'Variant'), available, quantity: qty }); }
            } else {
                if (qty > available) { alert('Quantity exceeds available stock.'); return; }
                const existingIdx = cart.findIndex(it => Number(it.supply_id) === supplyId && !it.variant_id);
                if (existingIdx >= 0) { cart[existingIdx].quantity = Math.min(available, Number(cart[existingIdx].quantity) + qty); }
                else { cart.push({ supply_id: supplyId, name, available, quantity: qty }); }
            }
            saveCart(); renderCart();
        });

        cartBody.addEventListener('input', function (e) {
            const target = e.target;
            if (target && target.type === 'number' && target.dataset.idx != null) {
                const idx = Number(target.dataset.idx);
                let val = Number(target.value || 0);
                const max = Number(target.max || 0);
                if (val < 1) val = 1; if (max && val > max) val = max; target.value = val;
                cart[idx].quantity = val; saveCart(); updateTotals();
            }
        });

        cartBody.addEventListener('click', function (e) {
            const btn = e.target.closest('button[data-remove]'); if (!btn) return;
            const idx = Number(btn.dataset.remove); cart.splice(idx, 1); saveCart(); renderCart();
        });

        document.getElementById('clear_cart').addEventListener('click', function () {
            cart = []; saveCart(); renderCart();
        });

        form.addEventListener('submit', function (e) {
            if (!cart.length) {
                alert('Please add at least one item to your request.');
                e.preventDefault();
                return;
            }
            const payload = cart.map(it => {
                const item = { supply_id: Number(it.supply_id), quantity: Number(it.quantity) };
                if (it.variant_id) item.variant_id = Number(it.variant_id);
                return item;
            });
            requestPayloadEl.value = JSON.stringify(payload);
        });

        renderCart();
    });
    </script>
</div>
@endsection