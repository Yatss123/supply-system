@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">My Department</h1>
                <p class="text-gray-600 mt-2">Dean overview and department details</p>
            </div>
            @if($department)
            <div class="text-right">
                <p class="text-sm text-gray-500">Department</p>
                <p class="text-lg font-semibold text-gray-900">{{ $department->department_name }}</p>
            </div>
            @endif
        </div>
    </div>

    @isset($errorMessage)
        <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 rounded-lg p-4 mb-6">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            {{ $errorMessage }}
        </div>
    @endisset

    @if($department)
    <!-- Tabs -->
    <div class="bg-white rounded-lg shadow border border-gray-200 p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center">
                <i class="fas fa-sitemap text-purple-600 mr-3"></i>
                <h2 class="text-xl font-semibold text-gray-900">Departments</h2>
            </div>
            <span class="bg-purple-100 text-purple-800 px-3 py-1 rounded text-sm font-medium">
                {{ $department->department_name }}
            </span>
        </div>

        <div class="flex items-center justify-between mb-4" id="dept-dean-tabs">
            <div class="flex space-x-2">
                <button type="button" class="bg-purple-50 border border-purple-200 text-purple-700 px-3 py-1 rounded text-sm font-medium" data-target="#dept-members">
                    Members
                </button>
                <button type="button" class="bg-gray-50 border border-gray-200 text-gray-700 px-3 py-1 rounded text-sm font-medium" data-target="#dept-issued">
                    Issued Items
                </button>
            </div>
            <form method="GET" action="{{ route('dean.departments') }}" class="flex items-center space-x-2">
                <label for="issued_type" class="text-sm text-gray-700">Issued Type</label>
                <select name="issued_type" id="issued_type" class="border border-gray-300 rounded px-2 py-1 text-sm" onchange="this.form.submit()">
                    <option value="" {{ empty($issuedType) ? 'selected' : '' }}>All</option>
                    <option value="consumable" {{ ($issuedType ?? '') === 'consumable' ? 'selected' : '' }}>Consumables</option>
                    <option value="grantable" {{ ($issuedType ?? '') === 'grantable' ? 'selected' : '' }}>Granted items</option>
                </select>
            </form>
        </div>

        <!-- Members Tab Content -->
        <div id="dept-members" class="dept-tab-content">
            <!-- Dean -->
            <div class="mb-6">
                <h4 class="text-md font-medium text-gray-700 mb-3">Dean</h4>
                <div class="bg-blue-50 rounded-lg p-4">
                    @php $deanUser = $department->dean ?? auth()->user(); @endphp
                    <a href="{{ route('users.profile', $deanUser) }}" class="text-blue-600 hover:text-blue-800 hover:underline font-medium">
                        {{ $deanUser->name }}
                    </a>
                    <p class="text-sm text-gray-600">{{ $deanUser->email }}</p>
                </div>
            </div>

            <!-- Filters -->
            <div class="mb-6">
                <h4 class="text-md font-medium text-gray-700 mb-3">Filter Members</h4>
                <div class="bg-white border border-gray-200 rounded-lg p-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <p class="text-sm font-semibold text-gray-700 mb-2">Academic Years</p>
                            <div class="flex flex-wrap gap-2" id="year-filters">
                                @for($y = 1; $y <= 4; $y++)
                                    <label class="flex items-center space-x-2 bg-gray-50 border border-gray-200 rounded px-2 py-1">
                                        <input type="checkbox" value="{{ $y }}" class="year-filter">
                                        <span>Year {{ $y }}</span>
                                    </label>
                                @endfor
                            </div>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-700 mb-2">Search by Name</p>
                            <input type="text" id="member-search" class="w-full border border-gray-300 rounded px-3 py-2 text-sm" placeholder="Type a name...">
                            <p class="mt-1 text-xs text-gray-500">Search advisers and students by name.</p>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-700 mb-2">Selected Filters</p>
                            <div id="selected-filters" class="flex flex-wrap gap-2 min-h-[36px]"></div>
                        </div>
                    </div>
                    <div id="filter-error" class="mt-3 hidden bg-red-50 border border-red-200 text-red-700 rounded px-3 py-2 text-sm"></div>
                </div>
            </div>

            <!-- Advisers -->
            <div class="mb-6">
                <h4 class="text-md font-medium text-gray-700 mb-3">Advisers ({{ ($advisers ?? collect())->count() }})</h4>
                <div id="advisers-list">
                    @if(($advisers ?? collect())->count() > 0)
                    <ul class="divide-y divide-gray-200 rounded-lg border border-gray-200 bg-white">
                        @foreach($advisers as $adviser)
                            <li class="p-4">
                                <a href="{{ route('users.profile', $adviser) }}" class="text-green-600 hover:text-green-800 hover:underline font-medium">
                                    {{ $adviser->name }}
                                </a>
                                <p class="text-sm text-gray-600">{{ $adviser->email }}</p>
                            </li>
                        @endforeach
                    </ul>
                    @else
                        <p class="text-sm text-gray-500">No advisers assigned.</p>
                    @endif
                </div>
            </div>

            <!-- Students grouped by academic year -->
            <div class="mb-2">
                <h4 class="text-md font-medium text-gray-700 mb-3">Students by Academic Year</h4>
                <div id="students-year-list">
                    @if(($studentsByYear ?? collect())->count() > 0)
                        @foreach($studentsByYear as $year => $students)
                            @php 
                                $y = (int)($year ?? 0);
                                $suffix = 'th';
                                if ($y % 100 < 11 || $y % 100 > 13) {
                                    if ($y % 10 == 1) $suffix = 'st';
                                    elseif ($y % 10 == 2) $suffix = 'nd';
                                    elseif ($y % 10 == 3) $suffix = 'rd';
                                }
                                $yearLabel = $y > 0 ? ($y . $suffix . ' year') : 'N/A';
                            @endphp
                            <div class="mb-4">
                                <h5 class="text-sm font-semibold text-gray-700 mb-2">{{ $yearLabel }} ({{ $students->count() }})</h5>
                                <ul class="divide-y divide-gray-200 rounded-lg border border-gray-200 bg-white">
                                    @foreach($students as $student)
                                        <li class="p-4">
                                            <a href="{{ route('users.profile', $student) }}" class="text-gray-700 hover:text-gray-900 hover:underline font-medium">
                                                {{ $student->name }}
                                            </a>
                                            <p class="text-sm text-gray-600">{{ $student->email }}</p>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endforeach
                    @else
                        <p class="text-sm text-gray-500">No students found.</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Issued Items Tab Content -->
        <div id="dept-issued" class="dept-tab-content" style="display: none;">
            <div class="mb-2">
                <h4 id="issued-items" class="text-md font-medium text-gray-700 mb-3">Issued Items ({{ ($issuedItems ?? collect())->count() }})</h4>
                <!-- Search Issued Items -->
                <div class="bg-white border border-gray-200 rounded-lg p-4 mb-4">
                    <form method="GET" action="{{ route('dean.departments') }}" class="flex items-center gap-3">
                        <input type="hidden" name="issued_type" value="{{ $issuedType ?? '' }}" />
                        <div class="flex-1">
                            <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Search issued items..." class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm" />
                        </div>
                        <button type="submit" class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700 text-sm">
                            <i class="fas fa-search mr-2"></i>Search
                        </button>
                        @if(!empty($search))
                        <a href="{{ route('dean.departments', ['issued_type' => $issuedType ?? '']) }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded hover:bg-gray-50 text-sm">
                            <i class="fas fa-times mr-2"></i>Clear
                        </a>
                        @endif
                    </form>
                </div>
                @if(($issuedItems ?? collect())->count() > 0)
                <!-- Issued Items Summary -->
                @php
                    // Total issued to this department across all issued items
                    $totalIssuedQty = ($issuedItems ?? collect())->sum(function($i){ return (int) ($i->quantity ?? 0); });

                    // Regular borrowed tracked on IssuedItem.borrowed_quantity
                    $regularBorrowedQty = ($issuedItems ?? collect())->sum(function($i){ return (int) ($i->borrowed_quantity ?? 0); });

                    // Inter-dept borrowed tracked via InterDepartmentBorrowedItem with status=active
                    $issuedIds = ($issuedItems ?? collect())->pluck('id')->all();
                    $interDeptBorrowedQty = (int) \App\Models\InterDepartmentBorrowedItem::whereIn('issued_item_id', $issuedIds)
                        ->where('status', 'active')
                        ->sum('quantity_borrowed');

                    // Total currently borrowed includes both regular and inter-dept
                    $totalBorrowedQty = (int) $regularBorrowedQty + (int) $interDeptBorrowedQty;

                    // Availability is issued minus total borrowed (not IssuedItem.availableQuantity which excludes inter-dept)
                    $totalAvailableQty = max(0, (int) $totalIssuedQty - (int) $totalBorrowedQty);
                @endphp
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="text-sm font-medium text-blue-800">Total Issued to Department</div>
                        <div class="text-2xl font-bold text-blue-900">{{ $totalIssuedQty }}</div>
                    </div>
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <div class="text-sm font-medium text-yellow-800">Total Currently Borrowed</div>
                        <div class="text-2xl font-bold text-yellow-900">{{ $totalBorrowedQty }}</div>
                    </div>
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                        <div class="text-sm font-medium text-green-800">Total Currently Available</div>
                        <div class="text-2xl font-bold text-green-900">{{ $totalAvailableQty }}</div>
                    </div>
                </div>
                <ul class="divide-y divide-gray-200 rounded-lg border border-gray-200 bg-white">
                    @foreach($issuedItems as $item)
                        <li class="p-4">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <h4 class="font-semibold text-gray-900 mb-1">{{ $item->supply->name ?? 'N/A' }}</h4>
                                    @php
                                        // Compute inter-department borrowed for this issued item (active only)
                                        $interDeptBorrowedForItem = (int) \App\Models\InterDepartmentBorrowedItem::where('issued_item_id', $item->id)
                                            ->where('status', 'active')
                                            ->sum('quantity_borrowed');

                                        // Total borrowed = regular + inter-dept
                                        $regularBorrowedForItem = (int) ($item->borrowed_quantity ?? 0);
                                        $totalBorrowedForItem = $regularBorrowedForItem + $interDeptBorrowedForItem;

                                        // Availability considering inter-dept borrows
                                        $availableConsideringInterDept = max(0, (int) ($item->quantity ?? 0) - $totalBorrowedForItem);

                                        // Derive status consistent with supply-level logic
                                        if (!$item->available_for_borrowing) {
                                            $computedStatus = 'Not Available for Borrowing';
                                        } elseif ($availableConsideringInterDept <= 0) {
                                            $computedStatus = 'Fully Borrowed';
                                        } elseif ($totalBorrowedForItem > 0) {
                                            $computedStatus = 'Partially Borrowed (' . $availableConsideringInterDept . ' available)';
                                        } else {
                                            $computedStatus = 'Available for Borrowing';
                                        }
                                    @endphp
                                    <div class="text-sm text-gray-600 space-y-1">
                                        <div><strong>Quantity:</strong> {{ $item->quantity }}</div>
                                        <div><strong>Borrowed:</strong> {{ $totalBorrowedForItem }}</div>
                                        <div><strong>Status:</strong> {{ $computedStatus }}</div>
                                        @if($item->issued_on)
                                            <div><strong>Issued On:</strong> {{ $item->formatted_issued_on ?? $item->issued_on }}</div>
                                        @endif
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <a href="{{ route('dean.supplies.show', $item->supply) }}" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm">View Supply</a>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
                @else
                    <p class="text-sm text-gray-500">No items issued to this department.</p>
                @endif
            </div>
        </div>
    </div>
    @endif

    <script>
        // Utility: format academic year as ordinal (e.g., 1st, 2nd)
        function formatYear(y) {
            const n = Number(y) || 0;
            const v = n % 100;
            let suffix = 'th';
            if (v < 11 || v > 13) {
                const u = n % 10;
                if (u === 1) suffix = 'st';
                else if (u === 2) suffix = 'nd';
                else if (u === 3) suffix = 'rd';
            }
            return `${n}${suffix}`;
        }
        // Simple tab toggling
        document.querySelectorAll('#dept-dean-tabs button').forEach(btn => {
            btn.addEventListener('click', () => {
                const target = btn.getAttribute('data-target');
                document.querySelectorAll('.dept-tab-content').forEach(el => el.style.display = 'none');
                document.querySelector(target).style.display = '';
            });
        });
        // If issued type is selected via nav bar, ensure Issued Items tab is shown on load
        (function(){
            const url = new URL(window.location.href);
            if (url.searchParams.get('issued_type')) {
                document.querySelectorAll('.dept-tab-content').forEach(el => el.style.display = 'none');
                const issuedTab = document.querySelector('#dept-issued');
                if (issuedTab) issuedTab.style.display = '';
            }
        })();

        // Member filters logic
        (function(){
            const selectedFiltersEl = document.getElementById('selected-filters');
            const filterErrorEl = document.getElementById('filter-error');
            const advisersListEl = document.getElementById('advisers-list');
            const studentsYearListEl = document.getElementById('students-year-list');
            const searchInput = document.getElementById('member-search');

            function getSelectedFilters() {
                const years = Array.from(document.querySelectorAll('.year-filter:checked')).map(cb => parseInt(cb.value, 10));
                const search = (searchInput && searchInput.value ? searchInput.value.trim() : '');
                return { years, search };
            }

            function renderSelectedFilters(filters) {
                selectedFiltersEl.innerHTML = '';
                const { years, search } = filters;
                if (years.length === 0 && !search) {
                    selectedFiltersEl.innerHTML = '<span class="text-sm text-gray-500">No filters selected</span>';
                    return;
                }
                years.forEach(y => {
                    const chip = document.createElement('span');
                    chip.className = 'bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs font-medium';
                    chip.textContent = `${formatYear(y)} Year`;
                    selectedFiltersEl.appendChild(chip);
                });
                if (search) {
                    const schChip = document.createElement('span');
                    schChip.className = 'bg-gray-100 text-gray-800 px-2 py-1 rounded text-xs font-medium';
                    schChip.textContent = `Search: ${search}`;
                    selectedFiltersEl.appendChild(schChip);
                }
            }

            async function updateMembers() {
                const filters = getSelectedFilters();
                renderSelectedFilters(filters);
                filterErrorEl.classList.add('hidden');
                filterErrorEl.textContent = '';

                const params = new URLSearchParams();
                (filters.years || []).forEach(y => params.append('years[]', y));
                if (filters.search) params.append('search', filters.search);

                // Show loading states
                advisersListEl.innerHTML = '<p class="text-sm text-gray-500">Loading advisers...</p>';
                studentsYearListEl.innerHTML = '<p class="text-sm text-gray-500">Loading students...</p>';

                try {
                    const res = await fetch(`{{ route('dean.departments.members.search') }}?${params.toString()}`, {
                        headers: { 'Accept': 'application/json' }
                    });

                    if (!res.ok) {
                        const errData = await res.json().catch(() => ({}));
                        const msg = errData.message || errData.error || `Request failed with status ${res.status}`;
                        filterErrorEl.textContent = msg;
                        filterErrorEl.classList.remove('hidden');
                        throw new Error(msg);
                    }

                    const data = await res.json();

                    // Render advisers
                    if (data.advisers && data.advisers.length > 0) {
                        advisersListEl.innerHTML = '<ul class="divide-y divide-gray-200 rounded-lg border border-gray-200 bg-white"></ul>';
                        const list = advisersListEl.querySelector('ul');
                        data.advisers.forEach(a => {
                            const li = document.createElement('li');
                            li.className = 'p-4';
                            li.innerHTML = `
                                <a href="/users/${a.id}/profile" class="text-green-600 hover:text-green-800 hover:underline font-medium">${a.name}</a>
                                <p class="text-sm text-gray-600">${a.email || ''}</p>
                            `;
                            list.appendChild(li);
                        });
                    } else {
                        advisersListEl.innerHTML = '<p class="text-sm text-gray-500">No advisers assigned.</p>';
                    }

                    // Render students grouped by year
                    if (data.students) {
                        const years = Object.keys(data.students).sort((a,b) => Number(a) - Number(b));
                        if (years.length > 0) {
                            studentsYearListEl.innerHTML = '';
                            years.forEach(y => {
                                const group = document.createElement('div');
                                group.className = 'mb-4';
                                const list = data.students[y] || [];
                                const n = Number(y) || 0;
                                const label = n > 0 ? `${formatYear(n)} year` : 'N/A';
                                group.innerHTML = `
                                    <h5 class="text-sm font-semibold text-gray-700 mb-2">${label} (${list.length})</h5>
                                    <ul class="divide-y divide-gray-200 rounded-lg border border-gray-200 bg-white"></ul>
                                `;
                                const listEl = group.querySelector('ul');
                                list.forEach(s => {
                                    const li = document.createElement('li');
                                    li.className = 'p-4';
                                    li.innerHTML = `
                                        <a href="/users/${s.id}/profile" class="text-gray-700 hover:text-gray-900 hover:underline font-medium">${s.name}</a>
                                        <p class="text-sm text-gray-600">${s.email || ''}</p>
                                    `;
                                    listEl.appendChild(li);
                                });
                                studentsYearListEl.appendChild(group);
                            });
                        } else {
                            studentsYearListEl.innerHTML = '<p class="text-sm text-gray-500">No students found.</p>';
                        }
                    } else {
                        studentsYearListEl.innerHTML = '<p class="text-sm text-gray-500">No students found.</p>';
                    }

                } catch (err) {
                    filterErrorEl.textContent = err.message || 'An error occurred while searching.';
                    filterErrorEl.classList.remove('hidden');
                    // Restore previous content state minimally
                    if (!advisersListEl.innerHTML) advisersListEl.innerHTML = '<p class="text-sm text-gray-500">No advisers assigned.</p>';
                    if (!studentsYearListEl.innerHTML) studentsYearListEl.innerHTML = '<p class="text-sm text-gray-500">No students found.</p>';
                }
            }

            // Attach change listeners
            document.querySelectorAll('.year-filter').forEach(cb => cb.addEventListener('change', updateMembers));
            if (searchInput) searchInput.addEventListener('input', updateMembers);

            // Initial render of selected filters
            renderSelectedFilters(getSelectedFilters());
        })();
    </script>
</div>
@endsection