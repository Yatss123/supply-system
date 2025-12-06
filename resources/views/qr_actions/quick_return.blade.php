@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-md mx-auto bg-white rounded-lg shadow-md overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-red-500 to-red-600 px-6 py-4">
            <h1 class="text-xl font-bold text-white flex items-center">
                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Quick Return
            </h1>
        </div>

        <!-- Supply Information -->
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center space-x-3">
                <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                </div>
                <div>
                    <h2 class="font-semibold text-gray-900">{{ $supply->name }}</h2>
                    <p class="text-sm text-gray-600">{{ $supply->category->name ?? 'No Category' }}</p>
                </div>
            </div>
        </div>

        <!-- Return Form -->
        <form action="{{ route('qr.process-return', $supply) }}" method="POST" enctype="multipart/form-data" class="p-6">
            @csrf

            <!-- Borrowed Items Selection -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-3">Select Items to Return</label>
                
                @if($borrowedItems->count() > 0)
                    <div class="space-y-3">
                        @foreach($borrowedItems as $item)
                            <div class="border border-gray-200 rounded-lg p-4">
                                <div class="flex items-start space-x-3">
                                    <input type="checkbox" 
                                           name="borrowed_items[]" 
                                           value="{{ $item->id }}" 
                                           id="item_{{ $item->id }}"
                                           class="mt-1 h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded">
                                    
                                    <div class="flex-1">
                                        <label for="item_{{ $item->id }}" class="block text-sm font-medium text-gray-900 cursor-pointer">
                                            @if($item instanceof \App\Models\BorrowedItem)
                                                Regular Borrow
                                            @else
                                                Inter-Department Borrow
                                                <span class="text-xs text-gray-500">(from {{ $item->lendingDepartment->name ?? 'Unknown' }})</span>
                                            @endif
                                        </label>
                                        
                                        <div class="mt-1 text-xs text-gray-600">
                                            <p>Quantity: {{ $item->quantity_borrowed }}</p>
                                            <p>Borrowed: {{ $item->created_at->format('M d, Y') }}</p>
                                            @if($item->due_date)
                                                <p class="@if($item->due_date < now()) text-red-600 @endif">
                                                    Due: {{ \Carbon\Carbon::parse($item->due_date)->format('M d, Y') }}
                                                </p>
                                            @endif
                                        </div>

                                        <!-- Quantity to Return -->
                                        <div class="mt-2">
                                            <label class="block text-xs font-medium text-gray-700">Quantity to Return</label>
                                            <input type="number" 
                                                   name="return_quantities[{{ $item->id }}]" 
                                                   min="1" 
                                                   max="{{ $item->quantity_borrowed }}" 
                                                   value="{{ $item->quantity_borrowed }}"
                                                   class="mt-1 block w-full px-3 py-1 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500 text-sm">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8 text-gray-500">
                        <svg class="w-12 h-12 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2 2v-5m16 0h-2M4 13h2m0 0V9a2 2 0 012-2h2m0 0V6a2 2 0 012-2h2.09M7 13h10v5a2 2 0 01-2 2H9a2 2 0 01-2-2v-5z"></path>
                        </svg>
                        <p>No borrowed items found for this supply.</p>
                    </div>
                @endif
            </div>

            @if($borrowedItems->count() > 0)
                <!-- Return Notes -->
                <div class="mb-6">
                    <label for="return_notes" class="block text-sm font-medium text-gray-700 mb-2">Return Notes (Optional)</label>
                    <textarea name="return_notes" 
                              id="return_notes" 
                              rows="3" 
                              class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500 text-sm"
                              placeholder="Any notes about the condition of returned items..."></textarea>
                </div>

                <!-- Photo Upload -->
                <div class="mb-6">
                    <label for="return_photo" class="block text-sm font-medium text-gray-700 mb-2">
                        Return Photo (Optional)
                        <span class="text-xs text-gray-500">- Take a photo of the returned items</span>
                    </label>
                    
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:border-red-400 transition-colors">
                        <div class="space-y-1 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <div class="flex text-sm text-gray-600">
                                <label for="return_photo" class="relative cursor-pointer bg-white rounded-md font-medium text-red-600 hover:text-red-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-red-500">
                                    <span>Upload a photo</span>
                                    <input id="return_photo" name="return_photo" type="file" accept="image/*" capture="environment" class="sr-only">
                                </label>
                                <p class="pl-1">or drag and drop</p>
                            </div>
                            <p class="text-xs text-gray-500">PNG, JPG, GIF up to 10MB</p>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex space-x-3">
                    <button type="submit" 
                            class="flex-1 bg-red-600 hover:bg-red-700 text-white font-medium py-3 px-4 rounded-lg transition duration-200 flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Process Return
                    </button>
                    
                    <a href="{{ route('qr.actions', $supply) }}" 
                       class="px-4 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition duration-200 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </a>
                </div>
            @endif
        </form>
    </div>
</div>

<script>
// Auto-disable quantity input when checkbox is unchecked
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('input[name="borrowed_items[]"]');
    
    checkboxes.forEach(checkbox => {
        const itemId = checkbox.value;
        const quantityInput = document.querySelector(`input[name="return_quantities[${itemId}]"]`);
        
        if (quantityInput) {
            // Initial state
            quantityInput.disabled = !checkbox.checked;
            
            // Toggle on change
            checkbox.addEventListener('change', function() {
                quantityInput.disabled = !this.checked;
                if (!this.checked) {
                    quantityInput.value = quantityInput.max;
                }
            });
        }
    });
});

// Photo preview functionality
document.getElementById('return_photo').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            // Create preview if doesn't exist
            let preview = document.getElementById('photo-preview');
            if (!preview) {
                preview = document.createElement('div');
                preview.id = 'photo-preview';
                preview.className = 'mt-2';
                e.target.parentNode.parentNode.appendChild(preview);
            }
            
            preview.innerHTML = `
                <img src="${e.target.result}" alt="Return photo preview" class="max-w-full h-32 object-cover rounded-md border">
                <p class="text-xs text-gray-500 mt-1">Photo ready for upload</p>
            `;
        };
        reader.readAsDataURL(file);
    }
});
</script>
@endsection