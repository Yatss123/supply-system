@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-2xl mx-auto">
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Order Request</h1>
            <p class="text-gray-600 mt-2">Place an order for this request</p>
        </div>

        <!-- Restock Request Details Card -->
        <div class="bg-white shadow-md rounded-lg p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Request Details</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Request ID</label>
                    <p class="mt-1 text-sm text-gray-900">#{{ $restockRequest->id }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Supply Item</label>
                    <p class="mt-1 text-sm text-gray-900">{{ $restockRequest->supply->name }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Unit</label>
                    <p class="mt-1 text-sm text-gray-900">{{ $restockRequest->supply->unit }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Current Status</label>
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                        {{ ucfirst($restockRequest->status) }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Order Form -->
        <div class="bg-white shadow-md rounded-lg p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Order Information</h2>
            
            <form action="{{ route('restock-requests.order.submit', $restockRequest) }}" method="POST">
                @csrf
                @method('PATCH')

                <!-- Quantity Field -->
                <div class="mb-6">
                    <label for="quantity" class="block text-sm font-medium text-gray-700 mb-2">
                        Quantity to Order <span class="text-red-500">*</span>
                    </label>
                    <input type="number" 
                           id="quantity" 
                           name="quantity" 
                           value="{{ old('quantity', $restockRequest->quantity) }}" 
                           min="1"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 @error('quantity') border-red-500 @enderror"
                           required>
                    @error('quantity')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Supplier Selection -->
                <div class="mb-6">
                    <label for="supplier_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Select Supplier <span class="text-red-500">*</span>
                    </label>
                    <select id="supplier_id" 
                            name="supplier_id" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 @error('supplier_id') border-red-500 @enderror"
                            required>
                        <option value="">Choose a supplier...</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" 
                                    {{ old('supplier_id', $restockRequest->supplier_id) == $supplier->id ? 'selected' : '' }}>
                                {{ $supplier->name }} - {{ $supplier->email }}
                            </option>
                        @endforeach
                    </select>
                    @error('supplier_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-between items-center">
                    <a href="{{ route('restock-requests.index') }}" 
                       class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 active:bg-gray-500 focus:outline-none focus:border-gray-500 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Cancel
                    </a>
                    
                    <button type="submit" 
                            class="inline-flex items-center px-6 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:border-green-900 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                        Place Order
                    </button>
                </div>
            </form>
        </div>

        <!-- Information Notice -->
        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-md p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800">Order Information</h3>
                    <div class="mt-2 text-sm text-blue-700">
                        <p>Once you place this order:</p>
                        <ul class="list-disc list-inside mt-1">
                            <li>The order request status will be updated to "Ordered"</li>
                            <li>Both you and the selected supplier will receive email notifications</li>
                            <li>You can mark the order as delivered once it arrives</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection