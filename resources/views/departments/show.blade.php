@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Top Actions: Back / Edit / Deactivate -->
    <div class="flex justify-end items-center mb-4 space-x-2">
        <button type="button"
                onclick="window.history.back()"
                class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back
        </button>

        @if(auth()->user() && method_exists(auth()->user(), 'hasAdminPrivileges') && auth()->user()->hasAdminPrivileges())
            <a href="{{ route('departments.edit', $department) }}"
               class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <i class="fas fa-edit mr-2"></i>
                Edit
            </a>
            @if($department->isActive())
                <form action="{{ route('departments.toggle-status', $department) }}" method="POST" class="inline"
                      onsubmit="return confirm('Are you sure you want to deactivate this department?')">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="inactive" />
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-yellow-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-600 focus:bg-yellow-600 active:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <i class="fas fa-ban mr-2"></i>
                        Deactivate
                    </button>
                </form>
            @else
                <form action="{{ route('departments.toggle-status', $department) }}" method="POST" class="inline"
                      onsubmit="return confirm('Are you sure you want to activate this department?')">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="active" />
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <i class="fas fa-check mr-2"></i>
                        Activate
                    </button>
                </form>
            @endif
        @endif
    </div>
    <!-- Department Header -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">
                    {{ $department->department_name }}
                    @if($department->isActive())
                        <span class="ml-3 inline-flex items-center px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                    @else
                        <span class="ml-3 inline-flex items-center px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Inactive</span>
                    @endif
                </h1>
                <p class="text-gray-600 mt-2">Department Details and Statistics</p>
            </div>
            <div class="text-right">
                <p class="text-sm text-gray-500">Dean</p>
                @if($department->dean)
                    <a href="{{ route('users.profile', $department->dean->id) }}" class="text-lg font-semibold text-blue-600 hover:text-blue-800 hover:underline">
                        {{ $department->dean->name }}
                    </a>
                @else
                    <p class="text-lg font-semibold text-gray-500">No Dean Assigned</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-blue-50 rounded-lg p-6">
            <div class="flex items-center">
                <div class="p-2 bg-blue-100 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-blue-600">Borrowed Items</p>
                    <p class="text-2xl font-bold text-blue-900">{{ $borrowedItems->total() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-green-50 rounded-lg p-6">
            <div class="flex items-center">
                <div class="p-2 bg-green-100 rounded-lg">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-green-600">Supply Requests</p>
                    <p class="text-2xl font-bold text-green-900">{{ $supplyRequests->total() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-purple-50 rounded-lg p-6">
            <div class="flex items-center">
                <div class="p-2 bg-purple-100 rounded-lg">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-purple-600">Issued Items</p>
                    <p class="text-2xl font-bold text-purple-900">{{ $issuedItems->total() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-orange-50 rounded-lg p-6">
            <div class="flex items-center">
                <div class="p-2 bg-orange-100 rounded-lg">
                    <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 0h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v2"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-orange-600">Borrow Requests</p>
                    <p class="text-2xl font-bold text-orange-900">{{ $loanRequests->total() }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs Navigation -->
    <div class="bg-white rounded-lg shadow-md">
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
                <button onclick="showTab('borrowed')" id="borrowed-tab" class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Borrowed Items
                </button>
                <button onclick="showTab('requests')" id="requests-tab" class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Supply Requests
                </button>
                <button onclick="showTab('issued')" id="issued-tab" class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Issued Items
                </button>
                <button onclick="showTab('loans')" id="loans-tab" class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Borrow Requests
                </button>
                <button onclick="showTab('users')" id="users-tab" class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Department Members
                </button>
            </nav>
        </div>

        <!-- Tab Content -->
        <div class="p-6">
            <!-- Borrowed Items Tab -->
            <div id="borrowed-content" class="tab-content">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Borrowed Items</h3>
                @if($borrowedItems->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supply</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Borrowed Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($borrowedItems as $item)
                                    <tr class="cursor-pointer hover:bg-gray-50"
                                        onclick="window.location='{{ $item->loanRequest ? route('loan-requests.show', $item->loanRequest) : route('supplies.show', $item->supply) }}'"
                                        title="View details">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $item->supply->name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $item->quantity }} {{ $item->supply->unit }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $item->borrowed_at->format('M d, Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($item->returned_at)
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                                    Returned
                                                </span>
                                            @else
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                    Borrowed
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $borrowedItems->appends(request()->query())->links() }}
                    </div>
                @else
                    <p class="text-gray-500">No borrowed items found for this department.</p>
                @endif
            </div>

            <!-- Supply Requests Tab -->
            <div id="requests-content" class="tab-content hidden">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Supply Requests</h3>
                @if($supplyRequests->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($supplyRequests as $request)
                                    <tr class="cursor-pointer hover:bg-gray-50"
                                        onclick="window.location='{{ route('supply-requests.show', $request) }}'"
                                        title="View request">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $request->item_name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $request->quantity }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $request->unit }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                                @if($request->status == 'approved') bg-green-100 text-green-800
                                                @elseif($request->status == 'pending') bg-yellow-100 text-yellow-800
                                                @else bg-red-100 text-red-800 @endif">
                                                {{ ucfirst($request->status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $request->created_at->format('M d, Y') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $supplyRequests->appends(request()->query())->links() }}
                    </div>
                @else
                    <p class="text-gray-500">No supply requests found for this department.</p>
                @endif
            </div>

            <!-- Issued Items Tab -->
            <div id="issued-content" class="tab-content hidden">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Issued Items</h3>
                @if($issuedItems->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supply</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Variant</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Issued Date</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($issuedItems as $item)
                                    <tr class="cursor-pointer hover:bg-gray-50"
                                        onclick="window.location='{{ route('issued-items.show', $item) }}'"
                                        title="View issued item">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $item->supply->name ?? 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $item->supplyVariant->variant_name ?? 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $item->quantity_issued }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $item->created_at->format('M d, Y') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $issuedItems->appends(request()->query())->links() }}
                    </div>
                @else
                    <p class="text-gray-500">No issued items found for this department.</p>
                @endif
            </div>

            <!-- Loan Requests Tab -->
            <div id="loans-content" class="tab-content hidden">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Borrow Requests</h3>
                @if($loanRequests->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supply</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requested By</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($loanRequests as $request)
                                    <tr class="cursor-pointer hover:bg-gray-50"
                                        onclick="window.location='{{ route('loan-requests.show', $request) }}'"
                                        title="View borrow request">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $request->supply->name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <a href="{{ route('users.profile', $request->requestedBy->id) }}" class="text-blue-600 hover:text-blue-800 hover:underline">
                                                {{ $request->requestedBy->name }}
                                            </a>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $request->quantity_requested }} {{ $request->supply->unit }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                                @if($request->status == 'approved') bg-green-100 text-green-800
                                                @elseif($request->status == 'pending') bg-yellow-100 text-yellow-800
                                                @elseif($request->status == 'completed') bg-blue-100 text-blue-800
                                                @else bg-red-100 text-red-800 @endif">
                                                {{ ucfirst($request->status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $request->created_at->format('M d, Y') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $loanRequests->appends(request()->query())->links() }}
                    </div>
                @else
                    <p class="text-gray-500">No borrow requests found for this department.</p>
                @endif
            </div>

            <!-- Department Members Tab -->
            <div id="users-content" class="tab-content hidden">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Department Members</h3>
                
                <!-- Deans Section -->
                @if($deans->count() > 0)
                    <div class="mb-6">
                        <h4 class="text-md font-medium text-gray-700 mb-3">Deans</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($deans as $dean)
                                <div class="bg-blue-50 rounded-lg p-4">
                                    <a href="{{ route('users.profile', $dean) }}" class="text-blue-600 hover:text-blue-800 hover:underline font-medium">
                                        {{ $dean->name }}
                                    </a>
                                    <p class="text-sm text-gray-600">{{ $dean->email }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Advisers Section -->
                @if($advisers->count() > 0)
                    <div class="mb-6">
                        <h4 class="text-md font-medium text-gray-700 mb-3">Advisers</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($advisers as $adviser)
                                <div class="bg-green-50 rounded-lg p-4">
                                    <a href="{{ route('users.profile', $adviser) }}" class="text-green-600 hover:text-green-800 hover:underline font-medium">
                                        {{ $adviser->name }}
                                    </a>
                                    <p class="text-sm text-gray-600">{{ $adviser->email }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Students Section -->
                @if($students->count() > 0)
                    <div class="mb-6">
                        <h4 class="text-md font-medium text-gray-700 mb-3">Students ({{ $students->count() }})</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($students->take(12) as $student)
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <a href="{{ route('users.profile', $student) }}" class="text-gray-700 hover:text-gray-900 hover:underline font-medium">
                                        {{ $student->name }}
                                    </a>
                                    <p class="text-sm text-gray-600">{{ $student->email }}</p>
                                </div>
                            @endforeach
                        </div>
                        @if($students->count() > 12)
                            <p class="text-sm text-gray-500 mt-3">Showing 12 of {{ $students->count() }} students.</p>
                        @endif
                    </div>
                @endif

                @if($deans->count() == 0 && $advisers->count() == 0 && $students->count() == 0)
                    <p class="text-gray-500">No members found for this department.</p>
                @endif
            </div>
        </div>
    </div>

    
</div>

<script>
function showTab(tabName) {
    // Hide all tab contents
    const contents = document.querySelectorAll('.tab-content');
    contents.forEach(content => content.classList.add('hidden'));
    
    // Remove active class from all tabs
    const tabs = document.querySelectorAll('.tab-button');
    tabs.forEach(tab => {
        tab.classList.remove('border-blue-500', 'text-blue-600');
        tab.classList.add('border-transparent', 'text-gray-500');
    });
    
    // Show selected tab content
    document.getElementById(tabName + '-content').classList.remove('hidden');
    
    // Add active class to selected tab
    const activeTab = document.getElementById(tabName + '-tab');
    activeTab.classList.remove('border-transparent', 'text-gray-500');
    activeTab.classList.add('border-blue-500', 'text-blue-600');
}

// Initialize first tab as active
document.addEventListener('DOMContentLoaded', function() {
    showTab('borrowed');
});
</script>
@endsection