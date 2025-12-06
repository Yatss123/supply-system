@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header Section -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Category Details</h1>
        <div class="flex space-x-2">
            @if(auth()->user()->hasAdminPrivileges())
                <a href="{{ route('categories.edit', $category) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Edit Category
                </a>
            @endif
            <a href="{{ route('categories.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Back to Categories
            </a>
        </div>
    </div>

    <!-- Category Information Card -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden mb-6">
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Category Information</h3>
        </div>
        <div class="px-6 py-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Basic Information -->
                <div>
                    <h4 class="text-md font-semibold text-gray-800 mb-3">Basic Information</h4>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Category Name</label>
                            <p class="text-sm text-gray-900 font-medium">{{ $category->name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Description</label>
                            <p class="text-sm text-gray-900">{{ $category->description ?? 'No description provided' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Statistics -->
                <div>
                    <h4 class="text-md font-semibold text-gray-800 mb-3">Statistics</h4>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Total Supplies</label>
                            <p class="text-sm text-gray-900">{{ $category->supplies_count ?? 0 }} items</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Category Status</label>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Active
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Timestamps -->
            <div class="mt-6 pt-6 border-t border-gray-200">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Created At</label>
                        <p class="text-sm text-gray-900">{{ $category->created_at ? $category->created_at->format('M d, Y \a\t g:i A') : 'Not available' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Last Updated</label>
                        <p class="text-sm text-gray-900">{{ $category->updated_at ? $category->updated_at->format('M d, Y \a\t g:i A') : 'Not available' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Related Supplies -->
    @if($category->supplies && $category->supplies->count() > 0)
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Supplies in this Category ({{ $category->supplies->count() }})</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplier</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($category->supplies->take(10) as $supply)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $supply->item_name }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $supply->supplier->name ?? 'N/A' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $supply->quantity }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $supply->unit }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $isLowStock = $supply->minimum_stock_level 
                                            ? $supply->quantity <= $supply->minimum_stock_level 
                                            : $supply->quantity <= 10;
                                    @endphp
                                    @if(!$supply->isBorrowable())
                                        @if($isLowStock)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                Low Stock
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                In Stock
                                            </span>
                                        @endif
                                    @endif
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
            </div>
            @if($category->supplies->count() > 10)
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 text-center">
                    <a href="{{ route('supplies.index', ['category' => $category->id]) }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                        View all {{ $category->supplies->count() }} supplies in this category
                    </a>
                </div>
            @endif
        </div>
    @else
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Supplies in this Category</h3>
            </div>
            <div class="px-6 py-4 text-center text-gray-500">
                <div class="flex flex-col items-center">
                    <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                    </svg>
                    <p class="text-lg font-medium">No supplies found in this category</p>
                    <p class="text-sm mt-1">Supplies added to this category will appear here</p>
                    @if(auth()->user()->hasAdminPrivileges())
                        <div class="mt-4">
                            <a href="{{ route('supplies.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Add Supply to Category
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <!-- Action Buttons -->
    @if(auth()->user()->hasAdminPrivileges())
        <div class="mt-6 flex justify-end space-x-2">
            <form action="{{ route('categories.destroy', $category) }}" method="POST" class="inline" onsubmit="return confirm('Deactivate this category? Deactivated categories are hidden from normal listings.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded">
                    Deactivate Category
                </button>
            </form>
        </div>
    @endif
</div>
@endsection
