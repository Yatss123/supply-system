@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header Section -->
    <div class="flex justify-between items-center mb-6">
        <div class="flex items-center space-x-3">
            <i class="fas fa-ruler text-indigo-600"></i>
            <h1 class="text-2xl font-semibold text-gray-800">Units</h1>
        </div>
        <div class="flex items-center space-x-2">
            <a href="{{ route('supplies.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                <i class="fas fa-boxes mr-2"></i>
                Supplies
            </a>
            <a href="{{ route('supplies.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-md">
                <i class="fas fa-plus mr-2"></i>
                Add Unit
            </a>
        </div>
    </div>

    <!-- Units Table and Search -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
            <div>
                <h3 class="text-lg font-medium text-gray-900">Units in Use</h3>
                <p class="text-sm text-gray-500">Distinct units currently used across supplies</p>
            </div>
            <div class="w-full max-w-md">
                <label for="unitsSearch" class="sr-only">Search units</label>
                <div class="relative">
                    <input id="unitsSearch" type="text" placeholder="Search units..."
                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" />
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                        <i class="fas fa-search"></i>
                    </span>
                </div>
            </div>
        </div>

        @if($units->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items Using Unit</th>
                    </tr>
                </thead>
                <tbody id="unitsTableBody" class="bg-white divide-y divide-gray-200">
                    @foreach($units as $unit)
                    <tr class="hover:bg-gray-50 cursor-pointer unit-row"
                        data-search-text="{{ strtolower(trim($unit->unit)) }}"
                        onclick="window.location='{{ route('units.show', ['unit' => $unit->unit]) }}'">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $unit->unit }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $unit->item_count }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div id="noUnitsFound" class="px-6 py-8 text-center text-gray-500">
            <p>No units found.</p>
            <p class="text-sm mt-1">Units are derived from supply entries.</p>
        </div>
        @endif
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('unitsSearch');
    const unitsTableBody = document.getElementById('unitsTableBody');
    const noUnitsFound = document.getElementById('noUnitsFound');

    if (searchInput && unitsTableBody) {
        searchInput.addEventListener('input', function() {
            const term = this.value.toLowerCase().trim();
            const rows = unitsTableBody.querySelectorAll('.unit-row');
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

            if (noUnitsFound) {
                noUnitsFound.style.display = visible === 0 ? '' : 'none';
            }
        });
    }
});
</script>
@endsection