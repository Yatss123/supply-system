@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Create Supply Variant</h1>
                    <p class="text-gray-600 mt-1">Add a new variant for: <strong>{{ $supply->name }}</strong></p>
                </div>
                <a href="{{ route('supplies.show', $supply) }}" 
                   class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back to Supply
                </a>
            </div>
        </div>

        <!-- Form -->
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="px-6 py-4 bg-gray-50 border-b">
                <h2 class="text-xl font-semibold text-gray-800">Variant Information</h2>
            </div>
            <div class="p-6">
                <form action="{{ route('supply-variants.store', $supply) }}" method="POST">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Variant Name -->
                        <div class="md:col-span-2">
                            <label for="variant_name" class="block text-sm font-medium text-gray-700 mb-2">
                                Variant Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   id="variant_name" 
                                   name="variant_name" 
                                   value="{{ old('variant_name') }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 @error('variant_name') border-red-500 @enderror"
                                   placeholder="e.g., Medium Male, Large Female, etc."
                                   required>
                            @error('variant_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- SKU -->
                        <div>
                            <label for="sku" class="block text-sm font-medium text-gray-700 mb-2">
                                SKU (Stock Keeping Unit) <span class="text-gray-500 text-sm">(Optional - Auto-generated if empty)</span>
                            </label>
                            <input type="text" 
                                   id="sku" 
                                   name="sku" 
                                   value="{{ old('sku') }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 @error('sku') border-red-500 @enderror"
                                   placeholder="Leave empty for auto-generation">
                            <p class="mt-1 text-sm text-gray-500">
                                If left empty, SKU will be auto-generated as: [Supply Abbreviation]-[Variant Name]-[First Attribute Letter]-[Number]
                            </p>
                            @error('sku')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Tin (Optional) -->
                        <div>
                            <label for="tin" class="block text-sm font-medium text-gray-700 mb-2">
                                Tin (Optional)
                            </label>
                            <input type="text"
                                   id="tin"
                                   name="tin"
                                   value="{{ old('tin') }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 @error('tin') border-red-500 @enderror"
                                   placeholder="Enter Tin value if applicable">
                            @error('tin')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Price -->
                        <div>
                            <label for="price" class="block text-sm font-medium text-gray-700 mb-2">
                                Price (Optional)
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-2 text-gray-500">$</span>
                                <input type="number" 
                                       id="price" 
                                       name="price" 
                                       value="{{ old('price') }}"
                                       step="0.01"
                                       min="0"
                                       class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 @error('price') border-red-500 @enderror"
                                       placeholder="0.00">
                            </div>
                            @error('price')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Quantity -->
                        <div>
                            <label for="quantity" class="block text-sm font-medium text-gray-700 mb-2">
                                Initial Quantity <span class="text-red-500">*</span>
                            </label>
                            <input type="number" 
                                   id="quantity" 
                                   name="quantity" 
                                   value="{{ old('quantity', 0) }}"
                                   min="0"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 @error('quantity') border-red-500 @enderror"
                                   required>
                            @error('quantity')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Attributes Section -->
                    <div class="mt-8">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Variant Attributes</h3>
                        <p class="text-sm text-gray-600 mb-4">Add specific attributes for this variant (e.g., size, color, gender, etc.)</p>
                        
                        <div id="attributes-container">
                            <div class="attribute-row grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Attribute Name</label>
                                    <input type="text" 
                                           name="attributes[0][key]" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                           placeholder="e.g., Size, Color, Gender">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Attribute Value</label>
                                    <div class="flex">
                                        <input type="text" 
                                               name="attributes[0][value]" 
                                               class="flex-1 px-3 py-2 border border-gray-300 rounded-l-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                               placeholder="e.g., Medium, Blue, Male">
                                        <button type="button" 
                                                class="remove-attribute px-3 py-2 bg-red-500 text-white rounded-r-md hover:bg-red-600 focus:outline-none"
                                                onclick="removeAttribute(this)">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button type="button" 
                                id="add-attribute" 
                                class="mt-2 bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded text-sm">
                            Add Another Attribute
                        </button>
                    </div>

                    <!-- Form Actions -->
                    <div class="mt-8 pt-6 border-t border-gray-200">
                        <div class="flex justify-end space-x-3">
                            <a href="{{ route('supplies.show', $supply) }}" 
                               class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                                Cancel
                            </a>
                            <button type="submit" 
                                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Create Variant
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
let attributeIndex = 1;

document.getElementById('add-attribute').addEventListener('click', function() {
    const container = document.getElementById('attributes-container');
    const newRow = document.createElement('div');
    newRow.className = 'attribute-row grid grid-cols-1 md:grid-cols-2 gap-4 mb-4';
    newRow.innerHTML = `
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Attribute Name</label>
            <input type="text" 
                   name="attributes[${attributeIndex}][key]" 
                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                   placeholder="e.g., Size, Color, Gender">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Attribute Value</label>
            <div class="flex">
                <input type="text" 
                       name="attributes[${attributeIndex}][value]" 
                       class="flex-1 px-3 py-2 border border-gray-300 rounded-l-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                       placeholder="e.g., Medium, Blue, Male">
                <button type="button" 
                        class="remove-attribute px-3 py-2 bg-red-500 text-white rounded-r-md hover:bg-red-600 focus:outline-none"
                        onclick="removeAttribute(this)">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
    `;
    container.appendChild(newRow);
    attributeIndex++;
});

function removeAttribute(button) {
    const row = button.closest('.attribute-row');
    if (document.querySelectorAll('.attribute-row').length > 1) {
        row.remove();
        // Reindex all remaining attributes to ensure proper sequential indexing
        reindexAttributes();
    } else {
        alert('At least one attribute is required.');
    }
}

function reindexAttributes() {
    const rows = document.querySelectorAll('.attribute-row');
    rows.forEach((row, index) => {
        const keyInput = row.querySelector('input[name*="[key]"]');
        const valueInput = row.querySelector('input[name*="[value]"]');
        
        if (keyInput) keyInput.name = `attributes[${index}][key]`;
        if (valueInput) valueInput.name = `attributes[${index}][value]`;
    });
    
    // Update the attributeIndex to be the next available index
    attributeIndex = rows.length;
}</script>
</script>
@endsection