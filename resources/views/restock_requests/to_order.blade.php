@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">To Order</h1>
        <p class="text-gray-600 mt-1">Review supply details and suggested order quantity.</p>
    </div>

    @if ($errors->any())
        <div class="mb-4 p-4 rounded border border-red-200 bg-red-50 text-red-700">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    @if(isset($supply))
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
        <div class="p-6">
            <div class="flex items-start justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">{{ $supply->name }}</h2>
                    <p class="text-gray-600 mt-1">{{ $supply->description }}</p>
                    <div class="mt-3 text-sm text-gray-700">
                        <div>Current Stock: <span class="font-semibold">{{ $available }}</span> {{ $supply->unit }}</div>
                        <div>Minimum Level: <span class="font-semibold">{{ $minLevel }}</span></div>
                        <div class="mt-1">Suggested Order: <span class="font-semibold">{{ $suggested }}</span></div>
                    </div>
                </div>
                <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-box text-gray-600"></i>
                </div>
            </div>
        </div>
        <div class="px-6 pb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <a href="{{ route('supplies.show', $supply) }}" class="inline-flex items-center justify-center px-4 py-2 rounded-lg border border-gray-200 bg-white text-gray-800 hover:bg-gray-50">
                    <i class="fas fa-eye mr-2"></i>
                    View Supply
                </a>
                <a href="{{ route('supplies.index', ['low_stock' => 1]) }}" class="inline-flex items-center justify-center px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700">
                    <i class="fas fa-list mr-2"></i>
                    Back to Low Stock List
                </a>
            </div>
            <p class="text-xs text-gray-500 mt-3">Ordering is finalized via Department Cart; this page provides a focused view and suggested quantity to order.</p>
        </div>
    </div>
    @else
        <div class="p-6 bg-white rounded-lg border border-gray-200 shadow-sm text-gray-700">
            Supply not found. Please navigate from the dashboard low stock list.
        </div>
    @endif
</div>
@endsection