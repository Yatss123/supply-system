@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-4xl mx-auto">
        <?php $itemsData = $restockRequest->items_json ? json_decode($restockRequest->items_json, true) : null; $items = $itemsData['items'] ?? []; $isComposite = $itemsData && isset($itemsData['items']) && count($itemsData['items']) > 0; ?>
        <!-- Header -->
        <div class="mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Order Request Details</h1>
                    <p class="text-gray-600 mt-2">Request #{{ $restockRequest->id }}</p>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('restock-requests.index') }}" 
                       class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 active:bg-gray-500 focus:outline-none focus:border-gray-500 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Back to List
                    </a>
                    @can('update', $restockRequest)
                    <a href="{{ route('restock-requests.edit', $restockRequest) }}" 
                       class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:border-blue-800 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                        <i class="fas fa-edit mr-2"></i>
                        Edit Request
                    </a>
                    @endcan
                </div>
            </div>
        </div>

        <!-- Request Information -->
        <div class="bg-white shadow-md rounded-lg p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Request Information</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Request ID</label>
                    <p class="text-lg font-semibold text-gray-900">#{{ $restockRequest->id }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full
                        @if($restockRequest->status === 'pending') bg-yellow-100 text-yellow-800
                        @elseif($restockRequest->status === 'ordered') bg-indigo-100 text-indigo-800
                        @elseif($restockRequest->status === 'delivered') bg-green-100 text-green-800
                        @else bg-gray-100 text-gray-800
                        @endif">
                        {{ ucfirst($restockRequest->status) }}
                    </span>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ordered By</label>
                    <p class="text-gray-900">{{ $restockRequest->requestedDepartment?->department_name ?? '—' }}</p>
                </div>
            </div>

            @if(!$isComposite)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Supply Item</label>
                    <p class="text-gray-900 font-medium">{{ $restockRequest->supply->name }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Unit</label>
                    <p class="text-gray-900">{{ $restockRequest->supply->unit }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Quantity Requested</label>
                    <p class="text-lg font-semibold text-gray-900">{{ $restockRequest->quantity }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Current Stock</label>
                    <p class="text-gray-900">{{ $restockRequest->supply->quantity }} {{ $restockRequest->supply->unit }}</p>
                </div>
            </div>
            @else
            <div class="mt-6">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-lg font-semibold text-gray-900">Selected Items</h3>
                    <span class="text-sm text-gray-600">Total quantity: {{ $itemsData['total_quantity'] ?? 0 }}</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach(($itemsData['items'] ?? []) as $it)
                            <tr>
                                <td class="px-4 py-2 text-gray-900">{{ $it['supply_name'] }}</td>
                                <td class="px-4 py-2">{{ $it['quantity'] }}</td>
                                <td class="px-4 py-2">{{ $it['unit'] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>

        <!-- Supplier Information -->
        @if($restockRequest->supplier)
        <div class="bg-white shadow-md rounded-lg p-6 mt-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Supplier Information</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Supplier</label>
                    <p class="text-gray-900 font-medium">{{ $restockRequest->supplier->name }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Preferred Contact</label>
                    <p class="text-gray-900">{{ ucfirst($restockRequest->supplier->preferred_contact_method ?? 'email') }}</p>
                </div>
                @if(!empty($restockRequest->supplier->email))
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <p class="text-gray-900">{{ $restockRequest->supplier->email }}</p>
                </div>
                @endif
                @if(!empty($restockRequest->supplier->phone1))
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                    <p class="text-gray-900">{{ $restockRequest->supplier->phone1 }}</p>
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>
@endsection