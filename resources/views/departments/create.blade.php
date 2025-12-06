@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-2xl mx-auto">
        <div class="bg-white shadow-lg rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h2 class="text-xl font-semibold text-gray-800">Add New Department</h2>
                    <a href="{{ route('departments.index') }}" class="text-gray-600 hover:text-gray-800 transition duration-200">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Departments
                    </a>
                </div>
            </div>

            <form action="{{ route('departments.store') }}" method="POST" class="px-6 py-6">
                @csrf
                
                <div class="space-y-6">
                    <!-- Department Name -->
                    <div>
                        <label for="department_name" class="block text-sm font-medium text-gray-700 mb-2">
                            Department Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="department_name" 
                               name="department_name" 
                               value="{{ old('department_name') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('department_name') border-red-500 @enderror"
                               placeholder="Enter department name"
                               required>
                        @error('department_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Dean -->
                    <div>
                        <label for="dean_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Dean (Optional)
                        </label>
                        <select id="dean_id" 
                                name="dean_id" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('dean_id') border-red-500 @enderror">
                            <option value="">Select a Dean (Optional)</option>
                            @foreach($deans as $dean)
                                <option value="{{ $dean->id }}" {{ old('dean_id') == $dean->id ? 'selected' : '' }}>
                                    {{ $dean->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('dean_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex justify-end space-x-4 mt-8 pt-6 border-t border-gray-200">
                    <a href="{{ route('departments.index') }}" 
                       class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition duration-200">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="px-6 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition duration-200">
                        <i class="fas fa-save mr-2"></i>Save Department
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection