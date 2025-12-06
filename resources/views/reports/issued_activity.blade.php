@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2 class="mb-3">Issued Items Activity</h2>

    <div class="d-flex align-items-center mb-2">
        <a href="{{ route('dashboard') }}" class="btn btn-link p-0">&larr; Back to Dashboard</a>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <strong>Period:</strong>
                    <span class="badge bg-primary text-uppercase">{{ $summary['period'] }}</span>
                    <span class="ms-2">{{ $summary['start'] }} to {{ $summary['end'] }}</span>
                </div>
                <div>
                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('reports.issued-activity', ['period' => request('period', $summary['period']), 'supply_id' => request('supply_id'), 'department_id' => request('department_id'), 'export' => 'csv']) }}">Export CSV</a>
                </div>
            </div>
            <div class="mt-3 d-flex justify-content-between align-items-center flex-wrap">
                <div class="btn-group mb-3 mb-md-0" role="group">
                    <a href="{{ route('reports.issued-activity', array_merge(request()->except('period'), ['period' => 'daily'])) }}" class="btn btn-sm {{ request('period', $summary['period']) === 'daily' ? 'btn-primary' : 'btn-outline-primary' }}">Daily</a>
                    <a href="{{ route('reports.issued-activity', array_merge(request()->except('period'), ['period' => 'weekly'])) }}" class="btn btn-sm {{ request('period', $summary['period']) === 'weekly' ? 'btn-primary' : 'btn-outline-primary' }}">Weekly</a>
                    <a href="{{ route('reports.issued-activity', array_merge(request()->except('period'), ['period' => 'monthly'])) }}" class="btn btn-sm {{ request('period', $summary['period']) === 'monthly' ? 'btn-primary' : 'btn-outline-primary' }}">Monthly</a>
                </div>

                <form method="GET" action="{{ route('reports.issued-activity') }}" class="row g-2 align-items-end w-100">
                    <input type="hidden" name="period" value="{{ request('period', $summary['period']) }}">

                    <div class="col-md-5">
                        <label for="supply_search" class="form-label">Supply</label>
                        <input type="text" id="supply_search" class="form-control" placeholder="Search supply by name" autocomplete="off">
                        <input type="hidden" name="supply_id" id="supply_id" value="{{ request('supply_id') }}">
                        <div id="supply_suggestions" class="list-group position-absolute w-100" style="z-index: 1000;"></div>
                    </div>

                    <div class="col-md-5">
                        <label for="department_search" class="form-label">Department</label>
                        <input type="text" id="department_search" class="form-control" placeholder="Search department by name" autocomplete="off">
                        <input type="hidden" name="department_id" id="department_id" value="{{ request('department_id') }}">
                        <div id="department_suggestions" class="list-group position-absolute w-100" style="z-index: 1000;"></div>
                    </div>

                    <div class="col-md-2 d-flex gap-2 align-items-end">
                        <button type="submit" class="btn btn-primary">Filter</button>
                        <a href="{{ route('reports.issued-activity', ['period' => request('period', $summary['period'])]) }}" class="btn btn-link">Reset</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Issued Items ({{ number_format($summary['count']) }})</span>
        </div>
        <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>Supply</th>
                        <th>Department</th>
                        <th>Received By</th>
                        <th>Issued By</th>
                        <th class="text-end">Quantity</th>
                        <th>Issued On</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                        <tr>
                            <td>{{ $item->supply->name ?? 'N/A' }}</td>
                            <td>{{ $item->department->department_name ?? 'N/A' }}</td>
                            <td>{{ $item->user->name ?? 'N/A' }}</td>
                            <td>{{ $item->issuedBy->name ?? 'N/A' }}</td>
                            <td class="text-end">{{ $item->quantity }}</td>
                            <td>{{ optional($item->issued_on)->format('Y-m-d') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">No issued items in this period.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(method_exists($items, 'links'))
        <div class="card-footer">
            {{ $items->links() }}
        </div>
        @endif
    </div>
</div>

@php
    $suppliesJson = $supplies->map(fn($s) => ['id' => $s->id, 'name' => $s->name]);
    $departmentsJson = $departments->map(fn($d) => ['id' => $d->id, 'name' => $d->department_name]);
@endphp

<script>
    (function() {
        const supplies = @json($suppliesJson);
        const departments = @json($departmentsJson);

        const supplyInput = document.getElementById('supply_search');
        const supplyHidden = document.getElementById('supply_id');
        const supplySug = document.getElementById('supply_suggestions');

        const deptInput = document.getElementById('department_search');
        const deptHidden = document.getElementById('department_id');
        const deptSug = document.getElementById('department_suggestions');

        function renderSuggestions(container, items, onSelect) {
            container.innerHTML = '';
            if (!items.length) { container.style.display = 'none'; return; }
            items.slice(0, 8).forEach(it => {
                const a = document.createElement('a');
                a.href = '#';
                a.className = 'list-group-item list-group-item-action';
                a.textContent = it.name;
                a.addEventListener('click', (e) => {
                    e.preventDefault();
                    onSelect(it);
                    container.style.display = 'none';
                });
                container.appendChild(a);
            });
            container.style.display = 'block';
        }

        function filterItems(items, term) {
            term = (term || '').trim().toLowerCase();
            if (!term) return items;
            return items.filter(it => (it.name || '').toLowerCase().includes(term));
        }

        function attachSearch(inputEl, hiddenEl, list, data, prefillId) {
            inputEl.addEventListener('input', () => {
                // typing clears existing selected id
                hiddenEl.value = '';
                const filtered = filterItems(data, inputEl.value);
                renderSuggestions(list, filtered, (it) => {
                    inputEl.value = it.name;
                    hiddenEl.value = it.id;
                });
            });
            inputEl.addEventListener('focus', () => {
                const filtered = filterItems(data, inputEl.value);
                renderSuggestions(list, filtered, (it) => {
                    inputEl.value = it.name;
                    hiddenEl.value = it.id;
                });
            });
            document.addEventListener('click', (e) => {
                if (!list.contains(e.target) && e.target !== inputEl) {
                    list.style.display = 'none';
                }
            });
            // prefill from existing selection
            if (prefillId) {
                const found = data.find(d => String(d.id) === String(prefillId));
                if (found) {
                    inputEl.value = found.name;
                    hiddenEl.value = found.id;
                }
            }
        }

        attachSearch(supplyInput, supplyHidden, supplySug, supplies, supplyHidden.value);
        attachSearch(deptInput, deptHidden, deptSug, departments, deptHidden.value);
    })();
</script>
@endsection