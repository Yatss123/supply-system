@extends('layouts.app')

@section('content')

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Flash Messages -->
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Header Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Supply Requests Management</h3>
                            <p class="text-sm text-gray-600">Manage and track all supply requests</p>
                        </div>
                        <a href="{{ route('supply-requests.create') }}" 
                           class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Create New Request
                        </a>
                    </div>

                    <!-- Statistics -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500">Total Requests</p>
                                    <p class="text-2xl font-semibold text-gray-900">{{ $supplyRequests->count() }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-yellow-50 p-4 rounded-lg">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-yellow-500 rounded-full flex items-center justify-center">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500">Pending</p>
                                    <p class="text-2xl font-semibold text-gray-900">{{ $supplyRequests->where('status', 'pending')->count() }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-green-50 p-4 rounded-lg">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500">Approved</p>
                                    <p class="text-2xl font-semibold text-gray-900">{{ $supplyRequests->where('status', 'approved')->count() }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-red-50 p-4 rounded-lg">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-red-500 rounded-full flex items-center justify-center">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500">Declined</p>
                                    <p class="text-2xl font-semibold text-gray-900">{{ $supplyRequests->where('status', 'declined')->count() }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Supply Requests Tabs -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- Search Bar -->
                    <div class="mb-6">
                        <div class="flex flex-col sm:flex-row gap-4">
                            <div class="flex-1">
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                        </svg>
                                    </div>
                                    <input type="text" 
                                           id="search-input" 
                                           name="search" 
                                           value="{{ $search ?? '' }}"
                                           placeholder="Search by Request #, description, status, item name, or department..." 
                                           class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                </div>
                            </div>
                            <button type="button" 
                                    id="clear-search" 
                                    class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Clear
                            </button>
                        </div>
                    </div>

                    <!-- Tab Navigation -->
                    <div class="border-b border-gray-200 mb-6">
                        <nav class="-mb-px flex space-x-8">
                            <button onclick="showTab('existing')" id="existing-tab" 
                                    class="tab-button border-b-2 border-blue-500 text-blue-600 py-2 px-1 text-sm font-medium">
                                Existing Supply Requests
                                <span class="ml-2 bg-blue-100 text-blue-600 py-1 px-2 rounded-full text-xs" id="existing-count">
                                    {{ $existingRequests->total() }}
                                </span>
                            </button>
                            <button onclick="showTab('new')" id="new-tab" 
                                    class="tab-button border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 py-2 px-1 text-sm font-medium">
                                New Item Requests
                                <span class="ml-2 bg-gray-100 text-gray-600 py-1 px-2 rounded-full text-xs" id="new-count">
                                    {{ $newBatches->total() }}
                                </span>
                            </button>
                        </nav>
                    </div>

                    <!-- Existing Supply Requests Tab -->
                    <div id="existing-content" class="tab-content">
                        @if($existingRequests->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Request
                                        </th>
                                        
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Department
                                        </th>
                                        @if(auth()->user()->isDean())
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Requester
                                        </th>
                                        @endif
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Requested Date
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($existingRequests as $request)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($request->batch_id)
                                                <a href="{{ route('supply-request-batches.show', $request->batch_id) }}" class="text-blue-600 hover:text-blue-900">Request #{{ $request->batch_id }}</a>
                                            @else
                                                <span class="text-gray-500">—</span>
                                            @endif
                                        </td>
                                        
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                @if($request->department)
                                                    <a href="{{ route('departments.show', $request->department) }}" class="text-blue-600 hover:text-blue-900 cursor-pointer">{{ $request->department->department_name }}</a>
                                                @else
                                                    <span class="text-gray-500">N/A</span>
                                                @endif
                                            </div>
                                        </td>
                                        @if(auth()->user()->isDean())
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                @if($request->user)
                                                    @can('view', $request->user)
                                                        <a href="{{ route('users.profile', $request->user) }}" class="text-blue-600 hover:text-blue-900 cursor-pointer">{{ $request->user->name }}</a>
                                                    @else
                                                        <span>{{ $request->user->name }}</span>
                                                    @endcan
                                                    <div class="text-xs text-gray-500">{{ $request->user->email }}</div>
                                                @else
                                                    <span class="text-gray-500">N/A</span>
                                                @endif
                                            </div>
                                        </td>
                                        @endif
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @switch($request->status)
                                                @case('pending')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                        Pending
                                                    </span>
                                                    @break
                                                @case('approved')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                        Approved
                                                    </span>
                                                    @break
                                                @case('declined')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                        Declined
                                                    </span>
                                                    @break
                                                @default
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                        {{ ucfirst($request->status) }}
                                                    </span>
                                            @endswitch
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ $request->created_at->format('M d, Y') }}</div>
                                            <div class="text-sm text-gray-500">{{ $request->created_at->format('h:i A') }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex space-x-2">
                                                <a href="{{ route('supply-requests.show', $request) }}" 
                                                   class="text-blue-600 hover:text-blue-900">View</a>
                                                
                                                @can('delete', $request)
                                                @if(!(auth()->user()->hasRole('dean') || auth()->user()->hasRole('adviser')))
                                                <form action="{{ route('supply-requests.destroy', $request) }}" 
                                                      method="POST" 
                                                      class="inline"
                                                      onsubmit="return confirm('Are you sure you want to delete this request?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                            class="text-red-600 hover:text-red-900">Delete</button>
                                                </form>
                                                @endif
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination for Existing Requests -->
                        @if($existingRequests->hasPages())
                        <div class="mt-6">
                            {{ $existingRequests->appends(['search' => $search])->links() }}
                        </div>
                        @endif
                        
                        @else
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No existing supply requests found</h3>
                            <p class="mt-1 text-sm text-gray-500">Requests for existing supplies will appear here.</p>
                        </div>
                        @endif
                    </div>

                    <!-- New Item Requests Tab -->
                    <div id="new-content" class="tab-content hidden">
                        @if($newBatches->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Request
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Items
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Department
                                        </th>
                                        @if(auth()->user()->isDean())
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Requester
                                        </th>
                                        @endif
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Requested Date
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($newBatches as $batch)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                <a href="{{ route('supply-request-batches.show', $batch) }}" class="text-blue-600 hover:text-blue-900 cursor-pointer">Request #{{ $batch->id }}</a>
                                            </div>
                                            @if($batch->description)
                                                <div class="text-xs text-gray-500">{{ $batch->description }}</div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ $batch->items()->count() }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                @if($batch->department)
                                                    <a href="{{ route('departments.show', $batch->department) }}" class="text-blue-600 hover:text-blue-900 cursor-pointer">{{ $batch->department->department_name }}</a>
                                                @else
                                                    <span class="text-gray-500">N/A</span>
                                                @endif
                                            </div>
                                        </td>
                                        @if(auth()->user()->isDean())
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                @if($batch->user)
                                                    @can('view', $batch->user)
                                                        <a href="{{ route('users.profile', $batch->user) }}" class="text-blue-600 hover:text-blue-900 cursor-pointer">{{ $batch->user->name }}</a>
                                                    @else
                                                        <span>{{ $batch->user->name }}</span>
                                                    @endcan
                                                    <div class="text-xs text-gray-500">{{ $batch->user->email }}</div>
                                                @else
                                                    <span class="text-gray-500">N/A</span>
                                                @endif
                                            </div>
                                        </td>
                                        @endif
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @switch($batch->status)
                                                @case('pending')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                        Pending
                                                    </span>
                                                    @break
                                                @case('approved')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                        Approved
                                                    </span>
                                                    @break
                                                @case('declined')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                        Declined
                                                    </span>
                                                    @break
                                                @default
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                        {{ ucfirst($batch->status) }}
                                                    </span>
                                            @endswitch
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ $batch->created_at->format('M d, Y') }}</div>
                                            <div class="text-sm text-gray-500">{{ $batch->created_at->format('h:i A') }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex space-x-2">
                                                <a href="{{ route('supply-request-batches.show', $batch) }}" 
                                                   class="text-blue-600 hover:text-blue-900">View</a>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination for New Requests -->
                        @if($newBatches->hasPages())
                        <div class="mt-6">
                            {{ $newBatches->appends(['search' => $search])->links() }}
                        </div>
                        @endif
                        
                        @else
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No new item requests found</h3>
                            <p class="mt-1 text-sm text-gray-500">Requests for new items will appear here.</p>
                            <div class="mt-6">
                                <a href="{{ route('supply-requests.create') }}" 
                                   class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                    Create Request
                                </a>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- JavaScript for Tab Functionality and Search -->
            <script>
                let searchTimeout;
                
                // Search functionality
                document.getElementById('search-input').addEventListener('input', function() {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(() => {
                        performSearch();
                    }, 300); // Debounce search by 300ms
                });
                
                document.getElementById('clear-search').addEventListener('click', function() {
                    document.getElementById('search-input').value = '';
                    performSearch();
                });
                
                function performSearch() {
                    const searchValue = document.getElementById('search-input').value;
                    const url = new URL(window.location.href);
                    
                    if (searchValue) {
                        url.searchParams.set('search', searchValue);
                    } else {
                        url.searchParams.delete('search');
                    }
                    
                    // Remove pagination parameters to start from page 1
                    url.searchParams.delete('existing_page');
                    url.searchParams.delete('new_page');
                    
                    window.location.href = url.toString();
                }
                
                function showTab(tabName) {
                    // Hide all tab contents
                    document.querySelectorAll('.tab-content').forEach(content => {
                        content.classList.add('hidden');
                    });
                    
                    // Remove active styles from all tabs
                    document.querySelectorAll('.tab-button').forEach(button => {
                        button.classList.remove('border-blue-500', 'text-blue-600');
                        button.classList.add('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
                    });
                    
                    // Show selected tab content
                    document.getElementById(tabName + '-content').classList.remove('hidden');
                    
                    // Add active styles to selected tab
                    const activeTab = document.getElementById(tabName + '-tab');
                    activeTab.classList.remove('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
                    activeTab.classList.add('border-blue-500', 'text-blue-600');
                }
                
                // Initialize the default tab on page load
                document.addEventListener('DOMContentLoaded', function() {
                    showTab('existing');
                });
            </script>
        </div>
    </div>
@endsection