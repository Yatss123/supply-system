@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">User Details</h1>
                    <p class="text-gray-600 mt-2">User ID: {{ $user->id }}</p>
                </div>
                <div class="flex space-x-3">
                    @if(auth()->user()->role->role_name === 'Super Admin')
                        <a href="{{ route('users.edit', $user) }}" 
                           class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Edit User
                        </a>
                    @endif
                    <a href="{{ route('users.index') }}" 
                       class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Back to User Management
                    </a>
                </div>
            </div>
        </div>

        <!-- User Information -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">User Information</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Name -->
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Full Name</label>
                        <p class="text-lg font-semibold text-gray-900">{{ $user->name }}</p>
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Email Address</label>
                        <p class="text-lg font-semibold text-gray-900">{{ $user->email }}</p>
                    </div>

                    <!-- Role -->
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Role</label>
                        <div class="flex items-center space-x-2">
                            <p class="text-lg font-semibold text-gray-900">{{ ucfirst(str_replace('_', ' ', $user->role->role_name ?? 'N/A')) }}</p>
                            @if($user->role)
                                @if($user->role->role_name === 'Super Admin')
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        Full Access
                                    </span>
                                @elseif($user->role->role_name === 'Admin')
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        Management
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Basic User
                                    </span>
                                @endif
                            @endif
                        </div>
                    </div>

                    <!-- Account Status -->
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Account Status</label>
                        <div class="flex items-center space-x-2">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                Active
                            </span>
                        </div>
                    </div>

                    <!-- Created Date -->
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Account Created</label>
                        <p class="text-lg font-semibold text-gray-900">{{ $user->created_at->format('M d, Y') }}</p>
                        <p class="text-sm text-gray-500">{{ $user->created_at->diffForHumans() }}</p>
                    </div>

                    <!-- Last Updated -->
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Last Updated</label>
                        <p class="text-lg font-semibold text-gray-900">{{ $user->updated_at->format('M d, Y H:i') }}</p>
                        <p class="text-sm text-gray-500">{{ $user->updated_at->diffForHumans() }}</p>
                    </div>

                    <!-- Email Verification -->
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Email Verification</label>
                        @if($user->email_verified_at)
                            <div class="flex items-center space-x-2">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                    Verified
                                </span>
                                <p class="text-sm text-gray-500">{{ $user->email_verified_at->format('M d, Y') }}</p>
                            </div>
                        @else
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                                Pending
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Role Permissions -->
        @if($user->role)
            <div class="mt-6 bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Role Permissions</h3>
                <div class="border-l-4 border-blue-500 pl-4">
                    <h4 class="font-medium text-gray-900">{{ ucfirst(str_replace('_', ' ', $user->role->role_name)) }}</h4>
                    <p class="text-sm text-gray-600 mt-1">{{ $user->role->description ?? 'No description available' }}</p>
                    
                    <div class="mt-4">
                        <h5 class="text-sm font-medium text-gray-700 mb-2">Permissions:</h5>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                            @if($user->role->role_name === 'Super Admin')
                                <div class="flex items-center text-sm text-green-600">
                                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                    Full system access
                                </div>
                                <div class="flex items-center text-sm text-green-600">
                                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                    User management
                                </div>
                                <div class="flex items-center text-sm text-green-600">
                                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                    Supply management
                                </div>
                                <div class="flex items-center text-sm text-green-600">
                                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                    Request approval
                                </div>
                            @elseif($user->role->role_name === 'Admin')
                                <div class="flex items-center text-sm text-green-600">
                                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                    Supply management
                                </div>
                                <div class="flex items-center text-sm text-green-600">
                                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                    Request approval
                                </div>
                                <div class="flex items-center text-sm text-green-600">
                                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                    View reports
                                </div>
                                <div class="flex items-center text-sm text-red-600">
                                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                    No user management
                                </div>
                            @else
                                <div class="flex items-center text-sm text-green-600">
                                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                    View supplies
                                </div>
                                <div class="flex items-center text-sm text-green-600">
                                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                    Create requests
                                </div>
                                <div class="flex items-center text-sm text-red-600">
                                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                    No management access
                                </div>
                                <div class="flex items-center text-sm text-red-600">
                                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                    No approval rights
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Temporary Privileges -->
        @php($authUser = auth()->user())
        @php($canAssign = false)
        @if($authUser)
            @php(
                $canAssign = $authUser->hasAdminPrivileges() ? true : ($authUser->isDean() && $authUser->department_id === $user->department_id)
            )
        @endif
        @if(($authUser && ($authUser->hasAdminPrivileges() || $authUser->isDean())) && $user->id !== $authUser->id)
            <div class="mt-6 bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Temporary Admin Privileges</h3>
                @if($errors->has('authorization'))
                    <div class="p-3 mb-4 rounded-md bg-red-50 border border-red-200 text-red-700 text-sm">
                        {{ $errors->first('authorization') }}
                    </div>
                @endif
                @if($user->temp_privilege_type === 'admin' && ($user->temp_privilege_expires_at === null || now()->lessThan($user->temp_privilege_expires_at)))
                    <div class="p-4 bg-green-50 border border-green-200 rounded-md mb-4">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                            <p class="text-sm text-green-800">
                                Temporary admin privileges are active
                                @if($user->temp_privilege_expires_at)
                                    until <span class="font-semibold">{{ $user->temp_privilege_expires_at->format('M d, Y H:i') }}</span>
                                @else
                                    (indefinite)
                                @endif
                            </p>
                        </div>
                    </div>
                    @if($canAssign)
                        <form method="POST" action="{{ route('users.temporary-privileges.revoke', $user) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                Revoke Temporary Privileges
                            </button>
                        </form>
                    @else
                        <p class="text-sm text-gray-600">You cannot revoke privileges outside your department.</p>
                    @endif
                @else
                    @if($canAssign)
                        <form method="POST" action="{{ route('users.temporary-privileges.assign', $user) }}" class="space-y-4">
                            @csrf
                            <div>
                                <label for="duration" class="block text-sm font-medium text-gray-700 mb-1">Duration</label>
                                <select id="duration" name="duration" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="1_day">1 day</option>
                                    <option value="1_week">1 week</option>
                                    <option value="custom">Custom</option>
                                    <option value="indefinite">Until I change it</option>
                                </select>
                            </div>
                            <div id="custom-expiry" class="hidden">
                                <label for="expires_at" class="block text-sm font-medium text-gray-700 mb-1">Custom Expiration</label>
                                <input type="datetime-local" id="expires_at" name="expires_at" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                                @error('expires_at')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    Assign Temporary Admin Privileges
                                </button>
                            </div>
                        </form>
                        <script>
                            (function(){
                                const duration = document.getElementById('duration');
                                const customExpiry = document.getElementById('custom-expiry');
                                const expiresAt = document.getElementById('expires_at');
                                function toggleCustom(){
                                    if(duration.value === 'custom'){
                                        customExpiry.classList.remove('hidden');
                                        // default to 24 hours from now
                                        const d = new Date(Date.now() + 24*60*60*1000);
                                        const pad = n => String(n).padStart(2,'0');
                                        const local = `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
                                        expiresAt.value = local;
                                    } else {
                                        customExpiry.classList.add('hidden');
                                        expiresAt.value = '';
                                    }
                                }
                                duration.addEventListener('change', toggleCustom);
                                toggleCustom();
                            })();
                        </script>
                    @else
                        <p class="text-sm text-gray-600">Deans can assign privileges only to users within their department.</p>
                    @endif
                @endif
            </div>
        @endif

        <!-- Actions -->
        @if(auth()->user()->role->role_name === 'Super Admin' && $user->id !== auth()->id())
            <div class="mt-6 bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Actions</h3>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('users.edit', $user) }}" 
                       class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Edit User
                    </a>
                    
                    <form action="{{ route('users.destroy', $user) }}" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                            Delete User
                        </button>
                    </form>
                </div>
            </div>
        @endif

        <!-- Current User Notice -->
        @if($user->id === auth()->id())
            <div class="mt-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-yellow-800">This is your account</h3>
                        <div class="mt-2 text-sm text-yellow-700">
                            <p>You are currently viewing your own user profile. You cannot delete your own account from this interface.</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection