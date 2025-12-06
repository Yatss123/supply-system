@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="bg-white shadow-lg rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold text-gray-800">Departments Management</h2>
                <div class="flex items-center space-x-2">
                    <a href="{{ route('supplies.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition duration-200">
                        <i class="fas fa-boxes mr-2"></i>Supplies
                    </a>
                    @if(auth()->user()->hasAdminPrivileges())
                    <a href="{{ route('departments.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition duration-200">
                        <i class="fas fa-plus mr-2"></i>Add Department
                    </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- Search Bar -->
        <div class="px-6 py-4 border-b border-gray-200">
            <form method="GET" action="{{ route('departments.index') }}" class="flex gap-4">
                <div class="flex-1">
                    <input type="text" 
                           name="search" 
                           value="{{ $search }}" 
                           placeholder="Search departments..." 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <button type="submit" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg transition duration-200">
                    <i class="fas fa-search mr-2"></i>Search
                </button>
                @if($search)
                    <a href="{{ route('departments.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-6 py-2 rounded-lg transition duration-200">
                        <i class="fas fa-times mr-2"></i>Clear
                    </a>
                @endif
            </form>
        </div>

        <!-- Departments Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Current Head</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created At</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($departments as $department)
                        <tr class="hover:bg-gray-50 cursor-pointer" onclick="window.location.href='{{ route('departments.show', $department) }}'">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium">
                                    <a href="{{ route('departments.show', $department) }}" 
                                       class="text-blue-600 hover:text-blue-800 hover:underline transition duration-200">
                                        {{ $department->department_name }}
                                    </a>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    @if($department->dean)
                                        <a href="{{ route('users.profile', $department->dean->id) }}" 
                                           class="text-blue-600 hover:text-blue-800 hover:underline transition duration-200">
                                            {{ $department->dean->name }}
                                        </a>
                                    @else
                                        No Dean Assigned
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-500">
                                    {{ $department->created_at ? $department->created_at->format('M d, Y') : 'N/A' }}
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-4 text-center text-gray-500">
                                @if($search)
                                    No departments found matching "{{ $search }}".
                                @else
                                    @if(auth()->user()->hasAdminPrivileges())
                                        No departments found. <a href="{{ route('departments.create') }}" class="text-blue-600 hover:text-blue-800">Create the first department</a>.
                                    @else
                                        No departments found.
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($departments->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $departments->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>

@if(session('success'))
    <div class="fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50" 
         x-data="{ show: true }" 
         x-show="show" 
         x-init="setTimeout(() => show = false, 3000)">
        {{ session('success') }}
    </div>
@endif
@endsection