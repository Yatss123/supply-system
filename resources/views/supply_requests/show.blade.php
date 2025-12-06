@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Supply Request Details</h1>
                    <p class="text-gray-600 mt-2">Request #{{ $supplyRequest->id }}</p>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('supply-requests.index') }}" 
                       class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Back to Requests
                    </a>
                    @if($supplyRequest->department_id)
                    <a href="{{ route('department-carts.show', $supplyRequest->department_id) }}"
                       class="px-4 py-2 text-sm font-medium text-blue-700 bg-blue-50 border border-blue-200 rounded-md shadow-sm hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-shopping-cart mr-2"></i> View Department Cart
                    </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- Status Badge -->
        <div class="mb-6">
            @php
                $statusColors = [
                    'pending' => 'bg-yellow-100 text-yellow-800',
                    'approved' => 'bg-green-100 text-green-800',
                    'declined' => 'bg-red-100 text-red-800'
                ];
                $statusColor = $statusColors[$supplyRequest->status] ?? 'bg-gray-100 text-gray-800';
            @endphp
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $statusColor }}">
                {{ ucfirst($supplyRequest->status) }}
            </span>
            @php
                $cartItem = \App\Models\DepartmentCartItem::where('supply_request_id', $supplyRequest->id)->first();
            @endphp
            @if($cartItem && $supplyRequest->department_id)
                <div class="mt-3 flex items-center space-x-3">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                        <i class="fas fa-check mr-1"></i> Added to Department Cart
                    </span>
                    <a href="{{ route('department-carts.show', $supplyRequest->department_id) }}" class="text-sm text-blue-600 hover:text-blue-800">
                        View Cart
                    </a>
                </div>
            @endif
        </div>

        <!-- Request Information -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Request Information</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Item Name -->
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Item Name</label>
                        <p class="text-lg font-semibold text-gray-900">{{ $supplyRequest->item_name }}</p>
                    </div>

                    <!-- Quantity -->
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Quantity</label>
                        <p class="text-lg font-semibold text-gray-900">{{ number_format($supplyRequest->quantity) }} {{ $supplyRequest->unit }}</p>
                    </div>

                    <!-- Department -->
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Department</label>
                        <p class="text-lg font-semibold text-gray-900">{{ $supplyRequest->department->department_name ?? 'N/A' }}</p>
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Status</label>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $statusColor }}">
                            {{ ucfirst($supplyRequest->status) }}
                        </span>
                    </div>

                    <!-- Request Date -->
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Request Date</label>
                        <p class="text-lg font-semibold text-gray-900">{{ $supplyRequest->created_at->format('M d, Y') }}</p>
                    </div>

                    <!-- Last Updated -->
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Last Updated</label>
                        <p class="text-lg font-semibold text-gray-900">{{ $supplyRequest->updated_at->format('M d, Y H:i') }}</p>
                    </div>
                </div>

                <!-- Description -->
                @if($supplyRequest->description)
                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-500 mb-2">Description</label>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-gray-900">{{ $supplyRequest->description }}</p>
                        </div>
                    </div>
                @endif

                <!-- Admin Actions: Approve/Decline integrated within Request Information -->
                @if($supplyRequest->status === 'pending')
                    <div class="mt-6">
                        <div class="flex flex-wrap gap-3">
                            @can('approve', $supplyRequest)
                                <form action="{{ route('supply-requests.approve', $supplyRequest) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" 
                                            class="px-4 py-2 text-sm font-medium text-white bg-green-600 border border-transparent rounded-md shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                                            onclick="return confirm('Are you sure you want to approve this request?')">
                                        Approve Request
                                    </button>
                                </form>
                            @endcan
                            @can('decline', $supplyRequest)
                                <form action="{{ route('supply-requests.decline', $supplyRequest) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" 
                                            class="px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                            onclick="return confirm('Are you sure you want to decline this request?')">
                                        Decline Request
                                    </button>
                                </form>
                            @endcan
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Actions -->
        @if($supplyRequest->status === 'pending')
            <div class="mt-6 bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Actions</h3>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('supply-requests.edit', $supplyRequest) }}" 
                       class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Edit Request
                    </a>
                    
                    @if(auth()->user()->role->role_name === 'Super Admin' || auth()->user()->role->role_name === 'Admin')
                        <form action="{{ route('supply-requests.destroy', $supplyRequest) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                    onclick="return confirm('Are you sure you want to delete this request? This action cannot be undone.')">
                                Delete Request
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @endif

        <!-- Request Timeline -->
        <div class="mt-6 bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Request Timeline</h3>
            <div class="flow-root">
                <ul class="-mb-8">
                    <li>
                        <div class="relative pb-8">
                            <div class="relative flex space-x-3">
                                <div>
                                    <span class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center ring-8 ring-white">
                                        <svg class="h-4 w-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                    </span>
                                </div>
                                <div class="min-w-0 flex-1 pt-1.5">
                                    <div>
                                        <p class="text-sm text-gray-500">Request created on <time datetime="{{ $supplyRequest->created_at->toISOString() }}">{{ $supplyRequest->created_at->format('M d, Y \a\t H:i') }}</time></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                    
                    @if($supplyRequest->status !== 'pending')
                        <li>
                            <div class="relative">
                                <div class="relative flex space-x-3">
                                    <div>
                                        @if($supplyRequest->status === 'approved')
                                            <span class="h-8 w-8 rounded-full bg-green-500 flex items-center justify-center ring-8 ring-white">
                                                <svg class="h-4 w-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                </svg>
                                            </span>
                                        @else
                                            <span class="h-8 w-8 rounded-full bg-red-500 flex items-center justify-center ring-8 ring-white">
                                                <svg class="h-4 w-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                </svg>
                                            </span>
                                        @endif
                                    </div>
                                    <div class="min-w-0 flex-1 pt-1.5">
                                        <div>
                                            <p class="text-sm text-gray-500">Request {{ $supplyRequest->status }} on <time datetime="{{ $supplyRequest->updated_at->toISOString() }}">{{ $supplyRequest->updated_at->format('M d, Y \a\t H:i') }}</time></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    @endif
                </ul>
            </div>
        </div>

        <!-- Restock & Order Timeline -->
        <div class="mt-6 bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Order Timeline</h3>
                @if(isset($supply) && $supply)
                    <a href="{{ route('supplies.show', $supply) }}" class="text-sm text-blue-600 hover:text-blue-800">View Supply</a>
                @endif
            </div>
            @if(isset($supply) && $supply && isset($restockRequests) && $restockRequests->count() > 0)
                <div class="flow-root">
                    <ul class="-mb-8">
                        @foreach($restockRequests as $rr)
                            @php
                                $statusColors = [
                                    'pending' => 'bg-yellow-500',
                                    'ordered' => 'bg-indigo-500',
                                    'delivered' => 'bg-green-500',
                                    'fulfilled' => 'bg-green-500',
                                    'rejected' => 'bg-red-500',
                                ];
                                $color = $statusColors[$rr->status] ?? 'bg-gray-400';
                                $time = $rr->status === 'pending' ? $rr->created_at : $rr->updated_at;
                            @endphp
                            <li>
                                <div class="relative pb-8">
                                    <div class="relative flex space-x-3">
                                        <div>
                                            <span class="h-8 w-8 rounded-full {{ $color }} flex items-center justify-center ring-8 ring-white">
                                                @if($rr->status === 'delivered' || $rr->status === 'fulfilled')
                                                    <svg class="h-4 w-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                    </svg>
                                                @elseif($rr->status === 'ordered')
                                                    <svg class="h-4 w-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M2 3a1 1 0 011-1h2a1 1 0 011 1v1h8V3a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 01-1 1h-2v7a2 2 0 01-2 2H7a2 2 0 01-2-2V8H3a1 1 0 01-1-1V3z" />
                                                    </svg>
                                                @elseif($rr->status === 'pending')
                                                    <svg class="h-4 w-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 2a8 8 0 100 16 8 8 0 000-16zM9 4a1 1 0 012 0v6a1 1 0 01-.293.707l-3 3a1 1 0 11-1.414-1.414L9 9.586V4z" clip-rule="evenodd" />
                                                    </svg>
                                                @else
                                                    <svg class="h-4 w-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.536-10.95a1 1 0 00-1.414-1.414L10 7.757 7.879 5.636a1 1 0 10-1.414 1.414L8.586 9.172l-2.121 2.121a1 1 0 101.414 1.414L10 10.586l2.121 2.121a1 1 0 001.414-1.414L11.414 9.172l2.122-2.122z" clip-rule="evenodd" />
                                                    </svg>
                                                @endif
                                            </span>
                                        </div>
                                        <div class="min-w-0 flex-1 pt-1.5">
                                            <div class="text-sm text-gray-900">
                                                @if($rr->status === 'pending')
                                                    <p>Order requested for <span class="font-semibold">{{ $rr->quantity }}</span> {{ $supply->unit ?? 'units' }}</p>
                                                @elseif($rr->status === 'ordered')
                                                    <p>Order placed for <span class="font-semibold">{{ $rr->quantity }}</span> {{ $supply->unit ?? 'units' }} @if($rr->supplier) with <span class="font-semibold">{{ $rr->supplier->name }}</span>@endif</p>
                                                @elseif($rr->status === 'delivered' || $rr->status === 'fulfilled')
                                                    <p>Delivery received for <span class="font-semibold">{{ $rr->quantity }}</span> {{ $supply->unit ?? 'units' }} (inventory updated)</p>
                                                @else
                                                    <p>Order status: <span class="font-semibold">{{ ucfirst($rr->status) }}</span> ({{ $rr->quantity }} {{ $supply->unit ?? 'units' }})</p>
                                                @endif
                                            </div>
                                            <p class="text-sm text-gray-500">on <time datetime="{{ $time->toISOString() }}">{{ $time->format('M d, Y \a\t H:i') }}</time></p>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @else
                <div class="text-sm text-gray-600">
                    @if(!$supply)
                        <p>No linked supply yet. Once approved, an order request will be created and tracked here.</p>
                    @else
                        <p>No order activity found for this supply yet.</p>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
@endsection