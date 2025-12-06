@extends('layouts.app')

@section('content')

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex gap-6" id="supplies-layout">

                <!-- Main Content -->
                <div class="flex-1" id="suppliesMain">
            <!-- Flash Messages (success handled globally in layout) -->

            @if (session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Header Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Supply Management</h3>
                            <p class="text-sm text-gray-600">Manage your supply inventory, stock levels, and manual receipts</p>
                        </div>
                    </div>

                    <!-- Tab Navigation -->
                    <div class="border-b border-gray-200 mb-4">
                        <nav class="-mb-px flex space-x-8">
                            <a href="{{ route('supplies.index', ['tab' => 'supplies'] + request()->only(['search', 'low_stock'])) }}" 
                               class="py-2 px-1 border-b-2 font-medium text-sm {{ ($tab ?? 'supplies') === 'supplies' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                                Supply Inventory
                            </a>
                            @can('viewAny', App\Models\ManualReceipt::class)
                            <a href="{{ route('supplies.index', ['tab' => 'receipts'] + request()->only(['search', 'low_stock'])) }}" 
                               class="py-2 px-1 border-b-2 font-medium text-sm {{ ($tab ?? 'supplies') === 'receipts' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                                Manual Receipts
                            </a>
                            @endcan
                        </nav>
                    </div>

                    @if(($tab ?? 'supplies') === 'supplies')
                    <!-- Search Form for Supplies -->
                    <form method="GET" action="{{ route('supplies.index') }}" class="mb-4" id="supplies-search-form">
                        <input type="hidden" name="tab" value="supplies">
                        <div class="flex gap-4">
                            <div class="flex-1">
                                <input type="text" 
                                       name="search" 
                                       value="{{ $search }}" 
                                       placeholder="Search supplies by name..." 
                                       id="live-search-input"
                                       autocomplete="off"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            @if($search)
                            <a href="{{ route('supplies.index', ['tab' => 'supplies']) }}" 
                               class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                                Clear
                            </a>
                            @endif
                        </div>
                        
                        <!-- Low Stock Filter Indicator -->
                        @if($lowStock)
                        <div class="mt-3 flex items-center justify-between bg-red-50 border border-red-200 rounded-lg p-3">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-red-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                </svg>
                                <span class="text-red-800 font-medium">Showing Low Stock Items Only</span>
                            </div>
                            <a href="{{ route('supplies.index', ['tab' => 'supplies']) }}" 
                               class="text-red-600 hover:text-red-800 text-sm font-medium">
                                Show All Items →
                            </a>
                        </div>
                        @endif
                    </form>
                    @endif
                </div>
            </div>

            @if(($tab ?? 'supplies') === 'supplies')
            <!-- Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-blue-50 p-4 rounded-lg">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Total Supplies</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ $supplies->total() }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-yellow-50 p-4 rounded-lg">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-yellow-500 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Low Stock Items</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ \App\Models\Supply::whereColumn('quantity', '<=', 'minimum_stock_level')->count() }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-green-50 p-4 rounded-lg">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Pending Requests</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ $pendingRequests }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Supplies Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if($supplies->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Supply Name
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Description
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Type
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Quantity
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Unit
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200" id="supplies-table-body">
                                @foreach($supplies as $supply)
                                @php
                                    $locationNames = [];
                                    if ($supply->location && $supply->location->name) { $locationNames[] = strtolower($supply->location->name); }
                                    foreach ($supply->inventories as $inv) {
                                        if ($inv->location && $inv->location->name) { $locationNames[] = strtolower($inv->location->name); }
                                    }
                                    $locationAttr = implode('|', array_unique($locationNames));
                                @endphp
                                <tr class="hover:bg-gray-50 cursor-pointer supply-row" 
                                    data-name="{{ strtolower($supply->name) }}" 
                                    data-desc="{{ strtolower($supply->description ?? '') }}"
                                    data-locations="{{ $locationAttr }}"
                                    data-url="@if(auth()->user()->hasRole('student')){{ route('student.supplies.show', $supply) }}@elseif(auth()->user()->hasRole('adviser')){{ route('adviser.supplies.show', $supply) }}@elseif(auth()->user()->hasRole('dean')){{ route('dean.supplies.show', $supply) }}@else{{ route('supplies.show', $supply) }}@endif">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $supply->name }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900">{{ $supply->description ?? 'No description' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            @if($supply->supply_type === 'consumable') bg-orange-100 text-orange-800
                                            @elseif($supply->supply_type === 'borrowable') bg-blue-100 text-blue-800
                                            @elseif($supply->supply_type === 'grantable') bg-purple-100 text-purple-800
                                            @else bg-gray-100 text-gray-800
                                            @endif">
                                            {{ $supply->getSupplyTypeLabel() }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $supply->quantity }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $supply->unit }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            @if($supply->status === 'active') bg-green-100 text-green-800
                                            @elseif($supply->status === 'inactive') bg-gray-100 text-gray-800
                                            @elseif($supply->status === 'damaged') bg-red-100 text-red-800
                                            @endif">
                                            {{ ucfirst($supply->status) }}
                                        </span>
                                        
                                        @if(!$supply->isBorrowable())
                                            @if($supply->quantity <= $supply->minimum_stock_level)
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 ml-2">
                                                    Low Stock
                                                </span>
                                            @elseif($supply->quantity <= ($supply->minimum_stock_level * 1.5))
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800 ml-2">
                                                    Warning
                                                </span>
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Client-side no results message -->
                    <div id="client-no-results" class="hidden text-center text-sm text-gray-500 mt-4">
                        No matching supplies on this page.
                    </div>

                    <!-- Pagination -->
                    <div class="mt-6" id="supplies-pagination">
                        {{ $supplies->appends(request()->query())->links() }}
                    </div>
                    @else
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No supplies found</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            @if($search)
                                No supplies match your search criteria.
                            @else
                                Get started by adding your first supply item.
                            @endif
                        </p>
                        @can('create', App\Models\Supply::class)
                        @if(!$search)
                        <div class="mt-6">
                            <a href="{{ route('supplies.create') }}" 
                               class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                Add Supply
                            </a>
                        </div>
                        @endif
                        @endcan
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Live search script for Supplies tab -->
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const input = document.getElementById('live-search-input');
                    const tbody = document.getElementById('supplies-table-body');
                    if (!input || !tbody) return;
                    const rows = Array.from(tbody.querySelectorAll('tr.supply-row'));
                    const noResultsEl = document.getElementById('client-no-results');

                    // Pre-index row text for performance
                    const indexed = rows.map(row => ({
                        row,
                        name: row.getAttribute('data-name') || '',
                        desc: row.getAttribute('data-desc') || '',
                        locs: row.getAttribute('data-locations') || ''
                    }));

                    function filterRows(term) {
                        const q = (term || '').trim().toLowerCase();
                        let visibleCount = 0;
                        indexed.forEach(item => {
                            const match = !q || item.name.includes(q) || item.desc.includes(q) || item.locs.includes(q);
                            item.row.style.display = match ? '' : 'none';
                            if (match) visibleCount++;
                        });
                        if (noResultsEl) {
                            noResultsEl.classList.toggle('hidden', visibleCount !== 0);
                        }
                    }

                    // Debounced input for responsiveness during continuous typing
                    let debounceTimer;
                    input.addEventListener('input', function(e) {
                        const val = e.target.value;
                        clearTimeout(debounceTimer);
                        debounceTimer = setTimeout(() => {
                            requestAnimationFrame(() => filterRows(val));
                        }, 120);
                    });

                    // Initialize filtering based on any pre-filled value
                    filterRows(input.value);

                    // Make each supply row clickable to view details
                    rows.forEach(row => {
                        const url = row.getAttribute('data-url');
                        if (!url) return;
                        // Accessibility: allow keyboard navigation
                        row.setAttribute('tabindex', '0');
                        row.setAttribute('role', 'link');
                        row.addEventListener('click', function() {
                            window.location.href = url;
                        });
                        row.addEventListener('keydown', function(e) {
                            if (e.key === 'Enter' || e.key === ' ') {
                                e.preventDefault();
                                window.location.href = url;
                            }
                        });
                    });
                });
            </script>

            @if(($tab ?? 'supplies') === 'receipts')
            <!-- Manual Receipts Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h4 class="text-lg font-semibold text-gray-900">Manual Receipts</h4>
                        @can('create', App\Models\ManualReceipt::class)
                        <button onclick="showManualReceiptChooser()" 
                                class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Add Manual Receipt
                        </button>
                        @endcan
                    </div>

                    <!-- Search and Filter Form -->
                    <div class="bg-gray-50 rounded-lg p-4 mb-6">
                        <form method="GET" action="{{ route('supplies.index') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                            <input type="hidden" name="tab" value="receipts">
                            
                            <div>
                                <label for="receipt_search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                                <input type="text" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                       id="receipt_search" 
                                       name="receipt_search" 
                                       value="{{ request('receipt_search') }}"
                                       placeholder="Supply, supplier, reference...">
                            </div>
                            
                            <div>
                                <label for="receipt_status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                <select class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" id="receipt_status" name="receipt_status">
                                    <option value="" {{ request('receipt_status') === '' ? 'selected' : '' }}>All Statuses</option>
                                    <option value="pending" {{ request('receipt_status') === 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="verified" {{ request('receipt_status') === 'verified' ? 'selected' : '' }}>Verified</option>
                                    <option value="needs_review" {{ request('receipt_status') === 'needs_review' ? 'selected' : '' }}>Needs Review</option>
                                </select>
                            </div>
                            
                            <div>
                                <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
                                <input type="date" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                       id="date_from" 
                                       name="date_from" 
                                       value="{{ request('date_from') }}">
                            </div>
                            
                            <div>
                                <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
                                <input type="date" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                       id="date_to" 
                                       name="date_to" 
                                       value="{{ request('date_to') }}">
                            </div>
                            
                            <div class="flex items-end space-x-2">
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </button>
                                <a href="{{ route('supplies.index', ['tab' => 'receipts']) }}" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-md transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </a>
                            </div>
                        </form>
                    </div>
                    
                    @if($manualReceipts->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Receipt Date
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Supply
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Quantity
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Supplier
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Reference
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Cost/Unit
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Added By
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($manualReceipts as $receipt)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $receipt->receipt_date->format('M d, Y') }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $receipt->supply->name }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ number_format($receipt->quantity) }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $receipt->supplier ? $receipt->supplier->name : 'Not specified' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $receipt->reference_number ?? 'N/A' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            @if($receipt->cost_per_unit)
                                                ${{ number_format($receipt->cost_per_unit, 2) }}
                                            @else
                                                N/A
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            @if($receipt->status === 'verified') bg-green-100 text-green-800
                                            @elseif($receipt->status === 'pending') bg-yellow-100 text-yellow-800
                                            @elseif($receipt->status === 'needs_review') bg-red-100 text-red-800
                                            @else bg-gray-100 text-gray-800
                                            @endif">
                                            {{ ucfirst(str_replace('_', ' ', $receipt->status)) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $receipt->addedBy->name }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <button onclick="showReceiptDetails({{ $receipt->id }})" 
                                                    class="text-blue-600 hover:text-blue-900 transition-colors" 
                                                    title="View Details">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                            </button>
                                            
                                            @can('update', $receipt)
                                            <button onclick="editReceipt({{ $receipt->id }})" 
                                                    class="text-indigo-600 hover:text-indigo-900 transition-colors" 
                                                    title="Edit Receipt">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                            </button>
                                            @endcan
                                            
                                            @can('delete', $receipt)
                                            <button onclick="deleteReceipt({{ $receipt->id }})" 
                                                    class="text-red-600 hover:text-red-900 transition-colors" 
                                                    title="Delete Receipt">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination for Manual Receipts -->
                    <div class="mt-6">
                        {{ $manualReceipts->appends(request()->query())->links() }}
                    </div>
                    @else
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No manual receipts found</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            @if(request()->hasAny(['receipt_search', 'receipt_status', 'date_from', 'date_to']))
                                No receipts match your current filters. Try adjusting your search criteria.
                            @else
                                Get started by adding your first manual receipt.
                            @endif
                        </p>
                        @can('create', App\Models\ManualReceipt::class)
                        <div class="mt-6">
                            <button onclick="showManualReceiptChooser()" 
                                    class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                Add Manual Receipt
                            </button>
                        </div>
                        @endcan
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Close sidebar layout wrappers before modals -->
            </div> <!-- End of flex-1 (suppliesMain) -->
            </div> <!-- End of flex layout (supplies-layout) -->

            <!-- Manual Receipt Action Chooser Modal -->
            <div id="manualReceiptActionModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
                <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
                    <div class="mt-3">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Choose Action</h3>
                            <button onclick="hideManualReceiptChooser()" class="text-gray-400 hover:text-gray-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <button type="button" onclick="proceedAddManualReceipt()" class="w-full px-4 py-6 border rounded-md hover:bg-green-50">
                                <div class="flex items-center">
                                    <svg class="w-6 h-6 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    <div>
                                        <div class="text-base font-medium text-gray-900">Add Manual Receipt</div>
                                        <div class="text-xs text-gray-600">Record stock received for an existing supply</div>
                                    </div>
                                </div>
                            </button>

                            <button type="button" onclick="proceedAddSupply()" class="w-full px-4 py-6 border rounded-md hover:bg-blue-50">
                                <div class="flex items-center">
                                    <svg class="w-6 h-6 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                    </svg>
                                    <div>
                                        <div class="text-base font-medium text-gray-900">Add Supply</div>
                                        <div class="text-xs text-gray-600">Create a new supply item in inventory</div>
                                    </div>
                                </div>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
         </div>
     </div>
 
     <!-- Manual Receipt Form Modal -->
    <div id="manualReceiptModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Add Manual Receipt</h3>
                    <button onclick="hideManualReceiptForm()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form id="manual_receipt_form" action="{{ route('manual-receipts.store') }}" method="POST" class="space-y-4">
                    @csrf

                    <!-- Select Supply -->
                    <div>
                        <label for="manual_receipt_supply_search" class="block text-sm font-medium text-gray-700">Select Supply <span class="text-red-500">*</span></label>
                        <input type="text" id="manual_receipt_supply_search" placeholder="Search supply by name..." 
                               class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" autocomplete="off">
                        <div id="supply_selection_error" class="hidden mt-1 text-xs text-red-600">Please select a supply from the list.</div>
                        <div id="selected_supply_display" class="hidden mt-2 text-sm text-gray-700">
                            Selected: <span id="selected_supply_name" class="font-medium"></span>
                        </div>
                        <div id="supply_list" class="mt-2 border border-gray-200 rounded-md max-h-48 overflow-y-auto"></div>
                        <input type="hidden" name="supply_id" id="supply_id">
                    </div>

                    <!-- Quantity -->
                    <div>
                        <label for="quantity" class="block text-sm font-medium text-gray-700">Quantity Received <span class="text-red-500">*</span></label>
                        <input type="number" name="quantity" id="quantity" min="1" required 
                               class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <!-- Receipt Date -->
                    <div>
                        <label for="receipt_date" class="block text-sm font-medium text-gray-700">Receipt Date <span class="text-red-500">*</span></label>
                        <input type="date" name="receipt_date" id="receipt_date" value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}" required 
                               class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <!-- Reference Number -->
                    <div>
                        <label for="reference_number" class="block text-sm font-medium text-gray-700">Reference Number</label>
                        <input type="text" name="reference_number" id="reference_number" placeholder="Invoice #, PO #, etc." 
                               class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    

                    <!-- Notes -->
                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                        <textarea name="notes" id="notes" rows="3" placeholder="Additional notes about this receipt..." 
                                  class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>

                    <div class="bg-blue-50 border border-blue-200 rounded-md p-3 text-sm text-blue-700">
                        Adding this receipt will update inventory and may fulfill pending order requests.
                    </div>

                    <div class="flex justify-end gap-2">
                        <button type="button" onclick="hideManualReceiptForm()" class="px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Save Receipt</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Receipt Details Modal -->
    <div id="receiptDetailsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Receipt Details</h3>
                    <button onclick="hideReceiptDetails()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <div id="receiptDetailsContent">
                    <!-- Receipt details will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <!-- Receipt Edit Modal -->
    <div id="receiptEditModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Edit Receipt</h3>
                    <button onclick="hideReceiptEdit()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <div id="receiptEditContent">
                    <!-- Receipt edit form will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <script>
        function showManualReceiptForm() {
            document.getElementById('manualReceiptModal').classList.remove('hidden');
        }

        function hideManualReceiptForm() {
            document.getElementById('manualReceiptModal').classList.add('hidden');
            // Reset form when hiding
            const form = document.querySelector('#manualReceiptModal form');
            if (form) {
                form.reset();
            }
        }

        // Chooser modal controls
        function showManualReceiptChooser() {
            const modal = document.getElementById('manualReceiptActionModal');
            if (modal) modal.classList.remove('hidden');
        }
        function hideManualReceiptChooser() {
            const modal = document.getElementById('manualReceiptActionModal');
            if (modal) modal.classList.add('hidden');
        }
        function proceedAddManualReceipt() {
            hideManualReceiptChooser();
            showManualReceiptForm();
        }
        function proceedAddSupply() {
            window.location.href = "{{ route('supplies.create') }}";
        }

         function showReceiptDetails(receiptId) {
             const modal = document.getElementById('receiptDetailsModal');
             const content = document.getElementById('receiptDetailsContent');
             
             // Show loading state
             content.innerHTML = '<div class="text-center py-4"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div><p class="mt-2 text-gray-500">Loading receipt details...</p></div>';
             modal.classList.remove('hidden');
             
             // Fetch receipt details
             fetch(`/manual-receipts/${receiptId}`)
                 .then(response => response.text())
                 .then(html => {
                     // Extract the content from the response (you might need to parse this differently)
                     const parser = new DOMParser();
                     const doc = parser.parseFromString(html, 'text/html');
                     const cardBody = doc.querySelector('.card-body');
                     if (cardBody) {
                         content.innerHTML = cardBody.innerHTML;
                     } else {
                         content.innerHTML = '<p class="text-red-500">Error loading receipt details.</p>';
                     }
                 })
                 .catch(error => {
                     console.error('Error:', error);
                     content.innerHTML = '<p class="text-red-500">Error loading receipt details.</p>';
                 });
         }

         function hideReceiptDetails() {
             document.getElementById('receiptDetailsModal').classList.add('hidden');
         }

         function editReceipt(receiptId) {
             const modal = document.getElementById('receiptEditModal');
             const content = document.getElementById('receiptEditContent');
             
             // Show loading state
             content.innerHTML = '<div class="text-center py-4"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div><p class="mt-2 text-gray-500">Loading edit form...</p></div>';
             modal.classList.remove('hidden');
             
             // Fetch edit form
             fetch(`/manual-receipts/${receiptId}/edit`)
                 .then(response => response.text())
                 .then(html => {
                     // Extract the form content from the response
                     const parser = new DOMParser();
                     const doc = parser.parseFromString(html, 'text/html');
                     const cardBody = doc.querySelector('.card-body');
                     if (cardBody) {
                         content.innerHTML = cardBody.innerHTML;
                         // Update form action to handle modal submission
                         const form = content.querySelector('form');
                         if (form) {
                             form.addEventListener('submit', function(e) {
                                 e.preventDefault();
                                 submitReceiptEdit(form, receiptId);
                             });
                         }
                     } else {
                         content.innerHTML = '<p class="text-red-500">Error loading edit form.</p>';
                     }
                 })
                 .catch(error => {
                     console.error('Error:', error);
                     content.innerHTML = '<p class="text-red-500">Error loading edit form.</p>';
                 });
         }

         function hideReceiptEdit() {
             document.getElementById('receiptEditModal').classList.add('hidden');
         }

         function submitReceiptEdit(form, receiptId) {
             const formData = new FormData(form);
             
             fetch(`/manual-receipts/${receiptId}`, {
                 method: 'POST',
                 body: formData,
                 headers: {
                     'X-Requested-With': 'XMLHttpRequest'
                 }
             })
             .then(response => {
                 if (response.ok) {
                     hideReceiptEdit();
                     // Redirect to supplies page with receipts tab
                     window.location.href = '/supplies?tab=receipts';
                 } else {
                     throw new Error('Update failed');
                 }
             })
             .catch(error => {
                 console.error('Error:', error);
                 alert('Error updating receipt. Please try again.');
             });
         }

        function deleteReceipt(receiptId) {
            if (confirm('Are you sure you want to delete this manual receipt? This action cannot be undone.')) {
                // Create and submit a delete form
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '/manual-receipts/' + receiptId;
                
                const csrfToken = document.querySelector('meta[name="csrf-token"]');
                if (csrfToken) {
                    const csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden';
                    csrfInput.name = '_token';
                    csrfInput.value = csrfToken.getAttribute('content');
                    form.appendChild(csrfInput);
                }
                
                const methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'DELETE';
                form.appendChild(methodInput);
                
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Enhanced autocomplete functionality for supply name
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('manualReceiptModal');
            const supplyNameInput = document.getElementById('supply_name');
            const autocompleteDropdown = document.getElementById('supply_autocomplete');
            const feedbackElement = document.getElementById('supply_feedback');
            
            // Close modal when clicking outside
            if (modal) {
                modal.addEventListener('click', function(e) {
                    if (e.target === this) {
                        hideManualReceiptForm();
                    }
                });
            }

            // Enhanced autocomplete for supply name input
            if (supplyNameInput && autocompleteDropdown && feedbackElement) {
                let existingSupplies = [];
                let selectedIndex = -1;
                
                // Populate existing supplies array from datalist
                const datalist = document.getElementById('existing_supplies');
                if (datalist) {
                    const options = datalist.querySelectorAll('option');
                    existingSupplies = Array.from(options).map(option => option.value);
                }

                // Function to show autocomplete dropdown
                function showAutocomplete(matches) {
                    if (matches.length === 0) {
                        autocompleteDropdown.classList.add('hidden');
                        return;
                    }

                    autocompleteDropdown.innerHTML = '';
                    matches.forEach((match, index) => {
                        const option = document.createElement('div');
                        option.className = 'px-3 py-2 cursor-pointer hover:bg-blue-50 border-b border-gray-100 last:border-b-0';
                        option.textContent = match;
                        option.addEventListener('click', function() {
                            supplyNameInput.value = match;
                            autocompleteDropdown.classList.add('hidden');
                            updateFeedback(match);
                            selectedIndex = -1;
                        });
                        autocompleteDropdown.appendChild(option);
                    });
                    
                    autocompleteDropdown.classList.remove('hidden');
                    selectedIndex = -1;
                }

                // Function to hide autocomplete dropdown
                function hideAutocomplete() {
                    autocompleteDropdown.classList.add('hidden');
                    selectedIndex = -1;
                }

                // Function to update feedback message
                function updateFeedback(inputValue) {
                    if (inputValue === '') {
                        feedbackElement.textContent = 'Type to search existing supplies or enter a new name to create one';
                        feedbackElement.className = 'mt-1 text-xs text-gray-500';
                        return;
                    }

                    // Check if the input matches an existing supply
                    const exactMatch = existingSupplies.find(supply => 
                        supply.toLowerCase() === inputValue.toLowerCase()
                    );

                    if (exactMatch) {
                        feedbackElement.textContent = `✓ Using existing supply: "${exactMatch}"`;
                        feedbackElement.className = 'mt-1 text-xs text-green-600';
                    } else {
                        feedbackElement.textContent = `⚠ New supply "${inputValue}" will be created`;
                        feedbackElement.className = 'mt-1 text-xs text-orange-600';
                    }
                }

                // Handle keyboard navigation
                function handleKeyNavigation(e) {
                    const options = autocompleteDropdown.querySelectorAll('div');
                    
                    if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        selectedIndex = Math.min(selectedIndex + 1, options.length - 1);
                        updateSelection(options);
                    } else if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        selectedIndex = Math.max(selectedIndex - 1, -1);
                        updateSelection(options);
                    } else if (e.key === 'Enter' && selectedIndex >= 0) {
                        e.preventDefault();
                        options[selectedIndex].click();
                    } else if (e.key === 'Escape') {
                        hideAutocomplete();
                    }
                }

                // Update visual selection
                function updateSelection(options) {
                    options.forEach((option, index) => {
                        if (index === selectedIndex) {
                            option.classList.add('bg-blue-100');
                        } else {
                            option.classList.remove('bg-blue-100');
                        }
                    });
                }

                // Input event listener for real-time autocomplete
                supplyNameInput.addEventListener('input', function() {
                    const inputValue = this.value.trim();
                    
                    if (inputValue === '') {
                        hideAutocomplete();
                        updateFeedback('');
                        return;
                    }

                    // Find matching supplies
                    const matches = existingSupplies.filter(supply =>
                        supply.toLowerCase().includes(inputValue.toLowerCase())
                    ).slice(0, 10); // Limit to 10 results

                    showAutocomplete(matches);
                    updateFeedback(inputValue);
                });

                // Handle keyboard navigation
                supplyNameInput.addEventListener('keydown', handleKeyNavigation);

                // Hide dropdown when clicking outside
                document.addEventListener('click', function(e) {
                    if (!supplyNameInput.contains(e.target) && !autocompleteDropdown.contains(e.target)) {
                        hideAutocomplete();
                    }
                });

                // Focus event to show relevant matches
                supplyNameInput.addEventListener('focus', function() {
                    const inputValue = this.value.trim();
                    if (inputValue !== '') {
                        const matches = existingSupplies.filter(supply =>
                            supply.toLowerCase().includes(inputValue.toLowerCase())
                        ).slice(0, 10);
                        showAutocomplete(matches);
                    }
                });

                // Handle form submission to show confirmation for new supplies
                const form = document.querySelector('#manualReceiptModal form');
                if (form) {
                    form.addEventListener('submit', function(e) {
                        const inputValue = supplyNameInput.value.trim();
                        const exactMatch = existingSupplies.find(supply => 
                            supply.toLowerCase() === inputValue.toLowerCase()
                        );

                        if (!exactMatch && inputValue !== '') {
                            const confirmed = confirm(`Are you sure you want to create a new supply named "${inputValue}"?`);
                            if (!confirmed) {
                                e.preventDefault();
                                return false;
                            }
                        }
                    });
                }
            }


        });
        window.availableSuppliesAll = @json($availableSuppliesAllJson);
        (function() {
            const searchInput = document.getElementById('manual_receipt_supply_search');
            const listEl = document.getElementById('supply_list');
            const hiddenInput = document.getElementById('supply_id');
            const selectedDisplay = document.getElementById('selected_supply_display');
            const selectedNameEl = document.getElementById('selected_supply_name');
            const selectionErrorEl = document.getElementById('supply_selection_error');
            const formEl = document.getElementById('manual_receipt_form');

            if (!searchInput || !listEl || !hiddenInput) return;

            function renderList(items) {
                listEl.innerHTML = '';
                if (!items || items.length === 0) {
                    listEl.innerHTML = '<div class="px-3 py-2 text-sm text-gray-500">No matching supplies</div>';
                    return;
                }
                items.slice(0, 5).forEach(item => {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'w-full text-left px-3 py-2 hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-blue-500 supply-list-item';
                    btn.dataset.id = item.id;
                    btn.dataset.name = item.name;
                    btn.dataset.available = item.available;
                    if (item.unit) btn.dataset.unit = item.unit;
                    btn.innerHTML = `
                        <div class="flex justify-between">
                            <span class="font-medium text-gray-900">${item.name}</span>
                            <span class="text-xs text-gray-600">Available: ${item.available}</span>
                        </div>
                        ${item.unit ? `<div class=\"text-xs text-gray-500\">${item.unit}</div>` : ''}
                    `;
                    btn.addEventListener('click', () => setSelected(btn));
                    listEl.appendChild(btn);
                });
            }

            function setSelected(btn) {
                hiddenInput.value = btn.dataset.id || '';
                if (selectionErrorEl) selectionErrorEl.classList.add('hidden');
                if (selectedDisplay && selectedNameEl) {
                    selectedDisplay.classList.remove('hidden');
                    selectedNameEl.textContent = btn.dataset.name || '';
                }
                Array.from(listEl.querySelectorAll('.supply-list-item')).forEach(el => el.classList.remove('bg-blue-100'));
                btn.classList.add('bg-blue-100');
            }

            function filterAndRender(query) {
                const q = (query || '').trim().toLowerCase();
                let items = window.availableSuppliesAll || [];
                if (q.length > 0) {
                    items = items.filter(s => (s.name || '').toLowerCase().includes(q));
                }
                items.sort((a, b) => {
                    const diff = (b.available || 0) - (a.available || 0);
                    if (diff !== 0) return diff;
                    return (a.name || '').localeCompare(b.name || '');
                });
                renderList(items);
            }

            Array.from(listEl.querySelectorAll('.supply-list-item')).forEach(btn => btn.addEventListener('click', () => setSelected(btn)));

            searchInput.addEventListener('input', (e) => filterAndRender(e.target.value));

            if (formEl) {
                formEl.addEventListener('submit', (e) => {
                    if (!hiddenInput.value) {
                        e.preventDefault();
                        if (selectionErrorEl) selectionErrorEl.classList.remove('hidden');
                    }
                });
            }
        })();

        // Server-side live search across pagination pages
        (function() {
            document.addEventListener('DOMContentLoaded', function() {
                const input = document.getElementById('live-search-input');
                const tbody = document.getElementById('supplies-table-body');
                const paginationEl = document.getElementById('supplies-pagination');
                const noResultsEl = document.getElementById('client-no-results');
                if (!input || !tbody) return;
                const originalTbodyHTML = tbody.innerHTML;
                let usingServer = false;

                function debounce(fn, delay) {
                    let t; return function(...args){ clearTimeout(t); t = setTimeout(()=>fn.apply(this,args), delay); };
                }
                function statusClass(status) {
                    if (status === 'active') return 'bg-green-100 text-green-800';
                    if (status === 'inactive') return 'bg-gray-100 text-gray-800';
                    if (status === 'damaged') return 'bg-red-100 text-red-800';
                    return 'bg-gray-100 text-gray-800';
                }
                function renderResults(items) {
                    tbody.innerHTML = '';
                    if (!items || items.length === 0) {
                        if (noResultsEl) {
                            noResultsEl.textContent = 'No matching supplies across all pages.';
                            noResultsEl.classList.remove('hidden');
                        }
                        return;
                    }
                    if (noResultsEl) noResultsEl.classList.add('hidden');
                    items.forEach(item => {
                        const tr = document.createElement('tr');
                        tr.className = 'hover:bg-gray-50 cursor-pointer supply-row';
                        tr.innerHTML = `
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">${item.name}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">${item.description || ''}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">${item.supply_type_label}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">${item.quantity}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">${item.unit}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${statusClass(item.status)}">${(item.status || '').charAt(0).toUpperCase() + (item.status || '').slice(1)}</span>
                            </td>
                        `;
                        tr.addEventListener('click', () => { window.location.href = item.url; });
                        tbody.appendChild(tr);
                    });
                }
                const runSearch = debounce(function() {
                    const q = (input.value || '').trim();
                    if (q.length >= 1) {
                        usingServer = true;
                        if (paginationEl) paginationEl.classList.add('hidden');
                        fetch('/supplies/search?q=' + encodeURIComponent(q))
                            .then(r => r.json())
                            .then(json => renderResults(json.results || []))
                            .catch(() => {
                                if (noResultsEl) {
                                    noResultsEl.textContent = 'Error searching supplies.';
                                    noResultsEl.classList.remove('hidden');
                                }
                            });
                    } else {
                        if (usingServer) {
                            usingServer = false;
                            tbody.innerHTML = originalTbodyHTML;
                            if (paginationEl) paginationEl.classList.remove('hidden');
                            if (noResultsEl) noResultsEl.classList.add('hidden');
                        }
                    }
                }, 200);
                input.addEventListener('input', runSearch);
            });
        })();


    </script>@endsection
