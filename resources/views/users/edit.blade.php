@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-2xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Edit User</h1>
            <p class="text-gray-600 mt-2">Update user information and permissions</p>
        </div>

        <!-- Form -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <form action="{{ route('users.update', $user) }}" method="POST">
                @csrf
                @method('PUT')

                <!-- Name -->
                <div class="mb-6">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        Full Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="name" 
                           name="name" 
                           value="{{ old('name', $user->name) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 @enderror"
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
                           value="{{ old('email', $user->email) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('email') border-red-500 @enderror"
                           required>
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Role -->
                @if(auth()->user()->isSuperAdmin())
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
                                <option value="{{ $role->id }}" 
                                        {{ old('role_id', $user->role_id) == $role->id ? 'selected' : '' }}>
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
                                <option value="{{ $department->id }}" {{ old('department_id', $user->department_id) == $department->id ? 'selected' : '' }}>
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
                            <option value="1st Year" {{ old('year_level', $user->year_level) == '1st Year' ? 'selected' : '' }}>1st Year</option>
                            <option value="2nd Year" {{ old('year_level', $user->year_level) == '2nd Year' ? 'selected' : '' }}>2nd Year</option>
                            <option value="3rd Year" {{ old('year_level', $user->year_level) == '3rd Year' ? 'selected' : '' }}>3rd Year</option>
                            <option value="4th Year" {{ old('year_level', $user->year_level) == '4th Year' ? 'selected' : '' }}>4th Year</option>
                        </select>
                        @error('year_level')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-gray-500">
                            Select the year level for this student.
                        </p>
                    </div>
                @else
                    <!-- Show current role for non-super admins -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Current Role</label>
                        <div class="px-3 py-2 bg-gray-100 border border-gray-300 rounded-md">
                            {{ ucfirst(str_replace('_', ' ', $user->role->name ?? 'N/A')) }}
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Only Super Admins can change user roles</p>
                    </div>

                    <!-- Show current department for non-super admins -->
                    @if($user->department)
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Current Department</label>
                            <div class="px-3 py-2 bg-gray-100 border border-gray-300 rounded-md">
                                {{ $user->department->name }}
                            </div>
                        </div>
                    @endif
                @endif

                <!-- Password Update -->
                <div class="mb-6">
                    <div class="flex items-center mb-3">
                        <input type="checkbox" 
                               id="change_password" 
                               name="change_password" 
                               value="1"
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="change_password" class="ml-2 block text-sm text-gray-700">
                            Change user password
                        </label>
                    </div>
                    
                    <div id="password-fields" style="display: none;">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                                    New Password <span class="text-red-500">*</span>
                                </label>
                                <input type="password" 
                                       id="password" 
                                       name="password" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('password') border-red-500 @enderror"
                                       placeholder="Enter new password">
                                @error('password')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                                    Confirm Password <span class="text-red-500">*</span>
                                </label>
                                <input type="password" 
                                       id="password_confirmation" 
                                       name="password_confirmation" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="Confirm new password">
                            </div>
                        </div>
                        <p class="mt-2 text-sm text-gray-500">
                            Password must be at least 8 characters long and contain uppercase, lowercase, numbers, and special characters.
                        </p>
                    </div>
                </div>

                <!-- Email Verification Status -->
                @if(auth()->user()->isSuperAdmin())
                    <div class="mb-6">
                        <div class="flex items-center">
                            <input type="checkbox" 
                                   id="email_verified" 
                                   name="email_verified" 
                                   value="1"
                                   {{ old('email_verified', $user->email_verified_at ? '1' : '0') == '1' ? 'checked' : '' }}
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="email_verified" class="ml-2 block text-sm text-gray-700">
                                Email verified
                            </label>
                        </div>
                        <p class="mt-1 text-sm text-gray-500">
                            Check this to mark the user's email as verified, or uncheck to require email verification.
                        </p>
                    </div>
                @endif

                <!-- Current Information Display -->
                <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-md">
                    <h4 class="text-sm font-medium text-blue-800 mb-2">Current Information</h4>
                    <div class="text-sm text-blue-700 space-y-1">
                        <div><strong>Account Created:</strong> {{ $user->created_at->format('M d, Y') }}</div>
                        <div><strong>Last Updated:</strong> {{ $user->updated_at->format('M d, Y H:i') }}</div>
                        <div><strong>Email Verified:</strong> 
                            @if($user->email_verified_at)
                                <span class="text-green-600">Yes ({{ $user->email_verified_at->format('M d, Y') }})</span>
                            @else
                                <span class="text-yellow-600">Pending</span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                    <a href="{{ route('users.show', $user) }}" 
                       class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="px-6 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Update User
                    </button>
                </div>
            </form>
        </div>

        <!-- Danger Zone -->
        @if(auth()->user()->isSuperAdmin() && $user->id !== auth()->id())
            <div class="mt-6 bg-white rounded-lg shadow-md p-6 border-l-4 border-red-500">
                <h3 class="text-lg font-semibold text-red-600 mb-4">Danger Zone</h3>
                <p class="text-sm text-gray-600 mb-4">
                    Permanently delete this user account. This action cannot be undone and will remove all associated data.
                </p>
                
                <form action="{{ route('users.destroy', $user) }}" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            class="px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                            onclick="return confirm('Are you sure you want to delete this user?\n\nThis will permanently remove:\n- User account and profile\n- All associated data\n- Access to the system\n\nThis action cannot be undone.')">
                        Delete User Account
                    </button>
                </form>
            </div>
        @endif

        <!-- Self-Edit Notice -->
        @if($user->id === auth()->id())
            <div class="mt-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-yellow-800">Editing Your Own Account</h3>
                        <div class="mt-2 text-sm text-yellow-700">
                            <p>You are editing your own user account. Some restrictions apply:</p>
                            <ul class="list-disc list-inside mt-1 space-y-1">
                                <li>You cannot change your own role</li>
                                <li>You cannot delete your own account</li>
                                <li>Use the profile settings for password changes</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Help Text -->
        <div class="mt-6 p-4 bg-gray-50 border border-gray-200 rounded-lg">
            <h4 class="text-sm font-medium text-gray-700 mb-2">Update Guidelines</h4>
            <ul class="text-xs text-gray-600 space-y-1">
                <li>• Email addresses must be unique across all users</li>
                <li>• Role changes take effect immediately</li>
                <li>• Password changes require confirmation</li>
                <li>• Dean role requires department assignment</li>
                <li>• Users will be notified of significant account changes</li>
                <li>• All changes are logged for security purposes</li>
            </ul>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const roleSelect = document.getElementById('role_id');
    const departmentField = document.getElementById('department-field');
    const departmentSelect = document.getElementById('department_id');
    const yearLevelField = document.getElementById('year-level-field');
    const yearLevelSelect = document.getElementById('year_level');
    const changePasswordCheckbox = document.getElementById('change_password');
    const passwordFields = document.getElementById('password-fields');
    const passwordInput = document.getElementById('password');
    const passwordConfirmInput = document.getElementById('password_confirmation');

    // Function to toggle department and year level field visibility
    function toggleRoleBasedFields() {
        if (!roleSelect || !departmentField) return;
        
        const selectedOption = roleSelect.options[roleSelect.selectedIndex];
        const roleName = selectedOption.text.toLowerCase();
        
        // Show department field for dean, adviser, and student roles
        if (roleName.includes('dean') || roleName.includes('adviser') || roleName.includes('student')) {
            departmentField.style.display = 'block';
            if (departmentSelect) {
                departmentSelect.required = true;
            }
        } else {
            departmentField.style.display = 'none';
            if (departmentSelect) {
                departmentSelect.required = false;
                departmentSelect.value = '';
            }
        }

        // Show year level field only for student role
        if (yearLevelField) {
            if (roleName.includes('student')) {
                yearLevelField.style.display = 'block';
                if (yearLevelSelect) {
                    yearLevelSelect.required = true;
                }
            } else {
                yearLevelField.style.display = 'none';
                if (yearLevelSelect) {
                    yearLevelSelect.required = false;
                    yearLevelSelect.value = '';
                }
            }
        }
    }

    // Function to toggle password fields
    function togglePasswordFields() {
        if (!changePasswordCheckbox || !passwordFields) return;
        
        if (changePasswordCheckbox.checked) {
            passwordFields.style.display = 'block';
            if (passwordInput) passwordInput.required = true;
            if (passwordConfirmInput) passwordConfirmInput.required = true;
        } else {
            passwordFields.style.display = 'none';
            if (passwordInput) {
                passwordInput.required = false;
                passwordInput.value = '';
            }
            if (passwordConfirmInput) {
                passwordConfirmInput.required = false;
                passwordConfirmInput.value = '';
            }
        }
    }

    // Event listeners
    if (roleSelect) {
        roleSelect.addEventListener('change', toggleRoleBasedFields);
        // Initialize on page load
        toggleRoleBasedFields();
    }

    if (changePasswordCheckbox) {
        changePasswordCheckbox.addEventListener('change', togglePasswordFields);
        // Initialize on page load
        togglePasswordFields();
    }

    // Form validation
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            // Check if department is required but not selected
            if (departmentField && departmentField.style.display !== 'none' && departmentSelect) {
                if (!departmentSelect.value) {
                    e.preventDefault();
                    alert('Please select a department for this role.');
                    departmentSelect.focus();
                    return false;
                }
            }

            // Check password confirmation if changing password
            if (changePasswordCheckbox && changePasswordCheckbox.checked) {
                if (passwordInput && passwordConfirmInput) {
                    if (passwordInput.value !== passwordConfirmInput.value) {
                        e.preventDefault();
                        alert('Password confirmation does not match.');
                        passwordConfirmInput.focus();
                        return false;
                    }
                }
            }
        });
    }
});
</script>

@endsection