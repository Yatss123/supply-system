@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Page Header -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6 bg-white border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">Profile Update Request Details</h2>
                        <p class="mt-1 text-sm text-gray-600">
                            Review and manage profile update request from {{ $profileRequest->user ? $profileRequest->user->name : 'Unknown User' }}.
                        </p>
                    </div>
                    <div class="flex items-center space-x-3">
                        @if($profileRequest->status === 'pending')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                Pending Review
                            </span>
                        @elseif($profileRequest->status === 'approved')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Approved
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                Rejected
                            </span>
                        @endif
                        <a href="{{ route('admin.profile-requests.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                            Back to List
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Success/Error Messages -->
        @if (session('success'))
            <div class="bg-green-50 border border-green-200 rounded-md p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-50 border border-red-200 rounded-md p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Current User Information -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Current User Information</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Name</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $profileRequest->user ? $profileRequest->user->name : 'Not provided' }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Email</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $profileRequest->user ? $profileRequest->user->email : 'Not provided' }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Department</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $profileRequest->user && $profileRequest->user->department ? $profileRequest->user->department->department_name : 'Not assigned' }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Address</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $profileRequest->user ? $profileRequest->user->address : 'Not provided' }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Date of Birth</label>
                            <p class="mt-1 text-sm text-gray-900">
                                {{ $profileRequest->user && $profileRequest->user->date_of_birth ? $profileRequest->user->date_of_birth->format('F j, Y') : 'Not provided' }}
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Civil Status</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $profileRequest->user && $profileRequest->user->civil_status ? ucfirst($profileRequest->user->civil_status) : 'Not provided' }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Gender</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $profileRequest->user && $profileRequest->user->gender ? ucfirst($profileRequest->user->gender) : 'Not provided' }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Contact Number</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $profileRequest->user ? $profileRequest->user->contact_number : 'Not provided' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Requested Changes -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Requested Changes</h3>
                    
                    <div class="space-y-4">
                        @if($profileRequest->requested_changes && is_array($profileRequest->requested_changes))
                            @foreach($profileRequest->requested_changes as $field => $value)
                                <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1">
                                            <h4 class="text-sm font-medium text-blue-900">
                                                {{ ucfirst(str_replace('_', ' ', $field)) }}
                                            </h4>
                                            <div class="mt-2">
                                                <p class="text-sm text-blue-800">
                                                <span class="font-medium">New Value:</span>
                                                @if($field === 'department_id')
                                                    {{ $departments->find($value)->department_name ?? 'Unknown Department' }}
                                                @elseif($field === 'role_id')
                                                    @php
                                                        $role = \App\Models\Role::find($value);
                                                    @endphp
                                                    {{ $role ? $role->display_name : 'Unknown Role' }}
                                                @elseif($field === 'year_level')
                                                    @php
                                                        $yearLevels = [
                                                            '1' => '1st Year',
                                                            '2' => '2nd Year', 
                                                            '3' => '3rd Year',
                                                            '4' => '4th Year',
                                                            '5' => '5th Year'
                                                        ];
                                                    @endphp
                                                    {{ $yearLevels[$value] ?? $value }}
                                                @elseif($field === 'date_of_birth')
                                                    {{ $value ? \Carbon\Carbon::parse($value)->format('F j, Y') : 'Not provided' }}
                                                @else
                                                    {{ is_array($value) ? implode(', ', $value) : $value }}
                                                @endif
                                            </p>
                                            <p class="text-sm text-gray-600 mt-1">
                                                <span class="font-medium">Current Value:</span>
                                                @if($field === 'department_id')
                                                    {{ $profileRequest->user && $profileRequest->user->department ? $profileRequest->user->department->department_name : 'Not assigned' }}
                                                @elseif($field === 'role_id')
                                                    {{ $profileRequest->user && $profileRequest->user->role ? $profileRequest->user->role->display_name : 'No role assigned' }}
                                                @elseif($field === 'year_level')
                                                    @php
                                                        $yearLevels = [
                                                            '1' => '1st Year',
                                                            '2' => '2nd Year', 
                                                            '3' => '3rd Year',
                                                            '4' => '4th Year',
                                                            '5' => '5th Year'
                                                        ];
                                                        $currentYearLevel = $profileRequest->user ? $profileRequest->user->year_level : null;
                                                    @endphp
                                                    {{ $currentYearLevel ? ($yearLevels[$currentYearLevel] ?? $currentYearLevel) : 'Not provided' }}
                                                @elseif($field === 'date_of_birth')
                                                    {{ $profileRequest->user && $profileRequest->user->date_of_birth ? $profileRequest->user->date_of_birth->format('F j, Y') : 'Not provided' }}
                                                @else
                                                    {{ $profileRequest->user ? ($profileRequest->user->{$field} ?? 'Not provided') : 'Not provided' }}
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                            
                            {{-- Show role assignment information when department is being changed --}}
                            @if(isset($profileRequest->requested_changes['department_id']))
                                <div class="bg-green-50 border border-green-200 rounded-md p-4">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1">
                                            <h4 class="text-sm font-medium text-green-900">
                                                <svg class="inline w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                                </svg>
                                                Automatic Role Assignment
                                            </h4>
                                            <div class="mt-2">
                                                <p class="text-sm text-green-800">
                                                    <span class="font-medium">Role will be assigned:</span>
                                                    @php
                                                        $user = $profileRequest->user;
                                                        $currentRole = $user->role->name ?? 'user';
                                                    @endphp
                                                    @if(!$user->role_id || $currentRole === 'user')
                                                        Student (automatically assigned when department is selected)
                                                    @else
                                                        {{ ucfirst(str_replace('_', ' ', $currentRole)) }} (current role will be maintained)
                                                    @endif
                                                </p>
                                                <p class="text-sm text-gray-600 mt-1">
                                                    <span class="font-medium">Current Role:</span>
                                                    {{ $user->role ? ucfirst(str_replace('_', ' ', $user->role->name)) : 'No role assigned' }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @else
                            <div class="bg-gray-50 border border-gray-200 rounded-md p-4">
                                <p class="text-sm text-gray-600">No requested changes found.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Request Information -->
        <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Request Information</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Submitted On</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $profileRequest->created_at ? $profileRequest->created_at->format('F j, Y g:i A') : 'Not available' }}</p>
                    </div>

                    @if($profileRequest->reviewed_at)
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Reviewed On</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $profileRequest->reviewed_at->format('F j, Y g:i A') }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Reviewed By</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $profileRequest->reviewer->name ?? 'Unknown' }}</p>
                        </div>
                    @endif

                    @if($profileRequest->admin_notes)
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Admin Notes</label>
                            <p class="mt-1 text-sm text-gray-900 bg-gray-50 p-3 rounded-md">{{ $profileRequest->admin_notes }}</p>
                        </div>
                    @endif
                </div>
            </div>
            </div>
        </div>

        <!-- Action Buttons -->
        @if($profileRequest->isPending())
            <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Actions</h3>
                    
                    <div class="flex space-x-4">
                        <!-- Approve Button -->
                        <form method="POST" action="{{ route('admin.profile-requests.approve', $profileRequest) }}" class="inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:border-green-900 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150" onclick="return confirm('Are you sure you want to approve this profile update request?')">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Approve Request
                            </button>
                        </form>

                        <!-- Reject Button -->
                        <button type="button" onclick="openRejectModal()" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-900 focus:outline-none focus:border-red-900 focus:ring ring-red-300 disabled:opacity-25 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            Reject Request
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Reject Modal -->
<div id="rejectModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Reject Profile Update Request</h3>
                <button type="button" onclick="closeRejectModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <form method="POST" action="{{ route('admin.profile-requests.reject', $profileRequest) }}">
                @csrf
                @method('PATCH')
                <div class="mb-4">
                    <label for="admin_notes" class="block text-sm font-medium text-gray-700 mb-2">
                        Reason for Rejection <span class="text-red-500">*</span>
                    </label>
                    <textarea name="admin_notes" id="admin_notes" rows="4" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" placeholder="Please provide a reason for rejecting this request..."></textarea>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeRejectModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                        Reject Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openRejectModal() {
    document.getElementById('rejectModal').classList.remove('hidden');
}

function closeRejectModal() {
    document.getElementById('rejectModal').classList.add('hidden');
    document.getElementById('admin_notes').value = '';
}

// Close modal when clicking outside
document.getElementById('rejectModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeRejectModal();
    }
});
</script>
@endsection