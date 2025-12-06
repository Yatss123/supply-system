@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header Section -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Supplier Details</h1>
        <div class="flex space-x-2">
            @if(auth()->user()->hasAdminPrivileges())
                @if($supplier->status === 'active')
                    <form action="{{ route('suppliers.deactivate', $supplier) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to deactivate this supplier?')">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded">Deactivate</button>
                    </form>
                @else
                    <form action="{{ route('suppliers.activate', $supplier) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to activate this supplier?')">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">Activate</button>
                    </form>
                @endif
                <a href="{{ route('suppliers.edit', $supplier) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Edit Supplier</a>
            @endif
            <a href="{{ route('suppliers.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">Back to Suppliers</a>
        </div>
    </div>

    <!-- Supplier Information Card -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden mb-6">
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Supplier Information</h3>
        </div>
        <div class="px-6 py-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Basic Information -->
                <div>
                    <h4 class="text-md font-semibold text-gray-800 mb-3">Basic Information</h4>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Supplier Name</label>
                            <p class="text-sm text-gray-900">{{ $supplier->name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Contact Person</label>
                            <p class="text-sm text-gray-900">{{ $supplier->contact_person ?? 'Not specified' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Status</label>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                {{ $supplier->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ ucfirst($supplier->status) }}
                            </span>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Email</label>
                            <p class="text-sm text-gray-900">
                                @if($supplier->email)
                                    <a href="mailto:{{ $supplier->email }}" class="text-blue-600 hover:text-blue-800">{{ $supplier->email }}</a>
                                @else
                                    Not specified
                                @endif
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Preferred Contact Method</label>
                            <p class="text-sm text-gray-900">
                                @php
                                    $pcm = $supplier->preferred_contact_method;
                                    $pcmLabel = match($pcm) {
                                        'email' => 'Email',
                                        'phone' => 'Phone',
                                        'facebook_messenger' => 'Facebook Messenger',
                                        default => 'Not specified',
                                    };
                                @endphp
                                {{ $pcmLabel }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Contact Information -->
                <div>
                    <h4 class="text-md font-semibold text-gray-800 mb-3">Contact Information</h4>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Primary Phone</label>
                            <p class="text-sm text-gray-900">
                                @if($supplier->phone1)
                                    <a href="tel:{{ $supplier->phone1 }}" class="text-blue-600 hover:text-blue-800">{{ $supplier->phone1 }}</a>
                                @else
                                    Not specified
                                @endif
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Secondary Phone</label>
                            <p class="text-sm text-gray-900">
                                @if($supplier->phone2)
                                    <a href="tel:{{ $supplier->phone2 }}" class="text-blue-600 hover:text-blue-800">{{ $supplier->phone2 }}</a>
                                @else
                                    Not specified
                                @endif
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Facebook Messenger</label>
                            <p class="text-sm text-gray-900">
                                @if($supplier->facebook_messenger)
                                    @php
                                        $fm = $supplier->facebook_messenger;
                                        $isUrl = preg_match('/^(https?:\\/\\/|m\\.me\\/|(www\\.)?facebook\\.com\\/|fb\\.me\\/)/i', $fm);
                                    @endphp
                                    @if($isUrl)
                                        <a href="{{ $fm }}" target="_blank" rel="noopener" class="text-blue-600 hover:text-blue-800">{{ $fm }}</a>
                                    @else
                                        {{ $fm }}
                                    @endif
                                @else
                                    Not specified
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Address Information -->
            <div class="mt-6">
                <h4 class="text-md font-semibold text-gray-800 mb-3">Address Information</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Address</label>
                        <p class="text-sm text-gray-900">{{ $supplier->address ?? 'Not specified' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">City</label>
                        <p class="text-sm text-gray-900">{{ $supplier->city ?? 'Not specified' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">State/Province</label>
                        <p class="text-sm text-gray-900">{{ $supplier->state ?? 'Not specified' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Postal Code</label>
                        <p class="text-sm text-gray-900">{{ $supplier->postal_code ?? 'Not specified' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Country</label>
                        <p class="text-sm text-gray-900">{{ $supplier->country ?? 'Not specified' }}</p>
                    </div>
                </div>
            </div>

            <!-- Timestamps -->
            <div class="mt-6 pt-6 border-t border-gray-200">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Created At</label>
                        <p class="text-sm text-gray-900">{{ $supplier->created_at ? $supplier->created_at->format('M d, Y \a\t g:i A') : 'Not available' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Last Updated</label>
                        <p class="text-sm text-gray-900">{{ $supplier->updated_at ? $supplier->updated_at->format('M d, Y \a\t g:i A') : 'Not available' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Related Supplies -->
    @if(isset($supplies) && $supplies->count() > 0)
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-medium text-gray-900">Supplies from this Supplier ({{ $supplies->total() }})</h3>
                    <div class="flex items-center space-x-2">
                        <input type="text" 
                               id="suppliesSearch" 
                               placeholder="Search supplies..." 
                               class="px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <button onclick="clearSuppliesSearch()" 
                                class="px-3 py-2 text-sm text-gray-500 hover:text-gray-700">
                            Clear
                        </button>
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="suppliesTableBody" class="bg-white divide-y divide-gray-200">
                        @foreach($supplies as $supply)
                            <tr class="hover:bg-gray-50 supply-row" data-search-text="{{ strtolower($supply->name . ' ' . ($supply->categories->pluck('name')->implode(' ') ?? '') . ' ' . $supply->unit) }}">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $supply->name }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $supply->categories->pluck('name')->implode(', ') ?: 'N/A' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $supply->quantity }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $supply->unit }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    @if(auth()->user()->hasRole('student'))
                                        <a href="{{ route('student.supplies.show', $supply) }}" class="text-blue-600 hover:text-blue-900">View</a>
                                    @elseif(auth()->user()->hasRole('adviser'))
                                        <a href="{{ route('adviser.supplies.show', $supply) }}" class="text-blue-600 hover:text-blue-900">View</a>
                                    @elseif(auth()->user()->hasRole('dean'))
                                        <a href="{{ route('dean.supplies.show', $supply) }}" class="text-blue-600 hover:text-blue-900">View</a>
                                    @else
                                        <a href="{{ route('supplies.show', $supply) }}" class="text-blue-600 hover:text-blue-900">View</a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div id="noSuppliesFound" class="px-6 py-4 text-center text-gray-500 hidden">
                    <p>No supplies found matching your search.</p>
                </div>
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                    {{ $supplies->links() }}
                </div>
            </div>
        </div>
    @else
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Supplies from this Supplier</h3>
            </div>
            <div class="px-6 py-4 text-center text-gray-500">
                <p>No supplies found for this supplier.</p>
            </div>
        </div>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('suppliesSearch');
    const suppliesTableBody = document.getElementById('suppliesTableBody');
    const noSuppliesFound = document.getElementById('noSuppliesFound');
    
    if (searchInput && suppliesTableBody) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            const rows = suppliesTableBody.querySelectorAll('.supply-row');
            let visibleRows = 0;
            
            rows.forEach(function(row) {
                const searchText = row.getAttribute('data-search-text');
                if (searchText && searchText.includes(searchTerm)) {
                    row.style.display = '';
                    visibleRows++;
                } else {
                    row.style.display = 'none';
                }
            });
            
            // Show/hide no results message
            if (visibleRows === 0 && searchTerm !== '') {
                noSuppliesFound.classList.remove('hidden');
            } else {
                noSuppliesFound.classList.add('hidden');
            }
        });
    }
});

function clearSuppliesSearch() {
    const searchInput = document.getElementById('suppliesSearch');
    const suppliesTableBody = document.getElementById('suppliesTableBody');
    const noSuppliesFound = document.getElementById('noSuppliesFound');
    
    if (searchInput) {
        searchInput.value = '';
        
        // Show all rows
        if (suppliesTableBody) {
            const rows = suppliesTableBody.querySelectorAll('.supply-row');
            rows.forEach(function(row) {
                row.style.display = '';
            });
        }
        
        // Hide no results message
        if (noSuppliesFound) {
            noSuppliesFound.classList.add('hidden');
        }
        
        // Focus back on search input
        searchInput.focus();
    }
}
</script>
@endsection