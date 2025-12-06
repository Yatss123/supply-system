@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-semibold">New Issuance</h1>
        <a href="{{ route('issued-items.index') }}" class="text-blue-600 hover:underline">Back to Issued Items</a>
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

    <form method="POST" action="{{ route('issued-items.store') }}" class="bg-white shadow rounded p-4 space-y-4" id="issuedItemForm">
        @csrf

        <!-- Cart-style selection layout, aligned with loan requests -->
        <div class="space-y-2">
            <label class="block text-sm font-medium text-gray-700">Select Item</label>
            <input type="text" id="supply_search" class="mt-1 block w-full border rounded px-3 py-2" placeholder="Search supplies..." />
            <div class="mt-2">
                <ul id="supply_list" class="border rounded divide-y"></ul>
                <div id="supply_list_meta" class="text-xs text-gray-500 mt-1"></div>
            </div>
            <script id="supplies_json" type="application/json">{!! $supplies->map(function($s){ return [
                'id' => $s->id,
                'name' => $s->name,
                'sku' => $s->sku ?? '',
                'available' => method_exists($s, 'availableQuantity') ? $s->availableQuantity() : ($s->quantity ?? 0),
                'has_variants' => method_exists($s, 'hasVariants') ? $s->hasVariants() : ($s->variants && $s->variants->count() > 0),
                'unit' => $s->unit,
            ]; })->toJson(JSON_UNESCAPED_UNICODE) !!}</script>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 items-end">
                <div>
                    <label class="block text-sm text-gray-600">Available</label>
                    <div id="selected_available" class="mt-1 px-3 py-2 border rounded bg-gray-50">—</div>
                </div>
                <div>
                    <label for="selected_quantity" class="block text-sm font-medium text-gray-700">Quantity to Issue</label>
                    <input id="selected_quantity" name="quantity" type="number" min="1" step="1" class="mt-1 block w-full border rounded px-3 py-2" placeholder="Enter quantity" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Manual QR/SKU/ID</label>
                    <div class="flex gap-2">
                        <input type="text" id="qr_manual" class="mt-1 block w-full border rounded px-3 py-2" placeholder="Scan or paste QR/SKU/ID" />
                        <button type="button" id="qr_use" class="px-3 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300">Use</button>
                    </div>
                    <div class="mt-2">
                        <button type="button" id="qr_scan" class="px-3 py-2 bg-blue-100 text-blue-800 rounded hover:bg-blue-200">Scan QR</button>
                    </div>
                    <div id="qr_camera" class="mt-2 border rounded p-2 hidden">
                        <p id="qr_status" class="text-xs text-gray-600">Requesting camera permission…</p>
                        <video id="qr_video" class="w-full max-h-64 bg-black" autoplay playsinline></video>
                        <div class="mt-2 flex gap-2">
                            <button type="button" id="qr_stop" class="px-3 py-2 bg-red-100 text-red-800 rounded hover:bg-red-200">Stop camera</button>
                        </div>
                    </div>
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

            <!-- Variant selection modal -->
            <div id="variant_modal_overlay" class="fixed inset-0 bg-black bg-opacity-40 hidden items-center justify-center z-50">
                <div class="bg-white rounded shadow-lg w-full max-w-2xl">
                    <div class="px-4 py-3 border-b flex items-center justify-between">
                        <h2 class="text-lg font-semibold">Select Variants</h2>
                        <button type="button" id="variant_modal_close" class="text-gray-500 hover:text-gray-700" aria-label="Close">&times;</button>
                    </div>
                    <div class="p-4">
                        <p class="text-sm text-gray-600 mb-3">Choose variant(s) and set quantities to add to the list.</p>
                        <div id="variant_modal_supply_name" class="text-sm font-medium text-gray-800 mb-2"></div>
                        <div id="variant_modal_list" class="space-y-2 max-h-64 overflow-auto"></div>
                        <small class="text-gray-500">Only variants with available stock can be selected.</small>
                    </div>
                    <div class="px-4 py-3 border-t flex gap-2 justify-end">
                        <button type="button" id="variant_modal_cancel" class="px-3 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300">Cancel</button>
                        <button type="button" id="variant_modal_done" class="px-3 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Done</button>
                    </div>
                </div>
            </div>
            <input type="hidden" name="supply_id" id="supply_id" />
            <input type="hidden" name="supply_variant_id" id="supply_variant_id" />
            <small class="text-gray-500">Select an item and set quantity. Variants are optional unless availability requires a specific variant.</small>

            <!-- Add to list for items -->
            <div class="mt-3 flex gap-2 md:justify-start">
                <button type="button" id="add_item_btn" class="px-3 py-2 bg-green-600 text-white rounded hover:bg-green-700">Add Item to List</button>
            </div>
            <div class="mt-2">
                <h3 class="text-sm font-medium text-gray-700">Saved Items</h3>
                <ul id="saved_items_list" class="border rounded divide-y"></ul>
            </div>
        </div>

        <div>
            @php($currentUser = auth()->user())
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
                <input type="hidden" name="department_id" id="department_id" value="{{ $defaultDeptId }}" />
            @endif
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Recipient (Optional)</label>
            <input type="text" id="recipient_search" class="mt-1 block w-full border rounded px-3 py-2" placeholder="Search recipients..." />
            <div class="mt-2">
                <ul id="recipient_list" class="border rounded divide-y"></ul>
                <div id="recipient_list_meta" class="text-xs text-gray-500 mt-1"></div>
            </div>
            <div class="mt-2 flex gap-2 items-center">
                <input type="hidden" name="user_id" id="user_id" />
                <button type="button" id="recipient_clear" class="px-3 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300">Clear Recipient</button>
            </div>
            <!-- Single-recipient selection only; saved recipients removed -->
            <script id="users_json" type="application/json">{!! $users->map(function($u){ return [
                'id' => $u->id,
                'name' => $u->name,
                'department_id' => $u->department_id,
                'department_name' => optional($u->department)->department_name ?? 'No Dept',
            ]; })->toJson(JSON_UNESCAPED_UNICODE) !!}</script>
            <small class="text-gray-500">Recipient must belong to the chosen department. Selecting a recipient auto-sets the department.</small>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="issued_on" class="block text-sm font-medium text-gray-700">Issue Date</label>
                <input id="issued_on" name="issued_on" type="date" value="{{ old('issued_on', date('Y-m-d')) }}" max="{{ date('Y-m-d') }}" class="mt-1 block w-full border rounded px-3 py-2 bg-gray-100" required readonly />
                <small class="text-gray-500">Date must be today or earlier.</small>
            </div>
            <div>
                <label for="notes" class="block text-sm font-medium text-gray-700">Notes (Optional)</label>
                <textarea id="notes" name="notes" rows="3" class="mt-1 block w-full border rounded px-3 py-2" placeholder="Additional notes">{{ old('notes') }}</textarea>
            </div>
        </div>

        <div class="pt-2 flex gap-2">
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Issue Item</button>
            <a href="{{ route('issued-items.index') }}" class="px-4 py-2 bg-white border text-gray-800 rounded hover:bg-gray-50">Cancel</a>
        </div>
    </form>

    <script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // Date logic: ensure issued_on is auto-set to today, constrained, and read-only
        const issuedOn = document.getElementById('issued_on');
        const todayStr = new Date().toISOString().split('T')[0];
        if (issuedOn) {
            issuedOn.max = todayStr;
            if (!issuedOn.value) issuedOn.value = todayStr;
            issuedOn.readOnly = true;
            // Prevent opening native picker on some browsers
            issuedOn.addEventListener('focus', function(){ this.blur(); });
            issuedOn.addEventListener('keydown', function(e){ e.preventDefault(); });
            issuedOn.addEventListener('input', function(){ this.value = todayStr; });
        }

        // Searchable list logic (render exactly 5 items)
        const suppliesJsonEl = document.getElementById('supplies_json');
        const searchInput = document.getElementById('supply_search');
        const listEl = document.getElementById('supply_list');
        const listMetaEl = document.getElementById('supply_list_meta');
        const availableEl = document.getElementById('selected_available');
        const qtyInput = document.getElementById('selected_quantity');
        const supplyIdInput = document.getElementById('supply_id');
        const variantIdInput = document.getElementById('supply_variant_id');
        let supplies = [];
        try { supplies = JSON.parse(suppliesJsonEl?.textContent || '[]'); } catch (e) { supplies = []; }
        supplies = supplies.sort((a,b) => a.name.localeCompare(b.name));
        let filteredSupplies = supplies.slice();
        let selectedSupply = null;
        let selectedVariant = null;
        const variantSelectorEl = document.getElementById('variant_selector');
        const variantSelectEl = document.getElementById('variant_select');
        const VARIANTS_ENDPOINT_PREFIX = '{{ url('/issued-items/supply-variants') }}';
        const VARIANT_BY_SKU_ENDPOINT_PREFIX = '{{ url('/issued-items/variant-by-sku') }}';

        const SUPPLY_SHOW_COUNT = 5;
        function renderSupplyList() {
            listEl.innerHTML = '';
            const itemsToShow = filteredSupplies.slice(0, SUPPLY_SHOW_COUNT);
            itemsToShow.forEach(s => {
                const li = document.createElement('li');
                li.setAttribute('data-id', s.id);
                li.setAttribute('data-name', s.name);
                li.setAttribute('data-available', s.available);
                const skuText = (s.sku ? `SKU: ${s.sku}` : 'SKU: —');
                const baseHtml = `<div class=\"flex items-center justify-between\">` +
                                 `<div class=\"flex flex-col\"><span class=\"font-medium text-gray-800\">${s.name}</span>` +
                                 `<span class=\"text-xs text-gray-600\">${skuText}</span></div>` +
                                 `<span class=\"text-xs text-gray-600\">Available: ${s.available}</span>` +
                                 `</div>`;

                // Disable selection for zero-availability items without variants
                if (!s.has_variants && Number(s.available) <= 0) {
                    li.className = 'px-3 py-2 text-gray-400 cursor-not-allowed opacity-50';
                    li.innerHTML = baseHtml;
                    li.title = 'Out of stock';
                    // Do not attach click handler
                } else {
                    li.className = 'px-3 py-2 cursor-pointer hover:bg-blue-50';
                    li.innerHTML = baseHtml;
                    // Manual click uses dropdown selection to avoid modal interruptions
                    li.addEventListener('click', function(){ onSupplySelected(s, li, false); });
                }
                listEl.appendChild(li);
            });
            // Fill placeholders to always render exactly SUPPLY_SHOW_COUNT entries
            const placeholders = SUPPLY_SHOW_COUNT - itemsToShow.length;
            for (let i = 0; i < placeholders; i++) {
                const li = document.createElement('li');
                li.className = 'px-3 py-2 text-gray-400 cursor-not-allowed';
                li.textContent = itemsToShow.length === 0 && i === 0 ? 'No matching items' : '—';
                listEl.appendChild(li);
            }
            const more = Math.max(0, filteredSupplies.length - SUPPLY_SHOW_COUNT);
            listMetaEl.textContent = more > 0 ? `Showing ${SUPPLY_SHOW_COUNT} of ${filteredSupplies.length} items` : `Showing ${Math.min(filteredSupplies.length, SUPPLY_SHOW_COUNT)} items`;
        }

        function filterSupplies() {
            const q = (searchInput?.value || '').toLowerCase();
            filteredSupplies = supplies.filter(s => s.name.toLowerCase().includes(q));
            renderSupplyList();
        }

        async function onSupplySelected(s, liEl, showVariantModal = true) {
            selectedSupply = s;
            selectedVariant = null;
            Array.from(listEl.children).forEach(el => el.classList.remove('bg-blue-100', 'border', 'border-blue-300'));
            if (liEl) liEl.classList.add('bg-blue-100', 'border', 'border-blue-300');
            // Set hidden supply_id
            if (supplyIdInput) supplyIdInput.value = s.id;
            // Update availability panel and quantity max
            // Guard: prevent selecting zero-availability items without variants
            if (!s.has_variants && Number(s.available) <= 0) {
                alert('This item is out of stock and cannot be selected.');
                if (liEl) liEl.classList.remove('bg-blue-100', 'border', 'border-blue-300');
                if (supplyIdInput) supplyIdInput.value = '';
                return;
            }
            if (s.has_variants) {
                if (availableEl) availableEl.textContent = '—';
                if (qtyInput) { qtyInput.value = ''; qtyInput.removeAttribute('max'); }
                if (variantIdInput) variantIdInput.value = '';
                if (showVariantModal && typeof openVariantModalForSupply === 'function') {
                    hideVariantSelector();
                    await openVariantModalForSupply(s);
                } else {
                    try { await loadVariantsForSupply(s.id); } catch (_) { hideVariantSelector(); }
                    showVariantSelector();
                }
            } else {
                if (availableEl) availableEl.textContent = s.available;
                if (qtyInput) { qtyInput.max = String(s.available); if (!qtyInput.value) qtyInput.value = 1; }
                hideVariantSelector();
                if (variantIdInput) variantIdInput.value = '';
            }
            // Expose selection to other scripts
            window.SuppliesUI = window.SuppliesUI || {};
            window.SuppliesUI.selectedSupply = selectedSupply;
            window.SuppliesUI.selectedVariant = selectedVariant;
        }

        function showVariantSelector() {
            if (variantSelectorEl) variantSelectorEl.classList.remove('hidden');
        }
        function hideVariantSelector() {
            if (variantSelectorEl) variantSelectorEl.classList.add('hidden');
            if (variantSelectEl) { variantSelectEl.innerHTML = '<option value=\"\">Select a variant</option>'; }
        }

        async function loadVariantsForSupply(supplyId) {
            try {
                const resp = await fetch(`${VARIANTS_ENDPOINT_PREFIX}/${supplyId}`);
                const data = await resp.json();
                const variants = (data && data.variants) ? data.variants : [];
                if (variants.length) {
                    if (variantSelectEl) {
                        variantSelectEl.innerHTML = '<option value=\"\">Select a variant</option>' + variants.map(v => {
                            const av = Number((v.available !== undefined ? v.available : (v.current_stock !== undefined ? v.current_stock : v.quantity)) || 0);
                            const disabled = av <= 0 ? 'disabled' : '';
                            const label = `${v.display_name} — Currently Available: ${av}${v.unit ? ' ' + v.unit : ''}`;
                            return `<option value=\"${v.id}\" ${disabled} data-available=\"${av}\" data-name=\"${label}\">${label}</option>`;
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
                if (!opt || !opt.value) { selectedVariant = null; if (availableEl) availableEl.textContent = '—'; if (qtyInput) { qtyInput.value = ''; qtyInput.removeAttribute('max'); } if (variantIdInput) variantIdInput.value = ''; return; }
                selectedVariant = {
                    id: Number(opt.value),
                    name: opt.dataset.name || 'Selected variant',
                    available: Number(opt.dataset.available || 0)
                };
                if (availableEl) availableEl.textContent = selectedVariant.available;
                if (qtyInput) { qtyInput.max = String(selectedVariant.available); if (!qtyInput.value) qtyInput.value = 1; }
                if (variantIdInput) variantIdInput.value = String(selectedVariant.id);
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
        
        // Variant modal logic
        const variantModalOverlay = document.getElementById('variant_modal_overlay');
        const variantModalListEl = document.getElementById('variant_modal_list');
        const variantModalDoneBtn = document.getElementById('variant_modal_done');
        const variantModalCancelBtn = document.getElementById('variant_modal_cancel');
        const variantModalCloseBtn = document.getElementById('variant_modal_close');
        const variantModalSupplyName = document.getElementById('variant_modal_supply_name');
        let variantModalCurrentSupply = null;

        function closeVariantModal() {
            if (!variantModalOverlay) return;
            variantModalOverlay.classList.add('hidden');
            variantModalOverlay.classList.remove('flex');
            variantModalCurrentSupply = null;
            if (variantModalListEl) variantModalListEl.innerHTML = '';
        }

        async function openVariantModalForSupply(supply, preselectVariantId = null) {
            if (!variantModalOverlay) return;
            variantModalCurrentSupply = supply;
            if (variantModalSupplyName) variantModalSupplyName.textContent = supply.name || `Supply #${supply.id}`;
            variantModalOverlay.classList.remove('hidden');
            variantModalOverlay.classList.add('flex');
            if (variantModalListEl) {
                variantModalListEl.innerHTML = '<div class="text-sm text-gray-600">Loading variants…</div>';
            }
            try {
                const resp = await fetch(`${VARIANTS_ENDPOINT_PREFIX}/${supply.id}`);
                const data = await resp.json();
                const variants = (data && data.variants) ? data.variants : [];
                if (!variants.length) {
                    variantModalListEl.innerHTML = '<div class="text-sm text-gray-600">No active variants available.</div>';
                    return;
                }
                variantModalListEl.innerHTML = '';
                variants.forEach(v => {
                    const av = Number((v.available !== undefined ? v.available : (v.current_stock !== undefined ? v.current_stock : v.quantity)) || 0);
                    const label = v.display_name || v.name || `Variant #${v.id}`;
                    const row = document.createElement('div');
                    row.className = 'flex items-center justify-between border rounded px-3 py-2';
                    const left = document.createElement('div');
                    left.className = 'flex items-center gap-2';
                    const checkbox = document.createElement('input');
                    checkbox.type = 'checkbox';
                    checkbox.className = 'variant-row-checkbox';
                    checkbox.dataset.vid = String(v.id);
                    checkbox.dataset.vname = label;
                    checkbox.dataset.available = String(av);
                    checkbox.disabled = av <= 0;
                    const nameEl = document.createElement('span');
                    nameEl.className = 'text-gray-800';
                    nameEl.textContent = label;
                    const availEl = document.createElement('span');
                    availEl.className = 'text-xs text-gray-600';
                    availEl.textContent = `Available: ${av}${v.unit ? ' ' + v.unit : ''}`;
                    left.append(checkbox, nameEl, availEl);
                    const right = document.createElement('div');
                    const qtyInputEl = document.createElement('input');
                    qtyInputEl.type = 'number';
                    qtyInputEl.min = '1';
                    qtyInputEl.step = '1';
                    qtyInputEl.max = String(av);
                    qtyInputEl.value = av > 0 ? '1' : '';
                    qtyInputEl.className = 'border rounded px-2 py-1 w-24 variant-row-qty';
                    qtyInputEl.dataset.vid = String(v.id);
                    qtyInputEl.disabled = av <= 0;
                    // If a specific variant is requested, preselect it when available
                    if (preselectVariantId && Number(preselectVariantId) === Number(v.id) && av > 0) {
                        checkbox.checked = true;
                        qtyInputEl.disabled = false;
                        try { qtyInputEl.focus(); } catch (_) {}
                    }
                    checkbox.addEventListener('change', function(){ qtyInputEl.disabled = !checkbox.checked; if (checkbox.checked && !qtyInputEl.value) qtyInputEl.value = '1'; });
                    right.append(qtyInputEl);
                    row.append(left, right);
                    variantModalListEl.appendChild(row);
                });
            } catch (e) {
                variantModalListEl.innerHTML = '<div class="text-sm text-red-600">Failed to load variants. Please try again.</div>';
            }
        }

        if (variantModalCancelBtn) variantModalCancelBtn.addEventListener('click', closeVariantModal);
        if (variantModalCloseBtn) variantModalCloseBtn.addEventListener('click', closeVariantModal);
        if (variantModalOverlay) variantModalOverlay.addEventListener('keydown', function(ev){ if (ev.key === 'Escape') closeVariantModal(); });
        if (variantModalDoneBtn) variantModalDoneBtn.addEventListener('click', function(){
            if (!variantModalCurrentSupply) { closeVariantModal(); return; }
            const s = variantModalCurrentSupply;
            const rows = Array.from(variantModalListEl.querySelectorAll('.variant-row-checkbox'));
            const selected = rows.filter(cb => cb.checked);
            if (!selected.length) { alert('Select at least one variant to add.'); return; }
            selected.forEach(cb => {
                const vid = Number(cb.dataset.vid || 0);
                const vname = cb.dataset.vname || 'Variant';
                const available = Number(cb.dataset.available || 0);
                const qtyEl = variantModalListEl.querySelector(`.variant-row-qty[data-vid="${vid}"]`);
                const qtyVal = qtyEl ? Number(qtyEl.value || 0) : 0;
                if (!qtyVal || qtyVal < 1) { return; }
                if (available && qtyVal > available) { alert(`Quantity exceeds available stock for ${vname}. Available: ${available}`); return; }
                const itemObj = {
                    supplyId: Number(s.id),
                    name: s.name || `Supply #${s.id}`,
                    variantId: vid,
                    variantName: vname,
                    quantity: qtyVal
                };
                const existingIdx = savedItems.findIndex(x => Number(x.supplyId) === Number(itemObj.supplyId) && Number(x.variantId || 0) === Number(itemObj.variantId));
                if (existingIdx >= 0) { savedItems[existingIdx].quantity = itemObj.quantity; }
                else { savedItems.push(itemObj); }
            });
            renderSavedItems();
            closeVariantModal();
        });

        if (searchInput) searchInput.addEventListener('input', filterSupplies);
        renderSupplyList();

        // Build SKU map for fast lookup
        const skuMap = new Map();
        supplies.forEach(s => { if (s && s.sku) { skuMap.set(String(s.sku).toUpperCase(), s); } });

        function findSupplyBySkuFromRaw(raw) {
            const text = String(raw || '').trim();
            if (!text) return null;

            // 1) URL-first payload: extract trailing segment after the URL and pick SKU
            const urlFirst = text.match(/^(https?:\/\/\S+)/i);
            if (urlFirst) {
                const trailing = text.slice(urlFirst[0].length).trim();
                // Prefer explicit SKU markers if present
                const skuMarker = trailing.match(/\bSKU\s*[:=]\s*([A-Z0-9\-]+)/i);
                if (skuMarker) {
                    const skuVal = String(skuMarker[1]).toUpperCase();
                    if (skuMap.has(skuVal)) return skuMap.get(skuVal);
                }
                // Otherwise, take the last token in trailing segment as candidate SKU
                const tokens = (trailing.toUpperCase().match(/[A-Z0-9\-]+/g) || []);
                if (tokens.length) {
                    const candidate = tokens[tokens.length - 1];
                    if (skuMap.has(candidate)) return skuMap.get(candidate);
                }
                // Fallback: parse supply ID from the URL path /qr/supply/{id}
                let supplyIdFromUrl = null;
                try {
                    const u = new URL(urlFirst[0]);
                    const m = u.pathname.match(/\/qr\/supply\/(\d+)/i);
                    if (m && m[1]) supplyIdFromUrl = Number(m[1]);
                } catch (_) {
                    const m2 = text.match(/\/qr\/supply\/(\d+)/i);
                    if (m2 && m2[1]) supplyIdFromUrl = Number(m2[1]);
                }
                if (supplyIdFromUrl) {
                    const byId = supplies.find(x => Number(x.id) === supplyIdFromUrl);
                    if (byId) return byId;
                }
                // If trailing is empty or unmatched and no ID fallback, continue with other strategies
            }

            // 2) JSON payload fallback: { sku, url }
            if (text.startsWith('{') && text.endsWith('}')) {
                try {
                    const obj = JSON.parse(text);
                    if (obj && typeof obj === 'object' && obj.sku) {
                        const skuVal = String(obj.sku).trim().toUpperCase();
                        if (skuMap.has(skuVal)) return skuMap.get(skuVal);
                    }
                } catch (_) { /* ignore JSON parse errors */ }
            }

            // 3) Explicit SKU markers in plain text
            const skuMarkerPlain = text.match(/\bSKU\s*[:=]\s*([A-Z0-9\-]+)/i);
            if (skuMarkerPlain) {
                const skuVal = String(skuMarkerPlain[1]).toUpperCase();
                if (skuMap.has(skuVal)) return skuMap.get(skuVal);
            }

            // 4) Direct full-text match (rare)
            const upper = text.toUpperCase();
            if (skuMap.has(upper)) return skuMap.get(upper);

            // 5) Token scan fallback (ignores typical URL separators)
            const tokens = upper.match(/[A-Z0-9\-]+/g) || [];
            for (const t of tokens) {
                if (skuMap.has(t)) return skuMap.get(t);
            }
            return null;
        }

        // Variant SKU resolution helpers
        async function resolveVariantBySku(skuCandidate) {
            const sku = String(skuCandidate || '').trim();
            if (!sku) return null;
            try {
                const resp = await fetch(`${VARIANT_BY_SKU_ENDPOINT_PREFIX}/${encodeURIComponent(sku)}`);
                if (!resp.ok) return null;
                const data = await resp.json();
                if (!data || !data.variant || !data.supply) return null;
                return data;
            } catch (_) { return null; }
        }

        function extractSkuCandidateFromRaw(raw) {
            const text = String(raw || '').trim();
            if (!text) return '';
            // 1) Try explicit SKU markers
            const m1 = text.match(/\bSKU\s*[:=]\s*([A-Z0-9\-]+)/i);
            if (m1 && m1[1]) return String(m1[1]).toUpperCase();
            // 2) JSON payload with sku
            if (text.startsWith('{') && text.endsWith('}')) {
                try { const obj = JSON.parse(text); if (obj && obj.sku) return String(obj.sku).toUpperCase(); } catch (_) {}
            }
            // 3) URL-first payload: take last token after URL
            const urlFirst = text.match(/^(https?:\/\/\S+)/i);
            if (urlFirst) {
                const trailing = text.slice(urlFirst[0].length).trim().toUpperCase();
                const tokens = trailing.match(/[A-Z0-9\-]+/g) || [];
                if (tokens.length) return tokens[tokens.length - 1];
            }
            // 4) Fallback: scan tokens for plausible SKU
            const tokens = text.toUpperCase().match(/[A-Z0-9\-]+/g) || [];
            return tokens.length ? tokens[tokens.length - 1] : '';
        }

        async function findVariantBySkuFromRaw(raw) {
            const candidate = extractSkuCandidateFromRaw(raw);
            if (!candidate) return null;
            return await resolveVariantBySku(candidate);
        }

        // Manual QR/ID/SKU usage
        const qrInput = document.getElementById('qr_manual');
        const qrUseBtn = document.getElementById('qr_use');
        if (qrUseBtn && qrInput) {
            qrUseBtn.addEventListener('click', async function(){
                const raw = (qrInput.value || '').trim();
                if (!raw) return;
                // Try variant SKU match first
                const resolved = await findVariantBySkuFromRaw(raw);
                if (resolved && resolved.supply && resolved.variant) {
                    const s = supplies.find(x => Number(x.id) === Number(resolved.supply.id));
                    if (!s) { alert('Variant found, but parent supply is not available in this list.'); return; }
                    const targetEl = Array.from(listEl.children).find(el => Number(el.getAttribute('data-id')) === Number(s.id));
                    await onSupplySelected(s, targetEl, true);
                    await openVariantModalForSupply(s, resolved.variant.id);
                    return;
                }
                // Fallback: supply-level SKU match
                let s = findSupplyBySkuFromRaw(raw);
                if (!s) {
                    // Fallback: numeric ID
                    const idMatch = raw.match(/\d+/);
                    const idVal = idMatch ? Number(idMatch[0]) : NaN;
                    if (idVal) {
                        s = supplies.find(x => Number(x.id) === idVal);
                    }
                }
                if (!s) {
                    const looksUrlFirst = /^https?:\/\/\S+/i.test(raw);
                    if (looksUrlFirst) {
                        alert('QR appears URL-only or missing SKU. Unable to map to a supply.');
                    } else {
                        alert('No matching supply found from provided QR/SKU/ID input.');
                    }
                    return;
                }
                const targetEl = Array.from(listEl.children).find(el => Number(el.getAttribute('data-id')) === Number(s.id));
                onSupplySelected(s, targetEl, true);
            });
        }

        // QR scanning with camera permission gating
        const qrScanBtn = document.getElementById('qr_scan');
        const qrStopBtn = document.getElementById('qr_stop');
        const qrCamContainer = document.getElementById('qr_camera');
        const qrStatusEl = document.getElementById('qr_status');
        const qrVideoEl = document.getElementById('qr_video');
        let mediaStream = null;
        let scanInterval = null;
        let barcodeDetector = null;
        const isBarcodeDetectorSupported = 'BarcodeDetector' in window;

        async function handleQRCode(rawValue) {
            const raw = String(rawValue || '').trim();
            // Try variant-by-SKU first
            const resolved = await findVariantBySkuFromRaw(raw);
            if (resolved && resolved.supply && resolved.variant) {
                const s = supplies.find(x => Number(x.id) === Number(resolved.supply.id));
                if (!s) { alert('Variant found, but parent supply is not available in this list.'); return; }
                const targetEl = Array.from(listEl.children).find(el => Number(el.getAttribute('data-id')) === Number(s.id));
                await onSupplySelected(s, targetEl, true);
                await openVariantModalForSupply(s, resolved.variant.id);
                return;
            }
            // Fallback to supply-level mapping
            let s = findSupplyBySkuFromRaw(raw);
            let targetEl = null;
            if (!s) {
                const idMatch = raw.match(/\d+/);
                const idVal = idMatch ? Number(idMatch[0]) : NaN;
                if (idVal) {
                    s = supplies.find(x => Number(x.id) === idVal);
                    targetEl = Array.from(listEl.children).find(el => Number(el.getAttribute('data-id')) === idVal);
                }
            } else {
                targetEl = Array.from(listEl.children).find(el => Number(el.getAttribute('data-id')) === Number(s.id));
            }
            if (!s) {
                const looksUrlFirst = /^https?:\/\/\S+/i.test(raw);
                if (looksUrlFirst) {
                    alert('QR appears URL-only or missing SKU. Unable to map to a supply.');
                } else {
                    alert('No matching supply found for scanned QR/SKU.');
                }
                return;
            }
            onSupplySelected(s, targetEl, true);
        }

        async function startQRScan() {
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                alert('Camera access is not supported in this browser.');
                return;
            }
            try {
                qrCamContainer.classList.remove('hidden');
                qrStatusEl.textContent = 'Requesting camera permission…';
                mediaStream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
                qrVideoEl.srcObject = mediaStream;
                try { await qrVideoEl.play(); } catch (_) {}
                qrStatusEl.textContent = 'Camera active. Scanning for QR codes…';
                if (isBarcodeDetectorSupported) {
                    barcodeDetector = new BarcodeDetector({ formats: ['qr_code'] });
                    scanInterval = setInterval(async () => {
                        try {
                            const codes = await barcodeDetector.detect(qrVideoEl);
                            if (codes && codes.length) {
                                const raw = codes[0].rawValue || '';
                                handleQRCode(raw);
                                stopQRScan();
                            }
                        } catch (err) {
                            // ignore detection errors
                        }
                    }, 500);
                } else if (window.jsQR) {
                    const canvas = document.createElement('canvas');
                    const ctx = canvas.getContext('2d');
                    scanInterval = setInterval(() => {
                        const vw = qrVideoEl.videoWidth || 0;
                        const vh = qrVideoEl.videoHeight || 0;
                        if (!vw || !vh) return;
                        canvas.width = vw; canvas.height = vh;
                        ctx.drawImage(qrVideoEl, 0, 0, vw, vh);
                        const imageData = ctx.getImageData(0, 0, vw, vh);
                        const code = window.jsQR(imageData.data, vw, vh);
                        if (code && code.data) {
                            handleQRCode(code.data);
                            stopQRScan();
                        }
                    }, 500);
                } else {
                    qrStatusEl.textContent = 'Camera active. QR scanning not supported; preview only.';
                }
            } catch (err) {
                qrStatusEl.textContent = 'Camera permission denied or unavailable.';
                alert('Unable to access camera: ' + (err && err.message ? err.message : 'Unknown error'));
                stopQRScan(true);
            }
        }

        function stopQRScan(keepHidden) {
            if (scanInterval) { clearInterval(scanInterval); scanInterval = null; }
            if (mediaStream) {
                try { mediaStream.getTracks().forEach(track => track.stop()); } catch (e) {}
                mediaStream = null;
            }
            qrVideoEl.srcObject = null;
            if (!keepHidden) qrCamContainer.classList.add('hidden');
            qrStatusEl.textContent = '';
            barcodeDetector = null;
        }

        if (qrScanBtn) qrScanBtn.addEventListener('click', startQRScan);
        if (qrStopBtn) qrStopBtn.addEventListener('click', function(){ stopQRScan(false); });
        window.addEventListener('beforeunload', function(){ stopQRScan(true); });

        // Recipient list (render exactly 5 entries) with real-time search
        const deptSelect = document.getElementById('department_id');
        const recipientSearchInput = document.getElementById('recipient_search');
        const recipientListEl = document.getElementById('recipient_list');
        const recipientMetaEl = document.getElementById('recipient_list_meta');
        const userIdHiddenInput = document.getElementById('user_id');
        const recipientClearBtn = document.getElementById('recipient_clear');
        const usersJsonEl = document.getElementById('users_json');
        let users = [];
        try { users = JSON.parse(usersJsonEl?.textContent || '[]'); } catch (e) { users = []; }
        users = users.sort((a,b) => a.name.localeCompare(b.name));
        const RECIPIENT_SHOW_COUNT = 5;
        let filteredRecipients = users.slice();

        function renderRecipientList() {
            recipientListEl.innerHTML = '';
            const itemsToShow = filteredRecipients.slice(0, RECIPIENT_SHOW_COUNT);
            itemsToShow.forEach(u => {
                const li = document.createElement('li');
                li.className = 'px-3 py-2 cursor-pointer hover:bg-blue-50';
                li.setAttribute('data-id', u.id);
                li.setAttribute('data-department-id', u.department_id);
                li.innerHTML = `<div class=\"flex items-center justify-between\"><span class=\"font-medium text-gray-800\">${u.name}</span><span class=\"text-xs text-gray-600\">${u.department_name}</span></div>`;
                li.addEventListener('click', function(){ onRecipientSelected(u, li); });
                recipientListEl.appendChild(li);
            });
            const placeholders = RECIPIENT_SHOW_COUNT - itemsToShow.length;
            for (let i = 0; i < placeholders; i++) {
                const li = document.createElement('li');
                li.className = 'px-3 py-2 text-gray-400 cursor-not-allowed';
                li.textContent = itemsToShow.length === 0 && i === 0 ? 'No matching recipients' : '—';
                recipientListEl.appendChild(li);
            }
            const more = Math.max(0, filteredRecipients.length - RECIPIENT_SHOW_COUNT);
            recipientMetaEl.textContent = more > 0 ? `Showing ${RECIPIENT_SHOW_COUNT} of ${filteredRecipients.length} recipients` : `Showing ${Math.min(filteredRecipients.length, RECIPIENT_SHOW_COUNT)} recipients`;
        }

        function filterRecipients() {
            const q = (recipientSearchInput?.value || '').toLowerCase();
            const deptId = deptSelect ? deptSelect.value : '';
            filteredRecipients = users.filter(u => {
                const deptOk = !deptId || String(u.department_id) === String(deptId);
                const nameOk = u.name.toLowerCase().includes(q);
                return deptOk && nameOk;
            });
            renderRecipientList();
        }

        let selectedRecipientObj = null;
        function onRecipientSelected(u, liEl) {
            Array.from(recipientListEl.children).forEach(el => el.classList.remove('bg-blue-100', 'border', 'border-blue-300'));
            if (liEl) liEl.classList.add('bg-blue-100', 'border', 'border-blue-300');
            if (userIdHiddenInput) userIdHiddenInput.value = u.id;
            // Auto-set department to recipient's department
            if (deptSelect) {
                if (deptSelect.tagName === 'SELECT') {
                    deptSelect.value = String(u.department_id || '');
                } else {
                    // Hidden input for non-admins
                    deptSelect.value = String(u.department_id || '');
                }
            }
            selectedRecipientObj = u;
            window.RecipientsUI = window.RecipientsUI || {};
            window.RecipientsUI.selectedRecipient = u;
        }

        if (recipientSearchInput) recipientSearchInput.addEventListener('input', filterRecipients);
        if (deptSelect) deptSelect.addEventListener('change', function(){
            // Clear selected recipient on department change, then re-filter
            if (userIdHiddenInput) userIdHiddenInput.value = '';
            filterRecipients();
        });
        if (recipientClearBtn) recipientClearBtn.addEventListener('click', function(){
            if (userIdHiddenInput) userIdHiddenInput.value = '';
            Array.from(recipientListEl.children).forEach(el => el.classList.remove('bg-blue-100', 'border', 'border-blue-300'));
        });

        // Initialize recipients list with possible old selection
        const oldUserId = Number('{{ old('user_id') }}') || 0;
        filterRecipients();
        if (oldUserId) {
            const u = users.find(x => Number(x.id) === oldUserId);
            if (u) {
                userIdHiddenInput.value = String(u.id);
                const li = Array.from(recipientListEl.children).find(el => Number(el.getAttribute('data-id')) === Number(u.id));
                if (li) li.classList.add('bg-blue-100', 'border', 'border-blue-300');
            }
        }

        // Saved lists: items
        const addItemBtn = document.getElementById('add_item_btn');
        const savedItemsListEl = document.getElementById('saved_items_list');
        const savedItems = [];
        function renderSavedItems() {
            savedItemsListEl.innerHTML = '';
            if (!savedItems.length) {
                const li = document.createElement('li');
                li.className = 'px-3 py-2 text-gray-500';
                li.textContent = 'No saved items';
                savedItemsListEl.appendChild(li);
                return;
            }
            savedItems.forEach((it, idx) => {
                const li = document.createElement('li');
                li.className = 'px-3 py-2 flex items-center justify-between';
                const left = document.createElement('div');
                left.innerHTML = `<span class="font-medium text-gray-800">${it.name}</span>` +
                                 (it.variantName ? ` <span class="text-xs text-gray-600">(${it.variantName})</span>` : '') +
                                 ` <span class="text-xs text-gray-600">Qty: ${it.quantity}</span>`;
                const right = document.createElement('div');
                right.className = 'flex gap-2';
                const useBtn = document.createElement('button');
                useBtn.type = 'button';
                useBtn.className = 'px-2 py-1 bg-blue-100 text-blue-800 rounded hover:bg-blue-200 text-xs';
                useBtn.textContent = 'Use';
                useBtn.addEventListener('click', function(){
                    const s = supplies.find(x => Number(x.id) === Number(it.supplyId));
                    const targetEl = Array.from(listEl.children).find(el => Number(el.getAttribute('data-id')) === Number(it.supplyId));
                    onSupplySelected(s, targetEl, false);
                    if (it.variantId) {
                        window.SuppliesUI.loadVariantsForSupply(it.supplyId).then(() => {
                            if (variantSelectEl) {
                                variantSelectEl.value = String(it.variantId);
                                const evt = new Event('change');
                                variantSelectEl.dispatchEvent(evt);
                            }
                        }).catch(()=>{});
                    }
                    if (qtyInput) qtyInput.value = String(it.quantity);
                });
                const removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.className = 'px-2 py-1 bg-red-100 text-red-800 rounded hover:bg-red-200 text-xs';
                removeBtn.textContent = 'Remove';
                removeBtn.addEventListener('click', function(){
                    savedItems.splice(idx, 1);
                    renderSavedItems();
                });
                right.append(useBtn, removeBtn);
                li.append(left, right);
                savedItemsListEl.appendChild(li);
            });
        }
        if (addItemBtn) addItemBtn.addEventListener('click', function(){
            const supId = supplyIdInput ? Number(supplyIdInput.value) : 0;
            if (!supId) { alert('Select an item first.'); return; }
            const qtyVal = qtyInput ? Number(qtyInput.value) : 0;
            if (!qtyVal || qtyVal < 1) { alert('Enter a valid quantity.'); return; }
            const max = Number(qtyInput?.max || 0);
            if (max && qtyVal > max) { alert('Quantity exceeds available stock.'); return; }
            const s = supplies.find(x => Number(x.id) === supId);
            // Prevent adding zero-availability items
            if (s) {
                if (s.has_variants) {
                    if (!selectedVariant) { alert('Please select a variant before adding.'); return; }
                    if (Number(selectedVariant.available || 0) <= 0) { alert('Selected variant is out of stock.'); return; }
                } else {
                    if (Number(s.available || 0) <= 0) { alert('Selected item is out of stock.'); return; }
                }
            }
            const itemObj = {
                supplyId: supId,
                name: s ? s.name : `Supply #${supId}`,
                variantId: selectedVariant ? Number(selectedVariant.id) : null,
                variantName: selectedVariant ? (selectedVariant.name || 'Variant') : null,
                quantity: qtyVal
            };
            const existingIdx = savedItems.findIndex(x => x.supplyId === itemObj.supplyId && x.variantId === itemObj.variantId);
            if (existingIdx >= 0) {
                savedItems[existingIdx].quantity = itemObj.quantity;
            } else {
                savedItems.push(itemObj);
            }
            renderSavedItems();
        });
        renderSavedItems();

        // Single recipient only: removed saved recipients list and controls

        // Validate selection before submit
        const form = document.getElementById('issuedItemForm');
        form.addEventListener('submit', function(e){
            // If there are saved items, serialize them into items[] for batch issuance
            if (savedItems && savedItems.length > 0) {
                // Remove any previously added items[] inputs to avoid duplicates
                const oldItemInputs = form.querySelectorAll('input[name^="items["]');
                oldItemInputs.forEach(el => el.remove());

                // Create hidden inputs for each saved item
                savedItems.forEach((item, idx) => {
                    const addHidden = (name, value) => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = `items[${idx}][${name}]`;
                        input.value = value ?? '';
                        form.appendChild(input);
                    };
                    addHidden('supply_id', item.supplyId);
                    if (item.variantId) addHidden('supply_variant_id', item.variantId);
                    addHidden('quantity', item.quantity);
                });
                // Allow submit to continue; server will validate the items[] array
                return;
            }

            // Fallback to single-item validation when no saved items present
            const supId = supplyIdInput ? supplyIdInput.value : '';
            if (!supId) { alert('Please select an item to issue.'); e.preventDefault(); return; }
            const qtyVal = qtyInput ? Number(qtyInput.value) : 0;
            if (!qtyVal || qtyVal < 1) { alert('Please enter a valid quantity to issue.'); e.preventDefault(); return; }
            const max = Number(qtyInput?.max || 0);
            if (max && qtyVal > max) { alert('Quantity exceeds available stock.'); e.preventDefault(); return; }
        });
    });
    </script>
</div>
@endsection