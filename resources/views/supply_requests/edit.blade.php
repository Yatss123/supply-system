@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-2xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Edit Supply Request</h1>
            <p class="text-gray-600 mt-2">Request #{{ $supplyRequest->id }} - Update your supply request details</p>
        </div>

        <!-- Status Notice -->
        @if($supplyRequest->status !== 'pending')
            <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-yellow-800">Request Status: {{ ucfirst($supplyRequest->status) }}</h3>
                        <div class="mt-2 text-sm text-yellow-700">
                            <p>This request has already been {{ $supplyRequest->status }}. Changes may not be processed.</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Form Card -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <form action="{{ route('supply-requests.update', $supplyRequest) }}" method="POST">
                @csrf
                @method('PATCH')

                <!-- Item Name -->
                <div class="mb-6">
                    <label for="item_name" class="block text-sm font-medium text-gray-700 mb-2">
                        Item Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="item_name" 
                           name="item_name" 
                           value="{{ old('item_name', $supplyRequest->item_name) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('item_name') border-red-500 @enderror"
                           placeholder="Enter the name of the item you need"
                           required>
                    @error('item_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Quantity and Unit Row -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <!-- Quantity -->
                    <div>
                        <label for="quantity" class="block text-sm font-medium text-gray-700 mb-2">
                            Quantity <span class="text-red-500">*</span>
                        </label>
                        <input type="number" 
                               id="quantity" 
                               name="quantity" 
                               value="{{ old('quantity', $supplyRequest->quantity) }}"
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
                            Unit <span class="text-red-500">*</span>
                        </label>
                        <select id="unit" 
                                name="unit" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('unit') border-red-500 @enderror"
                                required>
                            <option value="">Select unit</option>
                            <option value="pieces" {{ old('unit', $supplyRequest->unit) == 'pieces' ? 'selected' : '' }}>Pieces</option>
                            <option value="boxes" {{ old('unit', $supplyRequest->unit) == 'boxes' ? 'selected' : '' }}>Boxes</option>
                            <option value="packs" {{ old('unit', $supplyRequest->unit) == 'packs' ? 'selected' : '' }}>Packs</option>
                            <option value="bottles" {{ old('unit', $supplyRequest->unit) == 'bottles' ? 'selected' : '' }}>Bottles</option>
                            <option value="kg" {{ old('unit', $supplyRequest->unit) == 'kg' ? 'selected' : '' }}>Kilograms</option>
                            <option value="liters" {{ old('unit', $supplyRequest->unit) == 'liters' ? 'selected' : '' }}>Liters</option>
                            <option value="meters" {{ old('unit', $supplyRequest->unit) == 'meters' ? 'selected' : '' }}>Meters</option>
                            <option value="sets" {{ old('unit', $supplyRequest->unit) == 'sets' ? 'selected' : '' }}>Sets</option>
                        </select>
                        @error('unit')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Department -->
                <div class="mb-6">
                    <label for="department_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Department <span class="text-red-500">*</span>
                    </label>
                    <select id="department_id" 
                            name="department_id" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('department_id') border-red-500 @enderror"
                            required>
                        <option value="">Select department</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}" {{ old('department_id', $supplyRequest->department_id) == $department->id ? 'selected' : '' }}>
                                {{ $department->department_name }}
                            </option>
                        @endforeach
                    </select>
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
                              placeholder="Provide additional details about the request (optional)">{{ old('description', $supplyRequest->description) }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Current Status -->
                <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Current Status</h3>
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
                    <p class="text-sm text-gray-600 mt-2">
                        Last updated: {{ $supplyRequest->updated_at->format('M d, Y \a\t H:i') }}
                    </p>
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                    <a href="{{ route('supply-requests.show', $supplyRequest) }}" 
                       class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Cancel
                    </a>
                    <div class="flex space-x-3">
                        <button type="submit" 
                                class="px-6 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Update Request
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Delete Section (for admins or if pending) -->
        @if((auth()->user()->role->role_name === 'Super Admin' || auth()->user()->role->role_name === 'Admin') || $supplyRequest->status === 'pending')
            <div class="mt-6 bg-white rounded-lg shadow-md p-6 border-l-4 border-red-500">
                <h3 class="text-lg font-semibold text-red-900 mb-2">Danger Zone</h3>
                <p class="text-sm text-red-700 mb-4">
                    Once you delete this supply request, there is no going back. Please be certain.
                </p>
                <form action="{{ route('supply-requests.destroy', $supplyRequest) }}" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            class="px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                            onclick="return confirm('Are you sure you want to delete this supply request? This action cannot be undone.')">
                        Delete Request
                    </button>
                </form>
            </div>
        @endif

        <!-- Help Text -->
        <div class="mt-6 p-4 bg-blue-50 rounded-lg">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800">Update Guidelines</h3>
                    <div class="mt-2 text-sm text-blue-700">
                        <ul class="list-disc list-inside space-y-1">
                            <li>You can only edit requests that are still pending</li>
                            <li>Changes to approved or declined requests may not be processed</li>
                            <li>Be specific about any changes you make</li>
                            <li>Contact an administrator if you need to modify a processed request</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection