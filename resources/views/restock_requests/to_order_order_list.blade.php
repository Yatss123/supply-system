@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Order List</h1>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('to-order.index') }}" class="inline-flex items-center px-4 py-2 rounded-lg border border-gray-200 bg-white text-gray-800 hover:bg-gray-50">
                <i class="fas fa-arrow-left mr-2"></i> Back to To-Order
            </a>
            <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 rounded-lg border border-gray-200 bg-white text-gray-800 hover:bg-gray-50">
                <i class="fas fa-home mr-2"></i> Dashboard
            </a>
        </div>
    </div>

    @if (session('status'))
        <div class="mb-4 p-3 rounded border border-green-200 bg-green-50 text-green-700">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="mb-4 p-4 rounded border border-red-200 bg-red-50 text-red-700">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    @if (($totalCount ?? 0) === 0)
        <div class="p-6 rounded border border-gray-200 bg-white text-gray-700">Your order list is empty.</div>
    @else
        <div class="mb-4 text-sm text-gray-700">Total items in list: <span class="font-semibold">{{ $totalCount }}</span></div>

        <div class="space-y-8">
            @foreach ($grouped as $supplierId => $data)
                @php($supplierName = $data['name'])
                @php($items = $data['items'])
                @php($slug = \Illuminate\Support\Str::slug($supplierName))
                <div>
                    <!-- Single form supports both filtering (GET) and submission (POST) -->
                    <form method="POST" action="{{ route('to-order.submit') }}" class="mb-2">
                        @csrf
                        <input type="hidden" name="supplier_id" value="{{ $supplierId }}">
                        <div class="mb-3 flex items-center justify-between">
                            <div>
                                <h2 class="text-lg font-semibold text-gray-900">Supplier: {{ $supplierName }}</h2>
                                <div class="mt-1 text-xs text-gray-600">Count: {{ $items->count() }}</div>
                                <div class="mt-2 text-xs text-gray-700">
                                    <span class="mr-2">Selection scope:</span>
                                    <label class="inline-flex items-center mr-3">
                                        <input type="radio" name="selection_scope" value="all" class="mr-1" {{ request('selection_scope') !== 'selected' ? 'checked' : '' }}>
                                        All items
                                    </label>
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="selection_scope" value="selected" class="mr-1" {{ request('selection_scope') === 'selected' ? 'checked' : '' }}>
                                        Only selected
                                    </label>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <label class="text-sm text-gray-700 inline-flex items-center">
                                    <input type="checkbox" class="select-all mr-2" data-slug="{{ $slug }}">
                                    Select all
                                </label>
                                <button type="submit" formmethod="GET" formaction="{{ route('to-order.order-list') }}" class="inline-flex items-center px-3 py-2 rounded-lg bg-gray-600 text-white hover:bg-gray-700">
                                    <i class="fas fa-filter mr-2"></i> Apply Filter
                                </button>
                                <button type="submit" class="inline-flex items-center px-3 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700">
                                    <i class="fas fa-paper-plane mr-2"></i> Send Order
                                </button>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach ($items as $item)
                                @php($supply = $item['supply'])
                                @php($checked = in_array($supply->id, (array) request()->get('selected', [])))
                                <div class="p-5 rounded-lg border border-gray-200 bg-white shadow-sm">
                                    <div class="flex items-center justify-between">
                                        <h3 class="text-base font-semibold text-gray-900">{{ $supply->name }}</h3>
                                        <span class="inline-flex items-center text-xs px-2 py-1 rounded bg-gray-100 text-gray-700">{{ $supply->unit }}</span>
                                    </div>
                                    <div class="mt-2">
                                        <label class="inline-flex items-center text-sm text-gray-700">
                                            <input type="checkbox" name="selected[]" value="{{ $supply->id }}" class="item-checkbox item-{{ $slug }} mr-2" {{ $checked ? 'checked' : '' }}>
                                            Select item
                                        </label>
                                    </div>
                                    <p class="text-gray-600 mt-2">{{ $supply->description }}</p>
                                    <div class="mt-3 grid grid-cols-3 gap-3 text-sm">
                                        <div class="p-3 rounded border border-gray-200">
                                            <div class="text-gray-500">Current Stock</div>
                                            <div class="text-gray-900 font-semibold">{{ $item['available'] }}</div>
                                        </div>
                                        <div class="p-3 rounded border border-gray-200">
                                            <div class="text-gray-500">Minimum Level</div>
                                            <div class="text-gray-900 font-semibold">{{ $item['minLevel'] }}</div>
                                        </div>
                                        <div class="p-3 rounded border border-gray-200">
                                            <div class="text-gray-500">Suggested Order</div>
                                            <div class="text-gray-900 font-semibold">{{ $item['suggested'] }}</div>
                                        </div>
                                    </div>
                                    <div class="mt-3 text-xs text-gray-500">Categories: {{ $supply->categories->pluck('name')->join(', ') ?: 'None' }}</div>
                                    <div class="mt-1 text-xs text-gray-500">Suppliers: {{ $supply->suppliers->pluck('name')->join(', ') ?: 'None' }}</div>
                                    <div class="mt-4 flex gap-2">
                                        <a href="{{ route('supplies.show', $supply) }}" class="inline-flex items-center justify-center px-3 py-2 rounded-lg border border-gray-200 bg-white text-gray-800 hover:bg-gray-50">
                                            <i class="fas fa-eye mr-2"></i> View Supply
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </form>
                </div>
            @endforeach

            @if (($unsupplied->count() ?? 0) > 0)
                <div>
                    <div class="mb-3 flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-900">No Supplier Assigned</h2>
                        <span class="text-sm text-gray-600">Count: {{ $unsupplied->count() }}</span>
                    </div>
                    <p class="text-gray-600 mb-3">These supplies don’t have suppliers yet. Assign a supplier from the supply’s page.</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach ($unsupplied as $item)
                            @php($supply = $item['supply'])
                            <div class="p-5 rounded-lg border border-gray-200 bg-white shadow-sm">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-base font-semibold text-gray-900">{{ $supply->name }}</h3>
                                    <span class="inline-flex items-center text-xs px-2 py-1 rounded bg-gray-100 text-gray-700">{{ $supply->unit }}</span>
                                </div>
                                <p class="text-gray-600 mt-1">{{ $supply->description }}</p>
                                <div class="mt-3 grid grid-cols-3 gap-3 text-sm">
                                    <div class="p-3 rounded border border-gray-200">
                                        <div class="text-gray-500">Current Stock</div>
                                        <div class="text-gray-900 font-semibold">{{ $item['available'] }}</div>
                                    </div>
                                    <div class="p-3 rounded border border-gray-200">
                                        <div class="text-gray-500">Minimum Level</div>
                                        <div class="text-gray-900 font-semibold">{{ $item['minLevel'] }}</div>
                                    </div>
                                    <div class="p-3 rounded border border-gray-200">
                                        <div class="text-gray-500">Suggested Order</div>
                                        <div class="text-gray-900 font-semibold">{{ $item['suggested'] }}</div>
                                    </div>
                                </div>
                                <div class="mt-3 text-xs text-gray-500">Categories: {{ $supply->categories->pluck('name')->join(', ') ?: 'None' }}</div>
                                <div class="mt-1 text-xs text-gray-500">Suppliers: None</div>
                                <div class="mt-4 flex gap-2">
                                    <a href="{{ route('supplies.show', $supply) }}" class="inline-flex items-center justify-center px-3 py-2 rounded-lg border border-gray-200 bg-white text-gray-800 hover:bg-gray-50">
                                        <i class="fas fa-user-plus mr-2"></i> Assign on Supply Page
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    @endif
</div>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.select-all').forEach(function (toggle) {
      toggle.addEventListener('change', function () {
        var slug = this.getAttribute('data-slug');
        var checks = document.querySelectorAll('.item-' + slug);
        checks.forEach(function (c) { c.checked = toggle.checked; });
      });
    });
  });
</script>
@endsection