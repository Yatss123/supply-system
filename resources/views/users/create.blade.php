@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-2xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Create New User</h1>
            <p class="text-gray-600 mt-2">Add a new user to the system with appropriate role permissions</p>
        </div>

        <!-- Form Card -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <form action="{{ route('users.store') }}" method="POST">
                @csrf

                <!-- Name -->
                <div class="mb-6">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        Full Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="name" 
                           name="name" 
                           value="{{ old('name') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 @enderror"
                           placeholder="Enter user's full name"
                           required>
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div class="mb-6">
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        Email Address <span class="text-red-500">*</span>
                    </label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           value="{{ old('email') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('email') border-red-500 @enderror"
                           placeholder="Enter user's email address"
                           required>
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Role -->
                <div class="mb-6">
                    <label for="role_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Role <span class="text-red-500">*</span>
                    </label>
                    <select id="role_id" 
                            name="role_id" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('role_id') border-red-500 @enderror"
                            required>
                        <option value="">Select a role</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                {{ ucfirst(str_replace('_', ' ', $role->name)) }}
                                @if($role->description)
                                    - {{ $role->description }}
                                @endif
                            </option>
                        @endforeach
                    </select>
                    @error('role_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Department (conditional) -->
                <div class="mb-6" id="department-field" style="display: none;">
                    <label for="department_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Department <span class="text-red-500" id="department-required">*</span>
                    </label>
                    <select id="department_id" 
                            name="department_id" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('department_id') border-red-500 @enderror">
                        <option value="">Select a department</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>
                                {{ $department->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('department_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-sm text-gray-500" id="department-help">
                        Select the department this user belongs to.
                    </p>
                </div>

                <!-- Year Level (Only for Students) -->
                <div class="mb-6" id="year-level-field" style="display: none;">
                    <label for="year_level" class="block text-sm font-medium text-gray-700 mb-2">
                        Year Level <span class="text-red-500">*</span>
                    </label>
                    <select id="year_level" 
                            name="year_level" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('year_level') border-red-500 @enderror">
                        <option value="">Select Year Level</option>
                        <option value="1st Year" {{ old('year_level') == '1st Year' ? 'selected' : '' }}>1st Year</option>
                        <option value="2nd Year" {{ old('year_level') == '2nd Year' ? 'selected' : '' }}>2nd Year</option>
                        <option value="3rd Year" {{ old('year_level') == '3rd Year' ? 'selected' : '' }}>3rd Year</option>
                        <option value="4th Year" {{ old('year_level') == '4th Year' ? 'selected' : '' }}>4th Year</option>
                    </select>
                    @error('year_level')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-sm text-gray-500">
                        Select the year level for this student.
                    </p>
                </div>

                <!-- Password -->
                <div class="mb-6">
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        Password <span class="text-red-500">*</span>
                    </label>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('password') border-red-500 @enderror"
                           placeholder="Enter a secure password (minimum 8 characters)"
                           required>
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-sm text-gray-500">Password must be at least 8 characters long</p>
                </div>

                <!-- Confirm Password -->
                <div class="mb-6">
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                        Confirm Password <span class="text-red-500">*</span>
                    </label>
                    <input type="password" 
                           id="password_confirmation" 
                           name="password_confirmation" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Confirm the password"
                           required>
                </div>

                <!-- Email Verification Status -->
                <div class="mb-6">
                    <div class="flex items-center">
                        <input type="checkbox" 
                               id="email_verified" 
                               name="email_verified" 
                               value="1"
                               {{ old('email_verified') ? 'checked' : '' }}
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="email_verified" class="ml-2 block text-sm text-gray-700">
                            Mark email as verified
                        </label>
                    </div>
                    <p class="mt-1 text-sm text-gray-500">
                        Check this if the user's email address has been verified or if you want to skip email verification.
                    </p>
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                    <a href="{{ route('users.index') }}" 
                       class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="px-6 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Create User
                    </button>
                </div>
            </form>
        </div>

        <!-- Role Information -->
        <div class="mt-6 bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Role Permissions</h3>
            <div class="space-y-4">
                @foreach($roles as $role)
                    <div class="border-l-4 border-blue-500 pl-4">
                        <h4 class="font-medium text-gray-900">{{ ucfirst(str_replace('_', ' ', $role->name)) }}</h4>
                        <p class="text-sm text-gray-600">{{ $role->description }}</p>
                        <div class="mt-2 text-sm text-gray-500">
                            @if($role->name === 'super_admin')
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    Full System Access
                                </span>
                            @elseif($role->name === 'admin')
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    Management Privileges
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Basic Access
                                </span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Help Text -->
        <div class="mt-6 p-4 bg-blue-50 rounded-lg">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800">User Creation Guidelines</h3>
                    <div class="mt-2 text-sm text-blue-700">
                        <ul class="list-disc list-inside space-y-1">
                            <li>Choose the appropriate role based on the user's responsibilities</li>
                            <li>Ensure the email address is unique and valid</li>
                            <li>Use a strong password with at least 8 characters</li>
                            <li>The user will receive login credentials via email (if configured)</li>
                            <li>Super Admin role should be assigned carefully as it has full system access</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const roleSelect = document.getElementById('role_id');
    const departmentField = document.getElementById('department-field');
    const departmentSelect = document.getElementById('department_id');
    const departmentRequired = document.getElementById('department-required');
    const departmentHelp = document.getElementById('department-help');
    const yearLevelField = document.getElementById('year-level-field');
    const yearLevelSelect = document.getElementById('year_level');

    // Role names that require department
    const rolesRequiringDepartment = ['dean', 'adviser', 'student'];

    function toggleRoleBasedFields() {
        const selectedRoleId = roleSelect.value;
        const selectedRoleText = roleSelect.options[roleSelect.selectedIndex]?.text?.toLowerCase() || '';
        
        // Check if the selected role requires department
        const requiresDepartment = rolesRequiringDepartment.some(role => 
            selectedRoleText.includes(role)
        );

        // Handle department field
        if (requiresDepartment && selectedRoleId) {
            departmentField.style.display = 'block';
            departmentSelect.required = true;
            departmentRequired.style.display = 'inline';
            
            // Update help text based on role
            if (selectedRoleText.includes('dean')) {
                departmentHelp.textContent = 'Select the department this Dean will manage. Note: Each department can only have one Dean.';
            } else if (selectedRoleText.includes('adviser')) {
                departmentHelp.textContent = 'Select the department this Adviser belongs to.';
            } else if (selectedRoleText.includes('student')) {
                departmentHelp.textContent = 'Select the department this Student belongs to.';
            }
        } else {
            departmentField.style.display = 'none';
            departmentSelect.required = false;
            departmentSelect.value = '';
            departmentRequired.style.display = 'none';
        }

        // Handle year level field (only for students)
        if (yearLevelField) {
            if (selectedRoleText.includes('student') && selectedRoleId) {
                yearLevelField.style.display = 'block';
                yearLevelSelect.required = true;
            } else {
                yearLevelField.style.display = 'none';
                yearLevelSelect.required = false;
                yearLevelSelect.value = '';
            }
        }
    }

    // Initialize on page load
    toggleRoleBasedFields();

    // Listen for role changes
    roleSelect.addEventListener('change', toggleRoleBasedFields);

    // Form validation
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        const selectedRoleText = roleSelect.options[roleSelect.selectedIndex]?.text?.toLowerCase() || '';
        const requiresDepartment = rolesRequiringDepartment.some(role => 
            selectedRoleText.includes(role)
        );

        if (requiresDepartment && !departmentSelect.value) {
            e.preventDefault();
            alert('Please select a department for this role.');
            departmentSelect.focus();
            return false;
        }
    });
});
</script>
@endpush