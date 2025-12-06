@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">To Order Items</h1>
            <p class="text-gray-600 mt-1">All supplies at or below minimum stock level.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('to-order.order-list') }}" class="inline-flex items-center px-4 py-2 rounded-lg border border-gray-200 bg-white text-gray-800 hover:bg-gray-50">
                <i class="fas fa-list mr-2"></i> View Order List
            </a>
            <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 rounded-lg border border-gray-200 bg-white text-gray-800 hover:bg-gray-50">
                <i class="fas fa-home mr-2"></i> Dashboard
            </a>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('to-order.index') }}" class="row g-2 align-items-end">
                <div class="col-md-5">
                    <label for="search" class="form-label">Search supplies</label>
                    <input type="text" name="search" id="search" class="form-control" value="{{ $search ?? '' }}" placeholder="Name or description">
                </div>
                <div class="col-md-4">
                    <label for="filter" class="form-label">Filter</label>
                    <select name="filter" id="filter" class="form-select">
                        <option value="not_ordered" {{ ($filter ?? 'not_ordered') === 'not_ordered' ? 'selected' : '' }}>Not ordered</option>
                        <option value="ordered" {{ ($filter ?? 'not_ordered') === 'ordered' ? 'selected' : '' }}>Ordered</option>
                        <option value="all" {{ ($filter ?? 'not_ordered') === 'all' ? 'selected' : '' }}>All</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-2"></i> Apply
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
@forelse($toOrderItems as $item)
    @php
        $supply = $item['supply'];
        $isOrdered = in_array($supply->id, $orderedSupplyIds ?? [], true);
        $orderedBy = $orderedByMap[$supply->id] ?? null;
        $requestId = $supplyToRequestId[$supply->id] ?? null;
    @endphp
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start">
                <h5 class="card-title mb-2">
                    {{ $supply->name }}
                    @if($isOrdered)
                        <span class="badge bg-success ms-2">Ordered</span>
                    @endif
                </h5>
                <span class="text-muted">Min: {{ $item['minLevel'] }} | Avail: {{ $item['available'] }}</span>
            </div>
            <p class="card-text mb-1">Suggested Qty: <strong>{{ $item['suggested'] }}</strong></p>
            @if($orderedBy)
                <p class="card-text"><small class="text-muted">Ordered by: {{ $orderedBy }}</small></p>
            @endif
            <div class="d-flex gap-2 mt-2">
                @if($isOrdered && $requestId)
                    <a href="{{ route('restock-requests.show', $requestId) }}" class="btn btn-outline-secondary">View request</a>
                @else
                    <a href="{{ route('to-order.add', ['supply_id' => $supply->id]) }}" class="btn btn-primary">Restock</a>
                @endif
                <a href="{{ route('supplies.show', $supply->id) }}" class="btn btn-outline-primary">Details</a>
            </div>
        </div>
    </div>
@empty
    <div class="col-12">
        <div class="alert alert-info">No supplies match your filter.</div>
    </div>
@endforelse
    </div>
</div>
@endsection