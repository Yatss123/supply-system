@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-3">
        <div class="col-md-12 d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Ordered Activity</h4>
            <div>
                <a href="{{ route('reports.ordered-activity', ['period' => 'daily'] + request()->except('period')) }}" class="btn btn-sm {{ request('period', $summary['period']) === 'daily' ? 'btn-primary' : 'btn-outline-primary' }}">Daily</a>
                <a href="{{ route('reports.ordered-activity', ['period' => 'weekly'] + request()->except('period')) }}" class="btn btn-sm {{ request('period', $summary['period']) === 'weekly' ? 'btn-primary' : 'btn-outline-primary' }}">Weekly</a>
                <a href="{{ route('reports.ordered-activity', ['period' => 'monthly'] + request()->except('period')) }}" class="btn btn-sm {{ request('period', $summary['period']) === 'monthly' ? 'btn-primary' : 'btn-outline-primary' }}">Monthly</a>
            </div>
        </div>
    </div>

    <div class="d-flex align-items-center mb-2">
        <a href="{{ route('dashboard') }}" class="btn btn-link p-0">&larr; Back to Dashboard</a>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('reports.ordered-activity') }}" class="row g-2 align-items-end">
                <input type="hidden" name="period" value="{{ request('period', $summary['period']) }}">
                <div class="col-md-6">
                    <label for="search" class="form-label">Search</label>

                </div>
                <div class="col-md-4">
                    <label for="department_id" class="form-label">Department</label>
                    <select id="department_id" name="department_id" class="form-select">
                        <option value="">All Departments</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->department_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2 align-items-end">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('reports.ordered-activity', ['period' => request('period', $summary['period'])]) }}" class="btn btn-link">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Ordered Items ({{ number_format($summary['count']) }})</span>
        </div>
        <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>Supply</th>
                        <th>Supplier</th>
                        <th>Department</th>
                        <th class="text-end">Quantity</th>
                        <th>Ordered At</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $ord)
                        <tr>
                            <td>{{ $ord->supply->name ?? 'N/A' }}</td>
                            <td>{{ $ord->supplier->name ?? 'N/A' }}</td>
                            <td>{{ $ord->requestedDepartment->department_name ?? 'N/A' }}</td>
                            <td class="text-end">{{ $ord->quantity ?? '' }}</td>
                            <td>{{ optional($ord->updated_at)->format('Y-m-d') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">No orders found for the selected period.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
        </div>
    </div>
</div>
@endsection