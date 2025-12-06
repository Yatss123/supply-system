@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header Section -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Add New Supply</h1>
        <a href="{{ route('dashboard') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
            Back
        </a>
    </div>

    <!-- Form -->
    <div class="bg-white shadow-md rounded-lg p-6">
        <form method="POST" action="{{ route('supplies.store') }}" id="create_supply_form">
            @csrf

            <!-- Supply Name -->
            <div class="mb-4">
                <x-input-label for="name" :value="__('Supply Name')" />
                <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                <p id="name-duplicate-warning" class="mt-2 text-sm text-red-600 hidden">Item already in inventory</p>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const nameInput = document.getElementById('name');
        const warn = document.getElementById('name-duplicate-warning');
        if (!nameInput || !warn) return;
        let timer;
        nameInput.addEventListener('input', function () {
            clearTimeout(timer);
            const name = nameInput.value.trim();
            if (!name) { warn.classList.add('hidden'); return; }
            timer = setTimeout(async () => {
                try {
                    const resp = await fetch(`{{ route('supplies.check-name') }}?name=${encodeURIComponent(name)}`);
                    const data = await resp.json();
                    if (data && data.exists) {
                        warn.classList.remove('hidden');
                    } else {
                        warn.classList.add('hidden');
                    }
                } catch (e) {
                    warn.classList.add('hidden');
                }
            }, 250);
        });
    });
</script>
            </div>

            <!-- Description -->
            <div class="mb-4">
                <x-input-label for="description" :value="__('Description')" />
                <textarea id="description" name="description" rows="3" 
                           class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description') }}</textarea>
                <x-input-error :messages="$errors->get('description')" class="mt-2" />
            </div>

            <div class="mb-4">
                <x-input-label for="quantity" :value="__('Initial Quantity')" />
                <x-text-input id="quantity" class="block mt-1 w-full" type="number" min="0" name="quantity" :value="old('quantity', 0)" required />
                <x-input-error :messages="$errors->get('quantity')" class="mt-2" />
            </div>

            <div class="mb-4">
                <x-input-label for="unit" :value="__('Unit')" />
                <x-text-input id="unit" class="block mt-1 w-full" type="text" name="unit" :value="old('unit')" required />
                <x-input-error :messages="$errors->get('unit')" class="mt-2" />
            </div>

            <div class="mb-4">
                <x-input-label for="minimum_stock_level" :value="__('Minimum Stock Level')" />
                <x-text-input id="minimum_stock_level" class="block mt-1 w-full" type="number" min="0" name="minimum_stock_level" :value="old('minimum_stock_level', 0)" required />
                <x-input-error :messages="$errors->get('minimum_stock_level')" class="mt-2" />
            </div>

            <div class="mb-4">
                <x-input-label for="unit_price" :value="__('Unit Price (optional)')" />
                <x-text-input id="unit_price" class="block mt-1 w-full" type="number" step="0.01" min="0" name="unit_price" :value="old('unit_price')" />
                <x-input-error :messages="$errors->get('unit_price')" class="mt-2" />
            </div>

            <!-- Categories -->
            <div class="mb-4">
                <x-input-label for="category_id" :value="__('Category')" />
                <select id="category_id" name="category_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    <option value="">Select Category</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}"
                            {{ old('category_id') == $category->id ? 'selected' : '' }}>
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
                    <option value="consumable" {{ old('supply_type') == 'consumable' ? 'selected' : '' }}>
                        Consumable - Items that are used up and not returned
                    </option>
                    <option value="borrowable" {{ old('supply_type') == 'borrowable' ? 'selected' : '' }}>
                        Borrowable - Items that must be returned after use
                    </option>
                    <option value="grantable" {{ old('supply_type') == 'grantable' ? 'selected' : '' }}>
                        Grantable - Items that are given away permanently
                    </option>
                </select>
                <x-input-error :messages="$errors->get('supply_type')" class="mt-2" />
                <p class="text-sm text-gray-500 mt-1">Choose how this supply item will be distributed</p>


            <div class="mb-4">
                <x-input-label for="location_id" :value="__('Location')" />
                <select id="location_id" name="location_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    <option value="">Select Location</option>
                    @foreach($locations as $location)
                        <option value="{{ $location->id }}" {{ old('location_id') == $location->id ? 'selected' : '' }}>
                            {{ $location->name }}
                        </option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('location_id')" class="mt-2" />
            </div>

            <div class="mb-6">
                <x-input-label for="tin" :value="__('TIN (optional)')" />
                <x-text-input id="tin" class="block mt-1 w-full" type="text" name="tin" :value="old('tin')" />
                <x-input-error :messages="$errors->get('tin')" class="mt-2" />
            </div>
            <!-- Suppliers -->
            <div class="mb-6">
                <x-input-label for="supplier_ids" :value="__('Suppliers')" />
                <div class="mt-2 space-y-2 max-h-40 overflow-y-auto border border-gray-300 rounded-md p-3">
                    @foreach($suppliers as $supplier)
                        <label class="flex items-center">
                            <input type="checkbox" name="supplier_ids[]" value="{{ $supplier->id }}" 
                                   class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                   {{ in_array($supplier->id, old('supplier_ids', [])) ? 'checked' : '' }}>
                            <span class="ml-2 text-sm text-gray-700">{{ $supplier->name }}</span>
                        </label>
                    @endforeach
                </div>
                <x-input-error :messages="$errors->get('supplier_ids')" class="mt-2" />
                <p class="text-sm text-gray-500 mt-1">Select one or more suppliers for this supply</p>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end space-x-4">
                <a href="{{ route('dean.supplies.index') }}" 
                   class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Cancel
                </a>
                <x-primary-button>
                    {{ __('Create Supply') }}
                </x-primary-button>

        </form>
    </div>
</div>
@endsection
