@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-2xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Create New Supply Request</h1>
            <p class="text-gray-600 mt-2">Submit a request for supplies needed by your department</p>
        </div>

        <!-- Form Card -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <!-- Global flash and error messages -->
            @if(session('success'))
                <div class="mb-4 px-4 py-3 rounded bg-green-50 text-green-700 border border-green-200">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 px-4 py-3 rounded bg-red-50 text-red-700 border border-red-200">
                    {{ session('error') }}
                </div>
            @endif

            @if($errors->has('error'))
                <div class="mb-4 px-4 py-3 rounded bg-red-50 text-red-700 border border-red-200">
                    {{ $errors->first('error') }}
                </div>
            @endif

            <form action="{{ route('supply-requests.store') }}" method="POST">
                @csrf

                <!-- Request Type Selection -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Request Type <span class="text-red-500">*</span>
                    </label>
                    <div class="flex space-x-4">
                        <label class="flex items-center">
                            <input type="radio" name="request_type" value="existing" class="mr-2" id="existing_supply" onchange="toggleRequestType()" {{ old('request_type') === 'existing' ? 'checked' : '' }}>
                            <span class="text-sm text-gray-700">Request Existing Supply</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="request_type" value="new" class="mr-2" id="new_item" onchange="toggleRequestType()" {{ old('request_type', 'new') === 'new' ? 'checked' : '' }}>
                            <span class="text-sm text-gray-700">Request New Item</span>
                        </label>
                </div>
                </div>

                <!-- Existing Supply Selection (Hidden by default) -->
                <div id="existing_supply_section" class="mb-6" style="display: none;">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Select Supply and Quantity <span class="text-red-500">*</span>
                    </label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-2">
                        <div>
                            <label for="existing_supply_search" class="block text-xs font-medium text-gray-600 mb-1">Search supplies</label>
                            <input type="text"
                                   id="existing_supply_search"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="Type to filter supplies"
                                   oninput="filterExistingSupplies()">
                            <div class="mt-2 border border-gray-200 rounded-md">
                                <ul id="existing_supply_list" class="divide-y divide-gray-200 max-h-56 overflow-y-auto">
                                    <!-- populated dynamically -->
                                </ul>
                                <div id="existing_supply_list_empty" class="px-3 py-2 text-sm text-gray-500" style="display:none;">
                                    No matching supplies
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 mt-2">Showing up to 5 matches. Refine your search to find items.</p>
                        </div>
                        <div>
                            <label for="existing_quantity" class="block text-xs font-medium text-gray-600 mb-1">Quantity</label>
                            <input type="number" 
                                   id="existing_quantity" 
                                   min="1"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="Enter quantity">
                        </div>
                    </div>
                    <!-- Conditional Variant Selection -->
                    <div id="existing_variant_section" class="mb-4" style="display:none;">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Variant (optional)</label>
                        <select id="existing_variant_id" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select variant</option>
                        </select>
                        <p id="existing_variant_hint" class="text-xs text-gray-500 mt-2"></p>
                    </div>
                    <div class="mb-4">
                        <button type="button" 
                                class="px-4 py-2 text-sm font-medium text-white bg-green-600 border border-transparent rounded-md shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                                onclick="addExistingSupplyItem()">
                            Add Supply to Request
                        </button>
                        <p class="text-sm text-gray-500 mt-2">Add multiple supplies, review below, then submit.</p>
                    </div>

                    <div id="existing_items_list_container" class="mb-2" style="display:none;">
                        <h3 class="text-sm font-medium text-gray-700 mb-2">Supplies in this request</h3>
                        <div id="existing_items_list" class="space-y-2"></div>
                    </div>
                </div>

                <!-- New Items Section (supports multiple items) -->
                <div id="new_items_section">
                    <!-- Item Name (entry input) -->
                    <div class="mb-6" id="item_name_section">
                        <label for="item_name" class="block text-sm font-medium text-gray-700 mb-2">
                            Item Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="item_name" 
                               name="item_name" 
                               value="{{ old('item_name') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('item_name') border-red-500 @enderror"
                               placeholder="Enter the name of the item you need"
                               required>
                        @error('item_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Category (optional) -->
                    <div class="mb-6" id="category_section">
                        <label for="category_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Category (optional)
                        </label>
                        <select id="category_id" 
                                name="category_id" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select category (optional)</option>
                            @isset($categories)
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                @endforeach
                            @endisset
                        </select>
                    </div>

                    <!-- Quantity and Unit Row (entry inputs) -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-2" id="quantity_unit_row">
                        <!-- Quantity -->
                        <div>
                            <label for="quantity" class="block text-sm font-medium text-gray-700 mb-2">
                                Quantity <span class="text-red-500">*</span>
                            </label>
                            <input type="number" 
                                   id="quantity" 
                                   name="quantity" 
                                   value="{{ old('quantity') }}"
                                   min="1"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('quantity') border-red-500 @enderror"
                                   placeholder="Enter quantity"
                                   required>
                            @error('quantity')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Unit -->
                        <div>
                            <label for="unit" class="block text-sm font-medium text-gray-700 mb-2">
                                Unit (optional)
                            </label>
                            <select id="unit" 
                                    name="unit" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('unit') border-red-500 @enderror">
                                <option value="">Select unit (optional)</option>
                                <option value="pieces" {{ old('unit') == 'pieces' ? 'selected' : '' }}>Pieces</option>
                                <option value="boxes" {{ old('unit') == 'boxes' ? 'selected' : '' }}>Boxes</option>
                                <option value="packs" {{ old('unit') == 'packs' ? 'selected' : '' }}>Packs</option>
                                <option value="bottles" {{ old('unit') == 'bottles' ? 'selected' : '' }}>Bottles</option>
                                <option value="kg" {{ old('unit') == 'kg' ? 'selected' : '' }}>Kilograms</option>
                                <option value="liters" {{ old('unit') == 'liters' ? 'selected' : '' }}>Liters</option>
                                <option value="meters" {{ old('unit') == 'meters' ? 'selected' : '' }}>Meters</option>
                                <option value="sets" {{ old('unit') == 'sets' ? 'selected' : '' }}>Sets</option>
                            </select>
                            @error('unit')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Add item button -->
                    <div class="mb-6">
                        <button type="button" 
                                class="px-4 py-2 text-sm font-medium text-white bg-green-600 border border-transparent rounded-md shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                                onclick="addNewItem()">
                            Add Item to Request
                        </button>
                        <p class="text-sm text-gray-500 mt-2">Add multiple items, review below, then submit.</p>
                    </div>

                    <!-- Review list of new items -->
                    <div id="new_items_list_container" class="mb-6" style="display:none;">
                        <h3 class="text-sm font-medium text-gray-700 mb-2">Items in this request</h3>
                        <div id="new_items_list" class="space-y-2"></div>
                    </div>
                </div>

                <!-- Department -->
                <div class="mb-6">
                    <label for="department_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Department <span class="text-red-500">*</span>
                    </label>
                    @if(isset($user) && $user->hasAdminPrivileges())
                        <select id="department_id" 
                                name="department_id" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('department_id') border-red-500 @enderror"
                                required>
                            <option value="">Select department</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>
                                    {{ $department->department_name }}
                                </option>
                            @endforeach
                        </select>
                    @else
                        <input type="text"
                               value="{{ optional($user->department)->department_name ?? 'Not assigned' }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-100 text-gray-700"
                               readonly>
                        <input type="hidden" name="department_id" value="{{ optional($user->department)->id ?? '' }}">
                    @endif
                    @error('department_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div class="mb-6">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        Description
                    </label>
                    <textarea id="description" 
                              name="description" 
                              rows="4"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('description') border-red-500 @enderror"
                              placeholder="Provide additional details about the request (optional)">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                    <a href="{{ route('supply-requests.index') }}" 
                       class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="px-6 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Submit Request
                    </button>
                </div>
            </form>
        </div>

        <!-- Help Text -->
        <div class="mt-6 p-4 bg-blue-50 rounded-lg">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800">Request Guidelines</h3>
                    <div class="mt-2 text-sm text-blue-700">
                        <ul class="list-disc list-inside space-y-1">
                            <li>Be specific about the item name and quantity needed</li>
                            <li>Provide a clear description if the item requires special specifications</li>
                            <li>Your request will be reviewed by administrators</li>
                            <li>You will be notified once your request is approved or declined</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleRequestType() {
    const existingSupplyRadio = document.getElementById('existing_supply');
    const newItemRadio = document.getElementById('new_item');
    const existingSupplySection = document.getElementById('existing_supply_section');
    const newItemsSection = document.getElementById('new_items_section');
    const itemNameSection = document.getElementById('item_name_section');
    const itemNameInput = document.getElementById('item_name');
    const unitSelect = document.getElementById('unit');
    const quantityUnitRow = document.getElementById('quantity_unit_row');
    const quantityInput = document.getElementById('quantity');

    if (existingSupplyRadio.checked) {
        // Show existing supply section
        existingSupplySection.style.display = 'block';

        // Hide new items section
        newItemsSection.style.display = 'none';

        // Ensure hidden new item fields do not block submission
        if (itemNameInput) itemNameInput.required = false;
        if (quantityInput) quantityInput.required = false;

        // Load supplies for selected department
        const deptId = getSelectedDepartmentId();
        if (deptId) {
            loadSuppliesForDepartment(deptId);
        }
    } else {
        // Hide existing supply section
        existingSupplySection.style.display = 'none';
        
        // Show new items section
        newItemsSection.style.display = '';
        
        // Reset fields
        itemNameInput.readOnly = false;
        itemNameInput.style.backgroundColor = '';
        itemNameInput.value = '';
        unitSelect.disabled = false;
        unitSelect.style.backgroundColor = '';
        unitSelect.value = '';

        // Required fields are enforced unless items list has entries
        updateNewItemsRequiredState();
    }
}

// Existing supplies list for multi-item existing requests
let existingItems = [];

// Searchable supply list state and helpers
let loadedSupplies = [];
let filteredSupplies = [];
let selectedSupplyId = null;
let selectedSupply = null;

function renderSupplyList(source) {
    const listEl = document.getElementById('existing_supply_list');
    const emptyEl = document.getElementById('existing_supply_list_empty');
    if (!listEl) return;
    listEl.innerHTML = '';

    const items = (source || []).slice(0, 5);
    if (items.length === 0) {
        if (emptyEl) emptyEl.style.display = 'block';
        return;
    }
    if (emptyEl) emptyEl.style.display = 'none';

    items.forEach(s => {
        const li = document.createElement('li');
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'w-full text-left px-3 py-2 hover:bg-blue-50 flex justify-between items-center';
        btn.dataset.id = s.id;
        btn.onclick = () => onSupplySelected(s.id);
        if (String(selectedSupplyId) === String(s.id)) {
            btn.className += ' bg-blue-100 ring-2 ring-blue-500';
        }
        const left = document.createElement('span');
        left.className = 'text-sm font-medium text-gray-800';
        left.textContent = s.name;
        const right = document.createElement('span');
        right.className = 'text-xs text-gray-500';
        const qty = (s.available_quantity ?? s.quantity ?? 0);
        right.textContent = `${qty} ${s.unit} available`;
        btn.appendChild(left);
        btn.appendChild(right);
        li.appendChild(btn);
        listEl.appendChild(li);
    });
}

function filterExistingSupplies() {
    const input = document.getElementById('existing_supply_search');
    const q = (input ? input.value : '').trim().toLowerCase();
    if (!q) {
        filteredSupplies = loadedSupplies.slice();
    } else {
        filteredSupplies = loadedSupplies.filter(s => {
            const name = (s.name || '').toLowerCase();
            const unit = (s.unit || '').toLowerCase();
            return name.includes(q) || unit.includes(q);
        });
    }
    renderSupplyList(filteredSupplies);
}

function onSupplySelected(id) {
    selectedSupplyId = id;
    selectedSupply = loadedSupplies.find(s => String(s.id) === String(id)) || null;
    renderSupplyList(filteredSupplies.length ? filteredSupplies : loadedSupplies);
    if (selectedSupply) {
        loadVariantsForSupply(selectedSupply.id);
    }
}

function getSelectedDepartmentId() {
    const deptSelect = document.getElementById('department_id');
    if (deptSelect) {
        return deptSelect.value || '';
    }
    const hiddenDept = document.querySelector('input[name="department_id"][type="hidden"]');
    return hiddenDept ? hiddenDept.value || '' : '';
}

async function loadSuppliesForDepartment(departmentId) {
    const variantSection = document.getElementById('existing_variant_section');
    const variantSelect = document.getElementById('existing_variant_id');
    const variantHint = document.getElementById('existing_variant_hint');
    const searchEl = document.getElementById('existing_supply_search');
    if (!departmentId) return;
    selectedSupplyId = null;
    selectedSupply = null;
    if (searchEl) searchEl.value = '';
    if (variantSection) variantSection.style.display = 'none';
    if (variantSelect) variantSelect.innerHTML = '<option value="">Select variant</option>';
    if (variantHint) variantHint.textContent = '';

    try {
        const resp = await fetch(`/supply-requests/department-supplies/${departmentId}`);
        if (!resp.ok) throw new Error('Failed to load supplies');
        const data = await resp.json();
        loadedSupplies = (data && data.supplies) ? data.supplies.map(s => ({
            id: s.id,
            name: s.name,
            unit: s.unit,
            available_quantity: s.available_quantity,
        })) : [];
        filteredSupplies = loadedSupplies.slice();
        renderSupplyList(filteredSupplies);
    } catch (e) {
        console.error(e);
        loadedSupplies = [];
        filteredSupplies = [];
        renderSupplyList(filteredSupplies);
    }
}

async function loadVariantsForSupply(supplyId) {
    const variantSection = document.getElementById('existing_variant_section');
    const variantSelect = document.getElementById('existing_variant_id');
    const variantHint = document.getElementById('existing_variant_hint');
    if (!variantSelect) return;
    // Reset
    variantSelect.innerHTML = '<option value="">Select variant</option>';
    if (variantHint) variantHint.textContent = '';

    try {
        const resp = await fetch(`/issued-items/supply-variants/${supplyId}`);
        if (!resp.ok) throw new Error('Failed to load variants');
        const data = await resp.json();
        const variants = (data && data.variants) ? data.variants : [];
        if (variants.length > 0) {
            variants.forEach(v => {
                const opt = document.createElement('option');
                opt.value = v.id;
                const unit = v.unit ? ` ${v.unit}` : '';
                opt.textContent = `${v.name || v.display_name || 'Variant'} — Qty: ${v.quantity}${unit}`;
                variantSelect.appendChild(opt);
            });
            if (variantSection) variantSection.style.display = '';
            if (variantHint) variantHint.textContent = 'Select a variant if applicable.';
        } else {
            if (variantSection) variantSection.style.display = 'none';
        }
    } catch (e) {
        console.error(e);
        if (variantSection) variantSection.style.display = 'none';
    }
}

// Supply selection now handled by clickable list via onSupplySelected

function addExistingSupplyItem() {
    const quantityInput = document.getElementById('existing_quantity');
    const variantSelect = document.getElementById('existing_variant_id');
    const supplyId = selectedSupplyId;
    const quantity = parseInt(quantityInput.value, 10);
    const name = selectedSupply ? selectedSupply.name : '';
    const unit = selectedSupply ? (selectedSupply.unit || '') : '';
    const variantId = variantSelect && variantSelect.offsetParent !== null ? (variantSelect.value || '') : '';
    const variantName = variantId ? variantSelect.options[variantSelect.selectedIndex].text : '';

    if (!supplyId || !selectedSupply) {
        alert('Please select a supply item from the list.');
        return;
    }
    if (!quantity || quantity < 1) {
        alert('Please enter a quantity of at least 1.');
        return;
    }

    existingItems.push({ supply_id: supplyId, supply_variant_id: variantId || null, quantity, name: variantName ? `${name} - ${variantName}` : name, unit });
    selectedSupplyId = null;
    selectedSupply = null;
    const searchEl = document.getElementById('existing_supply_search');
    if (searchEl) searchEl.value = '';
    renderSupplyList(loadedSupplies);
    quantityInput.value = '';
    if (variantSelect) {
        variantSelect.innerHTML = '<option value="">Select variant</option>';
        const variantSection = document.getElementById('existing_variant_section');
        if (variantSection) variantSection.style.display = 'none';
    }
    renderExistingItemsList();
}

function renderExistingItemsList() {
    const container = document.getElementById('existing_items_list_container');
    const list = document.getElementById('existing_items_list');
    list.innerHTML = '';

    if (existingItems.length === 0) {
        container.style.display = 'none';
        return;
    }

    container.style.display = '';

    existingItems.forEach((item, idx) => {
        const row = document.createElement('div');
        row.className = 'flex items-center space-x-3';
        const unitLabel = item.unit ? ` (${item.unit})` : '';
        let hiddenInputs = `
            <input type="hidden" name="items[${idx}][supply_id]" value="${item.supply_id}">
            <input type="hidden" name="items[${idx}][quantity]" value="${item.quantity}">
        `;
        if (item.supply_variant_id) {
            hiddenInputs += `<input type=\"hidden\" name=\"items[${idx}][supply_variant_id]\" value=\"${item.supply_variant_id}\">`;
        }
        row.innerHTML = `
            ${hiddenInputs}
            <span class="text-sm text-gray-700 flex-1">${item.name}${unitLabel} — Qty: ${item.quantity}</span>
            <button type="button" class="px-3 py-1 text-xs bg-red-600 text-white rounded" onclick="removeExistingItem(${idx})">Remove</button>
        `;
        list.appendChild(row);
    });
}

function removeExistingItem(index) {
    existingItems.splice(index, 1);
    renderExistingItemsList();
}

// Manage new items list for multi-item new requests
let newItems = [];

function updateNewItemsRequiredState() {
    const itemNameInput = document.getElementById('item_name');
    const quantityInput = document.getElementById('quantity');
    const hasItems = newItems.length > 0;
    itemNameInput.required = !hasItems;
    quantityInput.required = !hasItems;
}

function addNewItem() {
    const itemNameInput = document.getElementById('item_name');
    const unitSelect = document.getElementById('unit');
    const quantityInput = document.getElementById('quantity');
    const categorySelect = document.getElementById('category_id');

    const name = itemNameInput.value.trim();
    const unit = unitSelect.value;
    const qty = parseInt(quantityInput.value, 10);
    const categoryId = categorySelect ? categorySelect.value : '';
    const categoryName = (categorySelect && categorySelect.selectedIndex > 0) ? categorySelect.options[categorySelect.selectedIndex].text : '';

    if (!name || !qty || qty < 1) {
        alert('Please provide item name and a quantity of at least 1. Unit is optional.');
        return;
    }

    newItems.push({ item_name: name, unit, quantity: qty, category_id: categoryId || '', category_name: categoryName || '' });
    itemNameInput.value = '';
    unitSelect.value = '';
    quantityInput.value = '';
    if (categorySelect) { categorySelect.value = ''; }

    renderNewItemsList();
    updateNewItemsRequiredState();
}

function renderNewItemsList() {
    const listContainer = document.getElementById('new_items_list_container');
    const list = document.getElementById('new_items_list');
    list.innerHTML = '';

    if (newItems.length === 0) {
        listContainer.style.display = 'none';
        return;
    }

    listContainer.style.display = '';

    newItems.forEach((item, idx) => {
        const row = document.createElement('div');
        row.className = 'flex items-center space-x-3';
        let hiddenInputs = `
            <input type="hidden" name="items[${idx}][item_name]" value="${item.item_name}">
            <input type="hidden" name="items[${idx}][unit]" value="${item.unit}">
            <input type="hidden" name="items[${idx}][quantity]" value="${item.quantity}">
        `;
        if (item.category_id) {
            hiddenInputs += `<input type=\"hidden\" name=\"items[${idx}][category_id]\" value=\"${item.category_id}\">`;
        }
        const unitLabel = item.unit ? ` (${item.unit})` : '';
        const categoryLabel = item.category_name ? ` — Category: ${item.category_name}` : '';
        row.innerHTML = `
            ${hiddenInputs}
            <span class="text-sm text-gray-700 flex-1">${item.item_name}${unitLabel} — Qty: ${item.quantity}${categoryLabel}</span>
            <button type="button" class="px-3 py-1 text-xs bg-red-600 text-white rounded" onclick="removeNewItem(${idx})">Remove</button>
        `;
        list.appendChild(row);
    });
}

function removeNewItem(index) {
    newItems.splice(index, 1);
    renderNewItemsList();
    updateNewItemsRequiredState();
}

// Initialize form state on page load
document.addEventListener('DOMContentLoaded', function() {
    toggleRequestType();
    // Initialize empty supply list UI
    renderSupplyList([]);
    const deptSelect = document.getElementById('department_id');
    if (deptSelect) {
        deptSelect.addEventListener('change', function() {
            const existingSupplyRadio = document.getElementById('existing_supply');
            if (existingSupplyRadio && existingSupplyRadio.checked) {
                const deptId = getSelectedDepartmentId();
                if (deptId) {
                    loadSuppliesForDepartment(deptId);
                }
            }
        });
    }
    // Prevent submitting existing requests without any items added
    const form = document.querySelector('form[action*="supply-requests.store"]');
    if (form) {
        form.addEventListener('submit', function(e) {
            const existingSupplyRadio = document.getElementById('existing_supply');
            if (existingSupplyRadio && existingSupplyRadio.checked && existingItems.length === 0) {
                e.preventDefault();
                alert('Please add at least one supply item to your request.');
            }
        });
    }
});
</script>
@endsection