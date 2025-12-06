@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Borrowing Information</h1>
                    <p class="text-gray-600 mt-1">View details of who has borrowed this item</p>
                </div>
                <div class="text-right">
                    <div class="text-sm text-gray-500">Supply</div>
                    <div class="font-semibold text-gray-900">{{ $supply->name }}</div>
                </div>
            </div>
        </div>

        <!-- Supply Information -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-box mr-2 text-blue-500"></i>
                Supply Details
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <div class="text-sm font-medium text-gray-500">Name</div>
                    <div class="text-gray-900">{{ $supply->name }}</div>
                </div>
                <div>
                    <div class="text-sm font-medium text-gray-500">Type</div>
                    <div class="text-gray-900 capitalize">{{ $supply->supply_type }}</div>
                </div>
                <div>
                    <div class="text-sm font-medium text-gray-500">Total Inventory</div>
                    <div class="text-gray-900">{{ $supply->quantity }} {{ $supply->unit }}</div>
                </div>
                <div>
                    <div class="text-sm font-medium text-gray-500">Currently Available</div>
                    <div class="text-gray-900">{{ $supply->availableQuantity() }} {{ $supply->unit }}</div>
                </div>
            </div>
        </div>

        <!-- Regular Borrowed Items -->
        @if($borrowedItems->count() > 0)
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-user-friends mr-2 text-green-500"></i>
                Regular Borrowed Items
            </h2>
            <div class="space-y-4">
                @foreach($borrowedItems as $item)
                <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>
                            <div class="text-sm font-medium text-gray-500">Borrower</div>
                            <div class="text-gray-900 font-medium">{{ $item->user->name }}</div>
                            <div class="text-sm text-gray-600">{{ $item->user->email }}</div>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-500">Department</div>
                            <div class="text-gray-900">{{ $item->department->department_name ?? 'N/A' }}</div>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-500">Quantity</div>
                            <div class="text-gray-900">{{ $item->quantity }} {{ $supply->unit }}</div>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-500">Borrowed Date</div>
                            <div class="text-gray-900">{{ $item->borrowed_at ? $item->borrowed_at->format('M d, Y') : 'N/A' }}</div>
                            @if($item->loanRequest && $item->loanRequest->expected_return_date)
                                <div class="text-sm text-gray-600">
                                    Due: {{ \Carbon\Carbon::parse($item->loanRequest->expected_return_date)->format('M d, Y') }}
                                    @if(\Carbon\Carbon::parse($item->loanRequest->expected_return_date)->isPast())
                                        <span class="text-red-600 font-medium">(Overdue)</span>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                    @if($item->loanRequest && $item->loanRequest->purpose)
                    <div class="mt-3 pt-3 border-t border-gray-200">
                        <div class="text-sm font-medium text-gray-500">Purpose</div>
                        <div class="text-gray-900 text-sm">{{ $item->loanRequest->purpose }}</div>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Inter-Department Borrowed Items -->
        @if($interDeptBorrowedItems->count() > 0)
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-exchange-alt mr-2 text-orange-500"></i>
                Inter-Department Borrowed Items
            </h2>
            <div class="space-y-4">
                @foreach($interDeptBorrowedItems as $item)
                <div class="border border-gray-200 rounded-lg p-4 bg-orange-50">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>
                            <div class="text-sm font-medium text-gray-500">Borrower</div>
                            <div class="text-gray-900 font-medium">{{ $item->borrowedBy->name }}</div>
                            <div class="text-sm text-gray-600">{{ $item->borrowedBy->email }}</div>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-500">Departments</div>
                            <div class="text-gray-900 text-sm">
                                <div><strong>From:</strong> {{ $item->lendingDepartment->department_name ?? 'N/A' }}</div>
                                <div><strong>To:</strong> {{ $item->borrowingDepartment->department_name ?? 'N/A' }}</div>
                            </div>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-500">Quantity</div>
                            <div class="text-gray-900">{{ $item->quantity_borrowed }} {{ $supply->unit }}</div>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-500">Borrowed Date</div>
                            <div class="text-gray-900">{{ $item->borrowed_date ? $item->borrowed_date->format('M d, Y') : 'N/A' }}</div>
                            @if($item->expected_return_date)
                                <div class="text-sm text-gray-600">
                                    Due: {{ $item->expected_return_date->format('M d, Y') }}
                                    @if($item->expected_return_date->isPast())
                                        <span class="text-red-600 font-medium">(Overdue)</span>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                    @if($item->interDepartmentLoanRequest && $item->interDepartmentLoanRequest->purpose)
                    <div class="mt-3 pt-3 border-t border-gray-200">
                        <div class="text-sm font-medium text-gray-500">Purpose</div>
                        <div class="text-gray-900 text-sm">{{ $item->interDepartmentLoanRequest->purpose }}</div>
                    </div>
                    @endif
                    @if($item->condition_notes)
                    <div class="mt-2">
                        <div class="text-sm font-medium text-gray-500">Condition Notes</div>
                        <div class="text-gray-900 text-sm">{{ $item->condition_notes }}</div>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- No Borrowed Items -->
        @if($borrowedItems->count() == 0 && $interDeptBorrowedItems->count() == 0)
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 text-center">
            <div class="text-gray-400 mb-4">
                <i class="fas fa-info-circle text-4xl"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No Active Borrowed Items</h3>
            <p class="text-gray-600">This supply currently has no active borrowed items.</p>
        </div>
        @endif

        <!-- Action Buttons -->
        <div class="flex justify-center space-x-4 mt-8">
            <a href="{{ route('qr.actions', $supply) }}" 
               class="bg-gray-500 hover:bg-gray-600 text-white font-medium py-2 px-6 rounded-lg transition duration-200">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to QR Actions
            </a>
            <a href="{{ route('supplies.show', $supply) }}" 
               class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-6 rounded-lg transition duration-200">
                <i class="fas fa-eye mr-2"></i>
                View Supply Details
            </a>
        </div>
    </div>
</div>
@endsection