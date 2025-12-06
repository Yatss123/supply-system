@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <!-- Page Header -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">Complete Your Profile</h2>
                        <p class="mt-1 text-sm text-gray-600">
                            Please provide additional information to complete your registration.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Profile Completion Form -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">Profile Information</h3>
                        <p class="mt-1 text-sm text-gray-600">
                            Complete your profile to access the system.
                        </p>
                    </div>
                </div>

                <form method="POST" action="{{ route('profile.complete.store') }}" class="space-y-6">
                    @csrf

                    <!-- Address -->
                    <div>
                        <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
                        <textarea name="address" id="address" rows="3" 
                                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('address') border-red-300 @enderror"
                                  required>{{ old('address') }}</textarea>
                        @error('address')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Date of Birth -->
                    <div>
                        <label for="date_of_birth" class="block text-sm font-medium text-gray-700">Date of Birth</label>
                        <input type="date" name="date_of_birth" id="date_of_birth" 
                               value="{{ old('date_of_birth') }}" 
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('date_of_birth') border-red-300 @enderror"
                               required>
                        @error('date_of_birth')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Civil Status -->
                    <div>
                        <label for="civil_status" class="block text-sm font-medium text-gray-700">Civil Status</label>
                        <select name="civil_status" id="civil_status" 
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('civil_status') border-red-300 @enderror"
                                required>
                            <option value="">Select Civil Status</option>
                            <option value="single" {{ old('civil_status') == 'single' ? 'selected' : '' }}>Single</option>
                            <option value="married" {{ old('civil_status') == 'married' ? 'selected' : '' }}>Married</option>
                            <option value="divorced" {{ old('civil_status') == 'divorced' ? 'selected' : '' }}>Divorced</option>
                            <option value="widowed" {{ old('civil_status') == 'widowed' ? 'selected' : '' }}>Widowed</option>
                        </select>
                        @error('civil_status')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Gender -->
                    <div>
                        <label for="gender" class="block text-sm font-medium text-gray-700">Gender</label>
                        <select name="gender" id="gender" 
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('gender') border-red-300 @enderror"
                                required>
                            <option value="">Select Gender</option>
                            <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Male</option>
                            <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female</option>
                            <option value="other" {{ old('gender') == 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                        @error('gender')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Contact Number -->
                    <div>
                        <label for="contact_number" class="block text-sm font-medium text-gray-700">Contact Number</label>
                        <input type="text" name="contact_number" id="contact_number" 
                               value="{{ old('contact_number') }}" 
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('contact_number') border-red-300 @enderror"
                               required>
                        @error('contact_number')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Role -->
                    <div>
                        <label for="role" class="block text-sm font-medium text-gray-700">Position</label>
                        <select name="role" id="role" 
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('role') border-red-300 @enderror"
                                required>
                            <option value="">Select Position</option>
                            <option value="student" {{ old('role') == 'student' ? 'selected' : '' }}>Student</option>
                            <option value="adviser" {{ old('role') == 'adviser' ? 'selected' : '' }}>Adviser</option>
                            <option value="dean" {{ old('role') == 'dean' ? 'selected' : '' }}>Dean</option>
                        </select>
                        @error('role')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Department -->
                    <div>
                        <label for="department_id" class="block text-sm font-medium text-gray-700">Department</label>
                        <select name="department_id" id="department_id" 
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('department_id') border-red-300 @enderror"
                                required>
                            <option value="">Select Department</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>
                                    {{ $department->department_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('department_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Year Level (Only for Students) -->
                    <div id="year-level-field" style="display: none;">
                        <label for="year_level" class="block text-sm font-medium text-gray-700">Year Level</label>
                        <select name="year_level" id="year_level" 
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('year_level') border-red-300 @enderror">
                            <option value="">Select Year Level</option>
                            <option value="1" {{ old('year_level') == '1' ? 'selected' : '' }}>1st Year</option>
                            <option value="2" {{ old('year_level') == '2' ? 'selected' : '' }}>2nd Year</option>
                            <option value="3" {{ old('year_level') == '3' ? 'selected' : '' }}>3rd Year</option>
                            <option value="4" {{ old('year_level') == '4' ? 'selected' : '' }}>4th Year</option>
                            <option value="5" {{ old('year_level') == '5' ? 'selected' : '' }}>5th Year</option>
                            <option value="6" {{ old('year_level') == '6' ? 'selected' : '' }}>6th Year</option>
                        </select>
                        @error('year_level')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Submit Button -->
                    <div class="flex items-center justify-end">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                            Complete Profile
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const roleSelect = document.getElementById('role');
    const yearLevelField = document.getElementById('year-level-field');
    const yearLevelSelect = document.getElementById('year_level');

    function toggleYearLevel() {
        if (roleSelect.value === 'student') {
            yearLevelField.style.display = 'block';
            yearLevelSelect.required = true;
        } else {
            yearLevelField.style.display = 'none';
            yearLevelSelect.required = false;
            yearLevelSelect.value = '';
        }
    }

    // Initial check
    toggleYearLevel();

    // Listen for changes
    roleSelect.addEventListener('change', toggleYearLevel);
});
</script>
@endsection