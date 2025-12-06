@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header Section -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Edit Supply</h1>
        <div class="flex space-x-2">
            <a href="{{ route('supplies.show', $supply) }}" 
               class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                View Supply
            </a>
            <button type="button"
                    onclick="window.history.back()"
                    class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Back
            </button>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white shadow-md rounded-lg p-6">
        <form method="POST" action="{{ route('supplies.update', $supply) }}">
            @csrf
            @method('PATCH')

            <!-- Supply Name -->
            <div class="mb-4">
                <x-input-label for="name" :value="__('Supply Name')" />
                <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $supply->name)" required autofocus />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <!-- Description -->
            <div class="mb-4">
                <x-input-label for="description" :value="__('Description')" />
                <textarea id="description" name="description" rows="3" 
                          class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $supply->description) }}</textarea>
                <x-input-error :messages="$errors->get('description')" class="mt-2" />
            </div>

            <!-- Tin (optional) -->
            <div class="mb-4">
                <x-input-label for="tin" :value="__('TIN (optional)')" />
                <x-text-input id="tin" class="block mt-1 w-full" type="text" name="tin" :value="old('tin', $supply->tin)" />
                <x-input-error :messages="$errors->get('tin')" class="mt-2" />
            </div>

            <!-- Quantity and Unit Row -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <x-input-label for="quantity" :value="__('Quantity')" />
                    @php $hasVariants = $supply->hasVariants(); @endphp
                    <x-text-input id="quantity" class="block mt-1 w-full" type="number" name="quantity" :value="old('quantity', $supply->quantity)" min="0" {{ $hasVariants ? 'disabled' : '' }} />
                    <x-input-error :messages="$errors->get('quantity')" class="mt-2" />
                    @if($hasVariants)
                        <p class="text-sm text-gray-500 mt-1">Quantity is managed via variants. Edit variant quantities instead.</p>
                    @endif
                </div>

                <div>
                    <x-input-label for="unit" :value="__('Unit')" />
                    <select id="unit" name="unit" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        <option value="">Select Unit</option>
                        <option value="pieces" {{ old('unit', $supply->unit) == 'pieces' ? 'selected' : '' }}>Pieces</option>
                        <option value="boxes" {{ old('unit', $supply->unit) == 'boxes' ? 'selected' : '' }}>Boxes</option>
                        <option value="packs" {{ old('unit', $supply->unit) == 'packs' ? 'selected' : '' }}>Packs</option>
                        <option value="bottles" {{ old('unit', $supply->unit) == 'bottles' ? 'selected' : '' }}>Bottles</option>
                        <option value="kg" {{ old('unit', $supply->unit) == 'kg' ? 'selected' : '' }}>Kilograms</option>
                        <option value="liters" {{ old('unit', $supply->unit) == 'liters' ? 'selected' : '' }}>Liters</option>
                        <option value="meters" {{ old('unit', $supply->unit) == 'meters' ? 'selected' : '' }}>Meters</option>
                        <option value="rolls" {{ old('unit', $supply->unit) == 'rolls' ? 'selected' : '' }}>Rolls</option>
                        <option value="sets" {{ old('unit', $supply->unit) == 'sets' ? 'selected' : '' }}>Sets</option>
                        <option value="other" {{ old('unit', $supply->unit) == 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                    <x-input-error :messages="$errors->get('unit')" class="mt-2" />
                </div>
            </div>

            <!-- Minimum Stock Level -->
            <div class="mb-4">
                <x-input-label for="minimum_stock_level" :value="__('Minimum Stock Level')" />
                <x-text-input id="minimum_stock_level" class="block mt-1 w-full" type="number" name="minimum_stock_level" :value="old('minimum_stock_level', $supply->minimum_stock_level)" required min="0" />
                <x-input-error :messages="$errors->get('minimum_stock_level')" class="mt-2" />
                <p class="text-sm text-gray-500 mt-1">Alert will be triggered when stock falls below this level</p>
            </div>

            <div class="mb-4">
                <x-input-label for="unit_price" :value="__('Unit Price (optional)')" />
                <x-text-input id="unit_price" class="block mt-1 w-full" type="number" step="0.01" min="0" name="unit_price" :value="old('unit_price', $supply->unit_price)" />
                <x-input-error :messages="$errors->get('unit_price')" class="mt-2" />
            </div>

            <!-- Category -->
            <div class="mb-4">
                <x-input-label for="category_id" :value="__('Category')" />
                <select id="category_id" name="category_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    <option value="">Select Category</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" 
                                {{ old('category_id', $supply->categories->first()->id ?? '') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('category_id')" class="mt-2" />
            </div>

            <!-- Supply Type -->
            <div class="mb-4">
                <x-input-label for="supply_type" :value="__('Supply Type')" />
                <select id="supply_type" name="supply_type" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    <option value="">Select Supply Type</option>
                    <option value="consumable" {{ old('supply_type', $supply->supply_type) == 'consumable' ? 'selected' : '' }}>
                        Consumable - Items that are used up and not returned
                    </option>
                    <option value="borrowable" {{ old('supply_type', $supply->supply_type) == 'borrowable' ? 'selected' : '' }}>
                        Borrowable - Items that must be returned after use
                    </option>
                    <option value="grantable" {{ old('supply_type', $supply->supply_type) == 'grantable' ? 'selected' : '' }}>
                        Grantable - Items that are given away permanently
                    </option>
                </select>
                <x-input-error :messages="$errors->get('supply_type')" class="mt-2" />
                <p class="text-sm text-gray-500 mt-1">Choose how this supply item will be distributed</p>
            </div>

            <!-- Suppliers -->
            <div class="mb-6">
                <x-input-label for="supplier_ids" :value="__('Suppliers')" />
                <div class="mt-2 space-y-2 max-h-40 overflow-y-auto border border-gray-300 rounded-md p-3">
                    @php
                        $selectedSuppliers = old('supplier_ids', $supply->suppliers->pluck('id')->toArray());
                    @endphp
                    @foreach($suppliers as $supplier)
                        <label class="flex items-center">
                            <input type="checkbox" name="supplier_ids[]" value="{{ $supplier->id }}" 
                                   class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                   {{ in_array($supplier->id, $selectedSuppliers) ? 'checked' : '' }}>
                            <span class="ml-2 text-sm text-gray-700">{{ $supplier->name }}</span>
                        </label>
                    @endforeach
                </div>
                <x-input-error :messages="$errors->get('supplier_ids')" class="mt-2" />
                <p class="text-sm text-gray-500 mt-1">Select one or more suppliers for this supply</p>
            </div>

            <!-- Current Information Display -->
            <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                <h3 class="text-lg font-medium text-gray-900 mb-3">Current Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="font-medium text-gray-700">Current Stock Status:</span>
                        @if($supply->quantity <= 0)
                            <span class="ml-2 inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                Out of Stock
                            </span>
                        @elseif($supply->quantity <= $supply->minimum_stock_level)
                            <span class="ml-2 inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                Low Stock
                            </span>
                        @else
                            <span class="ml-2 inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                In Stock
                            </span>
                        @endif
                    </div>
                    <div>
                        <span class="font-medium text-gray-700">Last Updated:</span>
                        <span class="ml-2 text-gray-600">{{ $supply->updated_at->format('M d, Y \a\t g:i A') }}</span>
                    </div>
                    <div>
                        <span class="font-medium text-gray-700">Current Categories:</span>
                        <span class="ml-2 text-gray-600">
                            @if($supply->categories->count() > 0)
                                {{ $supply->categories->pluck('name')->join(', ') }}
                            @else
                                None assigned
                            @endif
                        </span>
                    </div>
                    <div>
                        <span class="font-medium text-gray-700">Current Suppliers:</span>
                        <span class="ml-2 text-gray-600">
                            @if($supply->suppliers->count() > 0)
                                {{ $supply->suppliers->pluck('name')->join(', ') }}
                            @else
                                None assigned
                            @endif
                        </span>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end space-x-4">
                <a href="{{ route('supplies.show', $supply) }}" 
                   class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Cancel
                </a>
                <x-primary-button>
                    {{ __('Update Supply') }}
                </x-primary-button>
            </div>
        </form>
    </div>

    <!-- Delete Section (for admins) -->
    @if(auth()->user()->hasAdminPrivileges())
        <div class="mt-6 bg-white shadow-md rounded-lg p-6 border-l-4 border-red-500">
            <h3 class="text-lg font-medium text-red-900 mb-2">Danger Zone</h3>
            <p class="text-sm text-red-700 mb-4">
                Once you delete this supply, all of its data will be permanently removed. This action cannot be undone.
            </p>
            <form action="{{ route('supplies.destroy', $supply) }}" method="POST" 
                  onsubmit="return confirm('Are you sure you want to delete this supply? This action cannot be undone.')">
                @csrf
                @method('DELETE')
                <button type="submit" 
                        class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                    Delete Supply
                </button>
            </form>
        </div>
    @endif
</div>
@endsection
