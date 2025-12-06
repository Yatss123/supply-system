@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header Section -->
    <div class="flex justify-between items-center mb-6">
        <div class="flex items-center space-x-3">
            <i class="fas fa-ruler text-indigo-600"></i>
            <h1 class="text-2xl font-semibold text-gray-800">Unit: {{ $unit }}</h1>
        </div>
        <div class="flex items-center space-x-2">
            <a href="{{ route('units.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                <i class="fas fa-list mr-2"></i>
                Units
            </a>
            <a href="{{ route('supplies.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                <i class="fas fa-boxes mr-2"></i>
                Supplies
            </a>
        </div>
    </div>

    <!-- Search Bar -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden mb-6">
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
            <div>
                <h3 class="text-lg font-medium text-gray-900">Items Using This Unit</h3>
                <p class="text-sm text-gray-500">Click any item to view its details</p>
            </div>
            <div class="w-full max-w-md">
                <label for="itemsSearch" class="sr-only">Search items</label>
                <div class="relative">
                    <input id="itemsSearch" type="text" placeholder="Search items by name, category, supplier..." 
                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" />
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                        <i class="fas fa-search"></i>
                    </span>
                </div>
            </div>
        </div>

        @if($supplies->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Categories</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Suppliers</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Available</th>
                    </tr>
                </thead>
                <tbody id="itemsTableBody" class="bg-white divide-y divide-gray-200">
                    @foreach($supplies as $supply)
                        @php
                            $categoryNames = $supply->categories->pluck('name')->implode(' ');
                            $supplierNames = $supply->suppliers->pluck('name')->implode(' ');
                            $searchText = strtolower(trim($supply->name.' '.$categoryNames.' '.$supplierNames.' '.$supply->unit));
                        @endphp
                        <tr class="hover:bg-gray-50 cursor-pointer item-row" 
                            data-search-text="{{ $searchText }}"
                            onclick="window.location='{{ route('supplies.show', $supply) }}'">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-box text-gray-400"></i>
                                    <span class="font-medium">{{ $supply->name }}</span>
                                </div>
                                <div class="text-xs text-gray-500">Unit: {{ $supply->unit ?? '—' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                @if($supply->categories->count() > 0)
                                    {{ $supply->categories->pluck('name')->implode(', ') }}
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                @if($supply->suppliers->count() > 0)
                                    {{ $supply->suppliers->pluck('name')->implode(', ') }}
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                {{ method_exists($supply, 'availableQuantity') ? $supply->availableQuantity() : ($supply->quantity ?? 0) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div id="noItemsFound" class="px-6 py-8 text-center text-gray-500">
            <p>No items found for this unit.</p>
        </div>
        @endif
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('itemsSearch');
    const itemsTableBody = document.getElementById('itemsTableBody');
    const noItemsFound = document.getElementById('noItemsFound');

    if (searchInput && itemsTableBody) {
        searchInput.addEventListener('input', function() {
            const term = this.value.toLowerCase().trim();
            const rows = itemsTableBody.querySelectorAll('.item-row');
            let visible = 0;

            rows.forEach(row => {
                const text = row.getAttribute('data-search-text') || '';
                if (!term || text.includes(term)) {
                    row.style.display = '';
                    visible++;
                } else {
                    row.style.display = 'none';
                }
            });

            if (noItemsFound) {
                noItemsFound.style.display = visible === 0 ? '' : 'none';
            }
        });
    }
});
</script>
@endsection