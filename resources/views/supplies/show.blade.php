@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header Section -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Supply Details</h1>
        <div class="flex space-x-2">
            @if(auth()->user()->hasAdminPrivileges())
                <a href="{{ route('supplies.edit', $supply) }}" 
                   class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Edit Supply
                </a>
            @endif

            @if(auth()->user()->hasRole('student'))
                <a href="{{ route('loan-requests.index') }}" 
                   class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back to Requests
                </a>
            @elseif(auth()->user()->hasRole('adviser'))
                <a href="{{ route('supply-requests.index') }}" 
                   class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back to Requests
                </a>
            @elseif(auth()->user()->hasRole('dean'))
                <a href="{{ route('dean.departments') }}#issued-items" 
                   class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back to Supplies
                </a>
            @else
                <a href="{{ route('supplies.index') }}" 
                   class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back to Supplies
                </a>
            @endif
        </div>
    </div>

    <!-- Supply Information Card -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden mb-6">
        <div class="px-6 py-4 bg-gray-50 border-b">
            <h2 class="text-xl font-semibold text-gray-800">{{ $supply->name }}</h2>
        </div>
        
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Basic Information -->
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>
                    
                    <div class="space-y-3">
                        <div>
                            <span class="text-sm font-medium text-gray-500">Name:</span>
                            <p class="text-sm text-gray-900">{{ $supply->name }}</p>
                        </div>
                        
                        @if($supply->description)
                            <div>
                                <span class="text-sm font-medium text-gray-500">Description:</span>
                                <p class="text-sm text-gray-900">{{ $supply->description }}</p>
                            </div>
                        @endif
                        
                        @if($supply->tin)
                            <div>
                                <span class="text-sm font-medium text-gray-500">Tin:</span>
                                <p class="text-sm text-gray-900">{{ $supply->tin }}</p>
                            </div>
                        @endif

                        @if($supply->sku)
                            <div>
                                <span class="text-sm font-medium text-gray-500">SKU:</span>
                                <p class="text-sm text-gray-900">{{ $supply->sku }}</p>
                            </div>
                        @endif

                        <div>
                            <span class="text-sm font-medium text-gray-500">Current Quantity:</span>
                            <p class="text-sm text-gray-900">{{ $supply->hasVariants() ? $supply->getTotalVariantQuantity() : $supply->quantity }} {{ $supply->unit }}</p>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-500">Currently Available:</span>
                            <p class="text-sm text-gray-900">{{ $supply->availableQuantity() }} {{ $supply->unit }}</p>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-500">Missing:</span>
                            @php
                                $totalMissing = 0;
                                if ($supply->hasVariants()) {
                                    foreach ($supply->variants as $variant) {
                                        $totalMissing += method_exists($variant, 'totalMissingCount') ? (int) $variant->totalMissingCount() : 0;
                                    }
                                } else {
                                    $totalMissing = method_exists($supply, 'totalMissingCount') ? (int) $supply->totalMissingCount() : 0;
                                }
                            @endphp
                            <p class="text-sm text-gray-900">{{ $totalMissing }} {{ $supply->unit }}</p>
                        </div>
                        
                        <div>
                            <span class="text-sm font-medium text-gray-500">Unit:</span>
                            <p class="text-sm text-gray-900">{{ ucfirst($supply->unit) }}</p>
                        </div>

                        <div>
                            <span class="text-sm font-medium text-gray-500">Current Location:</span>
                            @if($supply->location)
                                @php
                                    $child = $supply->location;
                                    $parent = $child->parent;
                                @endphp
                                <p class="text-sm">
                                    @if($parent)
                                        <a href="{{ route('locations.show', $parent) }}" class="text-blue-700 hover:underline">{{ $parent->name }}</a>
                                        <span class="text-gray-700"> : </span>
                                        <a href="{{ route('locations.show', $child) }}" class="text-blue-700 hover:underline">{{ $child->name }}</a>
                                    @else
                                        <a href="{{ route('locations.show', $child) }}" class="text-blue-700 hover:underline">{{ $child->name }}</a>
                                    @endif
                                </p>
                            @else
                                <p class="text-sm text-gray-900">
                                    Not assigned
                                    @if(auth()->user()->hasAdminPrivileges())
                                        — <a href="#" class="text-blue-700 hover:underline" onclick="openAssignLocationModal(event)">assign location?</a>
                                    @endif
                                </p>
                            @endif
                        </div>
                        
                        <div>
                            <span class="text-sm font-medium text-gray-500">Supply Type:</span>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                @if($supply->supply_type === 'consumable') bg-orange-100 text-orange-800
                                @elseif($supply->supply_type === 'borrowable') bg-blue-100 text-blue-800
                                @elseif($supply->supply_type === 'grantable') bg-purple-100 text-purple-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ $supply->getSupplyTypeLabel() }}
                            </span>
                        </div>
                        
                        <div>
                            <span class="text-sm font-medium text-gray-500">Minimum Stock Level:</span>
                            <p class="text-sm text-gray-900">{{ $supply->minimum_stock_level }} {{ $supply->unit }}</p>
                        </div>
                        
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-medium text-gray-500">Status:</span>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                @if($supply->status === 'active') bg-green-100 text-green-800
                                @elseif($supply->status === 'inactive') bg-gray-100 text-gray-800
                                @elseif($supply->status === 'damaged') bg-red-100 text-red-800
                                @endif">
                                {{ ucfirst($supply->status) }}
                            </span>
                            @if($supply->damage_severity)
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                    Damaged — Severity: {{ ucfirst($supply->damage_severity) }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- QR Code -->
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">QR Code</h3>
                    <div class="text-center">
                        <img src="{{ $supply->getQrCodeImageUrl() }}" 
                             alt="QR Code for {{ $supply->name }}" 
                             class="mx-auto border border-gray-300 rounded-lg shadow-sm">
                        <p class="text-xs text-gray-500 mt-2">Scan to view supply details</p>
                        <div class="mt-3 flex justify-center space-x-2">
                            <a href="{{ route('qr.code.generate', ['supply' => $supply->id, 'action' => 'actions', 'size' => 600, 'format' => 'png']) }}"
                                class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded-lg transition duration-200 inline-flex items-center"
                                download="supply-{{ $supply->id }}-qr.png">
                                 <i class="fas fa-download mr-2"></i>
                                 Download PNG
                             </a>
                            <a href="{{ route('qr.code.generate', ['supply' => $supply->id, 'action' => 'actions', 'format' => 'svg']) }}"
                                class="bg-gray-700 hover:bg-gray-800 text-white font-medium py-2 px-4 rounded-lg transition duration-200 inline-flex items-center"
                                download="supply-{{ $supply->id }}-qr.svg">
                                 <i class="fas fa-download mr-2"></i>
                                 Download SVG
                             </a>
                         </div>
                    </div>
                </div>
            </div>
            
            <!-- Status Management Actions -->
            @if(auth()->user()->hasAdminPrivileges())
            <div class="mt-6 pt-6 border-t border-gray-200">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Status Management</h3>
                <div class="flex flex-wrap gap-2">
                    @if($supply->status !== 'active')
                        <form action="{{ route('supplies.mark-active', $supply) }}" method="POST" class="inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" 
                                    class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-sm"
                                    onclick="return confirm('Mark this supply as active?')">
                                Mark as Active
                            </button>
                        </form>
                    @endif
                    
                    @if($supply->status !== 'inactive')
                        <form action="{{ route('supplies.mark-inactive', $supply) }}" method="POST" class="inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" 
                                    class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded text-sm"
                                    onclick="return confirm('Mark this supply as inactive?')">
                                Mark as Inactive
                            </button>
                        </form>
                    @endif
                    
                    @if($supply->status !== 'damaged')
                        <form action="{{ route('supplies.mark-damaged', $supply) }}" method="POST" class="inline flex items-center gap-2">
                            @csrf
                            @method('PATCH')
                            <label for="severity" class="text-sm text-gray-700">Severity:</label>
                            <select name="severity" id="severity" class="border border-gray-300 rounded px-2 py-1 text-sm">
                                <option value="minor">Minor</option>
                                <option value="moderate">Moderate</option>
                                <option value="severe">Severe</option>
                            </select>
                            <button type="submit" 
                                    class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded text-sm">
                                Mark as Damaged
                            </button>
                        </form>
                    @endif
                    

                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Stock Status Section -->
    @if(!$supply->isBorrowable())
    <div class="bg-white shadow-md rounded-lg overflow-hidden mb-6">
        <div class="px-6 py-4 bg-gray-50 border-b">
            <h2 class="text-xl font-semibold text-gray-800">Stock Status</h2>
        </div>
        <div class="p-6">
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Current Stock Level</h3>
                    
                    <div class="space-y-3">
                        <div>
                            <span class="text-sm font-medium text-gray-500">Stock Status:</span>
                            @if($supply->availableQuantity() <= 0)
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                    Out of Stock
                                </span>
                            @elseif($supply->availableQuantity() <= $supply->minimum_stock_level)
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                    Low Stock
                                </span>
                            @else
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                    In Stock
                                </span>
                            @endif
                        </div>
                        
                        <div>
                            <span class="text-sm font-medium text-gray-500">Stock Level:</span>
                            <div class="mt-1">
                                <div class="bg-gray-200 rounded-full h-2">
                                    @php
                                        $percentage = $supply->minimum_stock_level > 0 ? min(100, ($supply->quantity / ($supply->minimum_stock_level * 2)) * 100) : 100;
                                        $colorClass = $percentage <= 25 ? 'bg-red-500' : ($percentage <= 50 ? 'bg-yellow-500' : 'bg-green-500');
                                    @endphp
                                    <div class="{{ $colorClass }} h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">{{ number_format($percentage, 1) }}% of recommended level</p>
                            </div>
                        </div>
                        
                        <div>
                            <span class="text-sm font-medium text-gray-500">Created:</span>
                            <p class="text-sm text-gray-900">{{ $supply->created_at->format('M d, Y \a\t g:i A') }}</p>
                        </div>
                        
                        <div>
                            <span class="text-sm font-medium text-gray-500">Last Updated:</span>
                            <p class="text-sm text-gray-900">{{ $supply->updated_at->format('M d, Y \a\t g:i A') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Variants Section -->
    @if($supply->has_variants)
        <div class="bg-white shadow-md rounded-lg overflow-hidden mb-6">
            <div class="px-6 py-4 bg-gray-50 border-b flex justify-between items-center">
                <h2 class="text-xl font-semibold text-gray-800">Supply Variants</h2>
                @if(auth()->user()->hasAdminPrivileges())
                    <a href="{{ route('supply-variants.create', ['supply' => $supply->id]) }}" 
                       class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm">
                        Add Variant
                    </a>
                @endif
            </div>
            <div class="p-6">
                @if($supply->variants->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Variant</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Attributes</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Currently Available</th>
                                    <!-- Columns Hidden: Missing, Damaged, Price -->
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($supply->variants as $variant)
                                    <tr onclick="window.location='{{ route('supply-variants.show', $variant) }}'" class="hover:bg-gray-50 cursor-pointer">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $variant->variant_name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            @php
                                                $attrs = $variant->attributes;
                                                if (is_string($attrs)) {
                                                    $decoded = json_decode($attrs, true);
                                                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                                        $attrs = $decoded;
                                                    } else {
                                                        $attrs = $attrs !== '' ? ['' => $attrs] : [];
                                                    }
                                                } elseif (!is_array($attrs)) {
                                                    $attrs = [];
                                                }
                                            @endphp
                                            @if(!empty($attrs))
                                                @foreach($attrs as $key => $value)
                                                    <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800 mr-1 mb-1">
                                                        {{ $key ? ucfirst($key) . ': ' : '' }}{{ is_array($value) ? implode(', ', $value) : $value }}
                                                    </span>
                                                @endforeach
                                            @else
                                                <span class="text-gray-400">No attributes</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $variant->sku ?? 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <span class="font-medium">{{ $variant->quantity }}</span>
                                            <span class="text-gray-500">{{ $supply->unit }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <span class="font-medium">{{ method_exists($variant, 'availableQuantity') ? $variant->availableQuantity() : $variant->quantity }}</span>
                                            <span class="text-gray-500">{{ $supply->unit }}</span>
                                        </td>
                                        <!-- Cells Hidden: Missing, Damaged, Price -->
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if(isset($variant->status) && $variant->status !== 'active')
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                                    Disabled
                                                </span>
                                            @elseif($variant->quantity <= 0)
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                                    Out of Stock
                                                </span>
                                            @elseif($variant->quantity <= 5)
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                    Low Stock
                                                </span>
                                            @else
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                                    In Stock
                                                </span>
                                            @endif
                                        </td>
                                        
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Variant Summary -->
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="bg-blue-50 p-4 rounded-lg">
                                <h4 class="text-sm font-medium text-blue-800">Total Variants</h4>
                                <p class="text-2xl font-bold text-blue-900">{{ $supply->variants->count() }}</p>
                            </div>
                            <div class="bg-green-50 p-4 rounded-lg">
                                <h4 class="text-sm font-medium text-green-800">Total Stock</h4>
                                <p class="text-2xl font-bold text-green-900">{{ $supply->getTotalVariantQuantity() }}</p>
                            </div>
                            <div class="bg-yellow-50 p-4 rounded-lg">
                                <h4 class="text-sm font-medium text-yellow-800">Available Variants</h4>
                                <p class="text-2xl font-bold text-yellow-900">{{ $supply->getAvailableVariants()->count() }}</p>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="text-center py-8">
                        <p class="text-gray-500 mb-4">No variants have been created for this supply yet.</p>
                        @if(auth()->user()->hasAdminPrivileges())
                            <a href="{{ route('supply-variants.create', ['supply' => $supply->id]) }}" 
                               class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Create First Variant
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    @else
        <!-- Enable Variants Option -->
        @if(auth()->user()->hasAdminPrivileges())
            <div class="bg-white shadow-md rounded-lg overflow-hidden mb-6">
                <div class="px-6 py-4 bg-gray-50 border-b">
                    <h2 class="text-xl font-semibold text-gray-800">Variant Management</h2>
                </div>
                <div class="p-6 text-center">
                    <p class="text-gray-500 mb-4">This supply doesn't use variants. Enable variants to manage different sizes, colors, or other variations.</p>
                    <form action="{{ route('supplies.enable-variants', $supply) }}" method="POST" class="inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit" 
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                                onclick="return confirm('Enable variants for this supply? This will allow you to manage different variations like sizes, colors, etc.')">
                            Enable Variants
                        </button>
                    </form>
                </div>
            </div>
        @endif
    @endif

    @if(auth()->user()->hasAdminPrivileges())
    <div id="assignLocationModal" class="fixed inset-0 bg-black bg-opacity-40 hidden z-50" aria-hidden="true">
        <div class="min-h-screen flex items-center justify-center">
            <div class="bg-white w-full max-w-xl rounded-lg shadow-lg">
                <div class="px-6 py-4 border-b flex justify-between items-center">
                    <h3 class="text-lg font-medium text-gray-900">Assign Location</h3>
                    <button class="text-gray-600 hover:text-gray-800" onclick="closeAssignLocationModal()">✕</button>
                </div>
                <div class="p-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-700">Navigate:</div>
                        <p id="assignLocFeedback" class="text-xs text-gray-500"></p>
                    </div>
                    <div id="assignLocBreadcrumb" class="flex flex-wrap gap-1 text-sm"></div>
                    <div id="assignLocList" class="max-h-56 overflow-y-auto border border-gray-200 rounded"></div>
                    <form id="assignLocForm" method="POST" action="{{ route('supplies.assign-location', $supply) }}" class="mt-2">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="location_id" id="assignLocId">
                        <div class="text-sm text-gray-700 mb-3">Selected: <span id="assignLocSelectedName" class="font-medium">None</span></div>
                        <div class="flex justify-end gap-2">
                            <button type="button" class="px-4 py-2 bg-gray-100 text-gray-800 rounded hover:bg-gray-200" onclick="closeAssignLocationModal()">Cancel</button>
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700" onclick="return validateAssignLocation()">Assign</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
    const elModal = document.getElementById('assignLocationModal');
    const elList = document.getElementById('assignLocList');
    const elBreadcrumb = document.getElementById('assignLocBreadcrumb');
    const elFeedback = document.getElementById('assignLocFeedback');
    const elSelectedName = document.getElementById('assignLocSelectedName');
    const elSelectedId = document.getElementById('assignLocId');
    let locPath = [];

    function openAssignLocationModal(e){ if(e) e.preventDefault(); elModal.classList.remove('hidden'); elSelectedName.textContent='None'; elSelectedId.value=''; locPath=[]; renderBreadcrumb(); loadParents(); }
    function closeAssignLocationModal(){ elModal.classList.add('hidden'); }
    function validateAssignLocation(){ if(!elSelectedId.value){ alert('Please select a location.'); return false; } return true; }

    function renderBreadcrumb(){
        const items = ['Parents'].concat(locPath.map(p => p.name));
        elBreadcrumb.innerHTML = items.map((name, idx) => {
            const isLast = idx === items.length - 1;
            return `<button type="button" class="px-2 py-1 rounded ${isLast ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800 hover:bg-gray-200'}" onclick="goToLevel(${idx-1})">${name}</button>`;
        }).join('<span class="text-gray-400"> / </span>');
    }

    function renderList(items){
        if(!Array.isArray(items) || items.length === 0){ elList.innerHTML = '<div class="p-3 text-sm text-gray-500">No child locations</div>'; return; }
        elList.innerHTML = items.map(i => (
            `<button type="button" class="w-full text-left px-3 py-2 hover:bg-gray-100" onclick="selectAndBrowse(${i.id}, '${(i.name||'').replace(/'/g, "&#39;")}', '${(i.description||'').replace(/'/g, "&#39;")}')">
                <div class="text-sm font-medium text-gray-800">${i.name}</div>
                <div class="text-xs text-gray-600">${i.description ?? ''}</div>
            </button>`
        )).join('');
    }

    async function loadParents(){
        elFeedback.textContent = 'Loading parents...';
        try{
            const res = await fetch(`{{ route('locations.parents') }}`);
            const data = await res.json();
            renderList(data.results || []);
            elFeedback.textContent = '';
        }catch(e){ elFeedback.textContent = 'Failed to load'; }
    }

    async function loadChildren(parentId){
        elFeedback.textContent = 'Loading children...';
        try{
            const url = `{{ route('locations.children', ['location' => '__ID__']) }}`.replace('__ID__', parentId);
            const res = await fetch(url);
            const data = await res.json();
            renderList(data.results || []);
            elFeedback.textContent = '';
        }catch(e){ elFeedback.textContent = 'Failed to load'; }
    }

    function selectAndBrowse(id, name){ elSelectedId.value = id; elSelectedName.textContent = name; locPath.push({id, name}); renderBreadcrumb(); loadChildren(id); }
    function goToLevel(level){
        if(level < 0){ locPath = []; renderBreadcrumb(); loadParents(); return; }
        locPath = locPath.slice(0, level+1);
        renderBreadcrumb();
        const current = locPath[locPath.length - 1];
        if(current){ loadChildren(current.id); } else { loadParents(); }
    }
    </script>
    @endif

    <!-- Categories and Suppliers -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <!-- Categories -->
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="px-6 py-4 bg-gray-50 border-b">
                <h3 class="text-lg font-semibold text-gray-800">Categories</h3>
            </div>
            <div class="p-6">
                @if($supply->categories->count() > 0)
                    <div class="flex flex-wrap gap-2">
                        @foreach($supply->categories as $category)
                            <span class="inline-flex px-3 py-1 text-sm font-medium rounded-full bg-blue-100 text-blue-800">
                                {{ $category->name }}
                            </span>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 text-sm">No categories assigned</p>
                @endif
            </div>
        </div>

        <!-- Suppliers -->
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="px-6 py-4 bg-gray-50 border-b">
                <h3 class="text-lg font-semibold text-gray-800">Suppliers</h3>
            </div>
            <div class="p-6">
                @if($supply->suppliers->count() > 0)
                    <div class="space-y-3">
                        @foreach($supply->suppliers as $supplier)
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div>
                                    <p class="font-medium text-gray-900">{{ $supplier->name }}</p>
                                    @if($supplier->contact_person)
                                        <p class="text-sm text-gray-500">Contact: {{ $supplier->contact_person }}</p>
                                    @endif
                                </div>
                                @if($supplier->phone1 || $supplier->email)
                                    <div class="text-right">
                                        @if($supplier->phone1)
                                            <p class="text-sm text-gray-600">{{ $supplier->phone1 }}</p>
                                        @endif
                                        @if($supplier->email)
                                            <p class="text-sm text-gray-600">{{ $supplier->email }}</p>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 text-sm">No suppliers assigned</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Borrowing Summary removed per request -->

    <!-- Two-column layout: Current Borrowing Status (left) and Borrowing History (right) -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <!-- Current Borrowing Status -->
    @php
        // Regular currently borrowed
        $activeBorrowedItems = $supply->borrowedItems->where('returned_at', null);
        // Inter-department currently borrowed for this supply (status=active)
        $activeInterDeptBorrowedItems = \App\Models\InterDepartmentBorrowedItem::with(['borrowedBy','borrowingDepartment','lendingDepartment','issuedItem'])
            ->whereHas('issuedItem', function($q) use ($supply) { $q->where('supply_id', $supply->id); })
            ->where('status', 'active')
            ->get();
    @endphp
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="px-6 py-4 bg-gray-50 border-b">
            <h3 class="text-lg font-semibold text-gray-800">Current Borrowing Status</h3>
        </div>
        <div class="p-6">
            @php 
                $hasAnyActive = $activeBorrowedItems->count() > 0 || $activeInterDeptBorrowedItems->count() > 0; 
                // Ensure pagination page size is always defined to avoid division by zero in subsequent sections
                $perPage = 5;
            @endphp
            @if($hasAnyActive)
                <div class="space-y-4">
                    @if($activeBorrowedItems->count() > 0)
                        <h4 class="text-sm font-semibold text-gray-800">Regular Borrowed</h4>
                        @php
                            $perPage = 5;
                            $currPage = max(1, (int) request()->input('current_page', 1));
                            $currTotal = $activeBorrowedItems->count();
                            $currPages = max(1, (int) ceil($currTotal / $perPage));
                        @endphp
                        <div class="flex justify-between items-center mb-2">
                            <div class="text-xs text-gray-500">Page {{ $currPage }} of {{ $currPages }}</div>
                            <div class="space-x-2">
                                <a href="{{ route('supplies.show', $supply) }}?current_page={{ max(1, $currPage - 1) }}" class="px-2 py-1 rounded border text-xs {{ $currPage <= 1 ? 'opacity-50 pointer-events-none' : '' }}">Prev</a>
                                <a href="{{ route('supplies.show', $supply) }}?current_page={{ min($currPages, $currPage + 1) }}" class="px-2 py-1 rounded border text-xs {{ $currPage >= $currPages ? 'opacity-50 pointer-events-none' : '' }}">Next</a>
                            </div>
                        </div>
                        <div class="space-y-3">
                            @foreach($activeBorrowedItems->slice(($currPage - 1) * $perPage, $perPage) as $borrowedItem)
                                <div class="flex items-start justify-between p-3 bg-gray-50 rounded-lg">
                                    <div>
                                        <p class="font-medium text-gray-900">
                                            Borrowed by: {{ optional($borrowedItem->user)->name ?? 'Unknown User' }}
                                        </p>
                                        <p class="text-sm text-gray-600">
                                            Department: {{ optional($borrowedItem->department)->department_name ?? 'N/A' }}
                                        </p>
                                        <p class="text-sm text-gray-600">
                                            Quantity: {{ $borrowedItem->quantity }} {{ $supply->unit }}
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm text-gray-500">
                                            Borrowed on: {{ $borrowedItem->borrowed_at ? \Carbon\Carbon::parse($borrowedItem->borrowed_at)->format('M d, Y') : 'N/A' }}
                                        </p>
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800 mt-2">
                                            Currently Borrowed
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    @if($activeInterDeptBorrowedItems->count() > 0)
                        <h4 class="text-sm font-semibold text-gray-800">Inter-Department Borrowed</h4>
                        @php
                            $currInterPage = max(1, (int) request()->input('current_inter_page', 1));
                            $currInterTotal = $activeInterDeptBorrowedItems->count();
                            $currInterPages = max(1, (int) ceil($currInterTotal / $perPage));
                        @endphp
                        <div class="flex justify-between items-center mb-2">
                            <div class="text-xs text-gray-500">Page {{ $currInterPage }} of {{ $currInterPages }}</div>
                            <div class="space-x-2">
                                <a href="{{ route('supplies.show', $supply) }}?current_inter_page={{ max(1, $currInterPage - 1) }}" class="px-2 py-1 rounded border text-xs {{ $currInterPage <= 1 ? 'opacity-50 pointer-events-none' : '' }}">Prev</a>
                                <a href="{{ route('supplies.show', $supply) }}?current_inter_page={{ min($currInterPages, $currInterPage + 1) }}" class="px-2 py-1 rounded border text-xs {{ $currInterPage >= $currInterPages ? 'opacity-50 pointer-events-none' : '' }}">Next</a>
                            </div>
                        </div>
                        <div class="space-y-3">
                            @foreach($activeInterDeptBorrowedItems->slice(($currInterPage - 1) * $perPage, $perPage) as $item)
                                <div class="flex items-start justify-between p-3 bg-purple-50 rounded-lg">
                                    <div>
                                        <p class="font-medium text-gray-900">
                                            Borrowed by: {{ optional($item->borrowedBy)->name ?? 'Unknown User' }}
                                        </p>
                                        <p class="text-sm text-gray-600">
                                            Departments: From {{ optional($item->lendingDepartment)->department_name ?? 'N/A' }}
                                            to {{ optional($item->borrowingDepartment)->department_name ?? 'N/A' }}
                                        </p>
                                        <p class="text-sm text-gray-600">
                                            Quantity: {{ $item->quantity_borrowed }} {{ $supply->unit }}
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm text-gray-500">
                                            Borrowed on: {{ $item->borrowed_date ? $item->borrowed_date->format('M d, Y') : 'N/A' }}
                                        </p>
                                        @if($item->expected_return_date)
                                            <p class="text-xs text-gray-500">Due: {{ $item->expected_return_date->format('M d, Y') }}</p>
                                        @endif
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800 mt-2">
                                            Active Inter-Dept Loan
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @else
                <p class="text-gray-500 text-sm">Not currently borrowed.</p>
            @endif
        </div>
    </div>

    <!-- Borrowing History -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="px-6 py-4 bg-gray-50 border-b">
            <h3 class="text-lg font-semibold text-gray-800">Borrowing History</h3>
        </div>
        <div class="p-6">
            @php
                $historicalInterDeptBorrowedItems = \App\Models\InterDepartmentBorrowedItem::with(['borrowedBy','borrowingDepartment','lendingDepartment','issuedItem'])
                    ->whereHas('issuedItem', function($q) use ($supply) { $q->where('supply_id', $supply->id); })
                    ->get();
            @endphp
            @if($supply->borrowedItems->count() > 0 || $historicalInterDeptBorrowedItems->count() > 0)
                @php
                    $perPage = 5;
                    $historyPage = max(1, (int) request()->input('history_page', 1));
                    $totalHistory = $supply->borrowedItems->count();
                    $totalHistoryPages = max(1, (int) ceil($totalHistory / $perPage));
                @endphp
                <div class="flex justify-between items-center mb-3">
                    <div class="text-sm text-gray-500">Page {{ $historyPage }} of {{ $totalHistoryPages }}</div>
                    <div class="space-x-2">
                        <a href="{{ route('supplies.show', $supply) }}?history_page={{ max(1, $historyPage - 1) }}" class="px-3 py-1 rounded border text-sm {{ $historyPage <= 1 ? 'opacity-50 pointer-events-none' : '' }}">Prev</a>
                        <a href="{{ route('supplies.show', $supply) }}?history_page={{ min($totalHistoryPages, $historyPage + 1) }}" class="px-3 py-1 rounded border text-sm {{ $historyPage >= $totalHistoryPages ? 'opacity-50 pointer-events-none' : '' }}">Next</a>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Borrower</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Borrowed Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Returned Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($supply->borrowedItems->sortByDesc('borrowed_at')->slice(($historyPage - 1) * $perPage, $perPage) as $borrowedItem)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        @if($borrowedItem->user)
                                            <a href="{{ route('users.show', $borrowedItem->user) }}" class="text-blue-600 hover:underline">
                                                {{ $borrowedItem->user->name }}
                                            </a>
                                        @else
                                            Unknown User
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        @if($borrowedItem->department)
                                            <a href="{{ route('departments.show', $borrowedItem->department) }}" class="text-blue-600 hover:underline">
                                                {{ $borrowedItem->department->department_name }}
                                            </a>
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $borrowedItem->quantity }} {{ $supply->unit }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $borrowedItem->borrowed_at ? \Carbon\Carbon::parse($borrowedItem->borrowed_at)->format('M d, Y') : 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $borrowedItem->returned_at ? \Carbon\Carbon::parse($borrowedItem->returned_at)->format('M d, Y') : '—' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($borrowedItem->returned_at)
                                            @php $status = $borrowedItem->returned_status; @endphp
                                            @if($status === 'returned_with_missing')
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-orange-100 text-orange-800">Returned w/ Missing</span>
                                            @elseif($status === 'returned_with_damage')
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Returned w/ Damage</span>
                                            @else
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Returned</span>
                                            @endif
                                        @else
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Borrowed</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($historicalInterDeptBorrowedItems->count() > 0)
                @php
                    $interHistoryPage = max(1, (int) request()->input('inter_history_page', 1));
                    $interTotal = $historicalInterDeptBorrowedItems->count();
                    $interPages = max(1, (int) ceil($interTotal / $perPage));
                @endphp
                <div class="flex justify-between items-center mb-3 mt-6">
                    <div class="text-sm text-gray-500">Page {{ $interHistoryPage }} of {{ $interPages }}</div>
                    <div class="space-x-2">
                        <a href="{{ route('supplies.show', $supply) }}?inter_history_page={{ max(1, $interHistoryPage - 1) }}" class="px-3 py-1 rounded border text-sm {{ $interHistoryPage <= 1 ? 'opacity-50 pointer-events-none' : '' }}">Prev</a>
                        <a href="{{ route('supplies.show', $supply) }}?inter_history_page={{ min($interPages, $interHistoryPage + 1) }}" class="px-3 py-1 rounded border text-sm {{ $interHistoryPage >= $interPages ? 'opacity-50 pointer-events-none' : '' }}">Next</a>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Borrower</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Departments</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Borrowed Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Returned Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($historicalInterDeptBorrowedItems->sortByDesc('borrowed_date')->slice(($interHistoryPage - 1) * $perPage, $perPage) as $item)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        @if($item->borrowedBy)
                                            <a href="{{ route('users.show', $item->borrowedBy) }}" class="text-blue-600 hover:underline">
                                                {{ $item->borrowedBy->name }}
                                            </a>
                                        @else
                                            Unknown User
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        From
                                        @if($item->lendingDepartment)
                                            <a href="{{ route('departments.show', $item->lendingDepartment) }}" class="text-blue-600 hover:underline">
                                                {{ $item->lendingDepartment->department_name }}
                                            </a>
                                        @else
                                            N/A
                                        @endif
                                        →
                                        @if($item->borrowingDepartment)
                                            <a href="{{ route('departments.show', $item->borrowingDepartment) }}" class="text-blue-600 hover:underline">
                                                {{ $item->borrowingDepartment->department_name }}
                                            </a>
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $item->quantity_borrowed }} {{ $supply->unit }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $item->borrowed_date ? $item->borrowed_date->format('M d, Y') : 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $item->actual_return_date ? $item->actual_return_date->format('M d, Y') : '—' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($item->status === 'returned')
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Returned</span>
                                        @else
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">Active</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            @else
                <p class="text-gray-500 text-sm">No borrowing history for this supply.</p>
            @endif
        </div>
    </div>
    </div> <!-- end two-column grid -->
</div>
@endsection
