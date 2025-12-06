@extends('layouts.app')

@section('content')
<div class="bg-white rounded-xl shadow-sm p-6">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h2 class="text-xl font-semibold text-gray-800">Monthly Allocations — Admin Overview</h2>
            <p class="text-sm text-gray-500">Month: {{ $month }}</p>
        </div>
        <form method="GET" action="{{ route('admin.allocations.index') }}" class="flex items-center space-x-2">
            <input type="month" name="month" value="{{ $month }}" class="form-control form-control-sm" />
            <button type="submit" class="btn btn-sm btn-outline-primary">Go</button>
        </form>
    </div>

    @if(session('success'))
        <div class="alert alert-success mb-4">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger mb-4">{{ session('error') }}</div>
    @endif

    <div class="list-group">
        @foreach($departments as $dept)
            @php $alloc = $allocations[$dept->id] ?? null; @endphp
            <div class="list-group-item">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="fw-medium">{{ $dept->department_name }}</div>
                        <div class="text-xs text-gray-500">Dean: {{ optional($dept->dean)->name ?? 'N/A' }}</div>
                    </div>
                    <div class="text-end">
                        @if($alloc)
                            <span class="badge {{ $alloc->status === 'open' ? 'bg-success' : 'bg-secondary' }}">{{ strtoupper($alloc->status) }}</span>
                        @else
                            <span class="badge bg-warning">NOT GENERATED</span>
                        @endif
                        <div class="small text-muted mt-1">
                            Items: {{ $alloc ? $alloc->items->count() : 0 }} · Low Stock: {{ $alloc ? $alloc->items->where('low_stock', true)->count() : 0 }}
                        </div>
                        <div class="small text-muted">Updated: {{ $alloc ? optional($alloc->updated_at)->diffForHumans() : '—' }}</div>
                    </div>
                </div>
                <div class="mt-2 d-flex flex-wrap gap-2">
                    <a href="{{ route('dean.allocations.show', $dept->id) }}?month={{ $month }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-eye mr-1"></i> View
                    </a>
                    @if(auth()->user()->hasAdminPrivileges())
                        <form method="POST" action="{{ route('admin.allocations.refresh', $dept->id) }}" onsubmit="return confirm('Refresh allocation for {{ $dept->department_name }} ({{ $month }})?');">
                            @csrf
                            <input type="hidden" name="month" value="{{ $month }}" />
                            <button type="submit" class="btn btn-sm btn-primary">
                                <i class="fas fa-sync-alt mr-1"></i>
                                {{ $alloc ? 'Refresh' : 'Generate' }}
                            </button>
                        </form>
                        @if($alloc)
                            <form method="POST" action="{{ route('admin.allocations.update-status', $dept->id) }}" onsubmit="return confirm('Change status for {{ $dept->department_name }} ({{ $month }})?');" class="d-inline-flex align-items-center">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="month" value="{{ $month }}" />
                                <select name="status" class="form-select form-select-sm w-32 me-2 d-inline">
                                    <option value="open" {{ $alloc->status === 'open' ? 'selected' : '' }}>Open</option>
                                    <option value="closed" {{ $alloc->status === 'closed' ? 'selected' : '' }}>Closed</option>
                                </select>
                                <button type="submit" class="btn btn-sm btn-outline-secondary">Update</button>
                            </form>

                        @endif
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection