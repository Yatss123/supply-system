@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header Section -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Edit Supplier</h1>
        <div class="flex space-x-2">
            <a href="{{ route('suppliers.show', $supplier) }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                View Supplier
            </a>
            <a href="{{ route('suppliers.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Back to Suppliers
            </a>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
        <form method="POST" action="{{ route('suppliers.update', $supplier) }}">
            @csrf
            @method('PUT')
            
            <!-- Name -->
            <div class="mb-4">
                <label for="name" class="block text-gray-700 text-sm font-bold mb-2">
                    Supplier Name <span class="text-red-500">*</span>
                </label>
                <input type="text" id="name" name="name" value="{{ old('name', $supplier->name) }}" 
                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('name') border-red-500 @enderror" 
                       required>
                @error('name')
                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Contact Person -->
            <div class="mb-4">
                <label for="contact_person" class="block text-gray-700 text-sm font-bold mb-2">
                    Contact Person
                </label>
                <input type="text" id="contact_person" name="contact_person" value="{{ old('contact_person', $supplier->contact_person) }}" 
                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('contact_person') border-red-500 @enderror">
                @error('contact_person')
                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Phone Numbers -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label for="phone1" class="block text-gray-700 text-sm font-bold mb-2">
                        Primary Phone
                    </label>
                    <input type="text" id="phone1" name="phone1" value="{{ old('phone1', $supplier->phone1) }}" 
                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('phone1') border-red-500 @enderror">
                    @error('phone1')
                        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="phone2" class="block text-gray-700 text-sm font-bold mb-2">
                        Secondary Phone
                    </label>
                    <input type="text" id="phone2" name="phone2" value="{{ old('phone2', $supplier->phone2) }}" 
                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('phone2') border-red-500 @enderror">
                    @error('phone2')
                        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Email -->
            <div class="mb-4">
                <label for="email" class="block text-gray-700 text-sm font-bold mb-2">
                    Email Address
                </label>
                <input type="email" id="email" name="email" value="{{ old('email', $supplier->email) }}" 
                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('email') border-red-500 @enderror">
                @error('email')
                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Facebook Messenger -->
            <div class="mb-4">
                <label for="facebook_messenger" class="block text-gray-700 text-sm font-bold mb-2">
                    Facebook Messenger
                </label>
                <input type="text" id="facebook_messenger" name="facebook_messenger" value="{{ old('facebook_messenger', $supplier->facebook_messenger) }}"
                       placeholder="username or m.me link"
                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('facebook_messenger') border-red-500 @enderror">
                @error('facebook_messenger')
                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Preferred Contact Method -->
            <div class="mb-4">
                <label for="preferred_contact_method" class="block text-gray-700 text-sm font-bold mb-2">
                    Preferred Contact Method <span class="text-red-500">*</span>
                </label>
                <select id="preferred_contact_method" name="preferred_contact_method" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('preferred_contact_method') border-red-500 @enderror" required>
                    <option value="email" {{ old('preferred_contact_method', $supplier->preferred_contact_method ?? 'email') == 'email' ? 'selected' : '' }}>Email</option>
                    <option value="phone" {{ old('preferred_contact_method', $supplier->preferred_contact_method) == 'phone' ? 'selected' : '' }}>Phone</option>
                    <option value="facebook_messenger" {{ old('preferred_contact_method', $supplier->preferred_contact_method) == 'facebook_messenger' ? 'selected' : '' }}>Facebook Messenger</option>
                </select>
                @error('preferred_contact_method')
                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Address -->
            <div class="mb-4">
                <label for="address" class="block text-gray-700 text-sm font-bold mb-2">
                    Address
                </label>
                <textarea id="address" name="address" rows="3" 
                          class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('address') border-red-500 @enderror">{{ old('address', $supplier->address) }}</textarea>
                @error('address')
                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- City -->
            <div class="mb-4">
                <label for="city" class="block text-gray-700 text-sm font-bold mb-2">
                    City
                </label>
                <input type="text" id="city" name="city" value="{{ old('city', $supplier->city) }}" 
                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('city') border-red-500 @enderror">
                @error('city')
                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- State and Postal Code -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label for="state" class="block text-gray-700 text-sm font-bold mb-2">
                        State/Province
                    </label>
                    <input type="text" id="state" name="state" value="{{ old('state', $supplier->state) }}" 
                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('state') border-red-500 @enderror">
                    @error('state')
                        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="postal_code" class="block text-gray-700 text-sm font-bold mb-2">
                        Postal Code
                    </label>
                    <input type="text" id="postal_code" name="postal_code" value="{{ old('postal_code', $supplier->postal_code) }}" 
                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('postal_code') border-red-500 @enderror">
                    @error('postal_code')
                        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Country -->
            <div class="mb-6">
                <label for="country" class="block text-gray-700 text-sm font-bold mb-2">
                    Country
                </label>
                <input type="text" id="country" name="country" value="{{ old('country', $supplier->country) }}" 
                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('country') border-red-500 @enderror">
                @error('country')
                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Submit Buttons -->
            <div class="flex items-center justify-between">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Update Supplier
                </button>
                <a href="{{ route('suppliers.show', $supplier) }}" class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection