@extends('layouts.app')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <div>
            <h2 class="text-xl font-semibold text-gray-800">Monthly Allocation - {{ $department->department_name }}</h2>
            <p class="text-sm text-gray-500">Month: {{ $month }}</p>
        </div>
        <div class="flex items-center space-x-3">
            <form method="GET" action="{{ route('dean.allocations.show', $department->id) }}" class="flex items-center space-x-2">
                <input type="month" name="month" value="{{ $month }}" class="form-control form-control-sm" />
                <button type="submit" class="btn btn-sm btn-outline-primary">Go</button>
            </form>
            <!-- Reminder Settings modal trigger -->
            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#reminderSettingsModal">
                <i class="fas fa-bell me-1"></i> Notify when to update stocks?
            </button>
            <!-- Update Stocks modal trigger -->
            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#updateStocksModal">
                <i class="fas fa-clipboard-check me-1"></i> Update Stocks
            </button>
            @if(auth()->user()->hasAdminPrivileges())
            <!-- Manage Items modal trigger removed -->
            @endif
            <!-- Audit History modal trigger -->
            <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#auditHistoryModal">
                <i class="fas fa-history me-1"></i> Audit History
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success mb-3">{{ session('success') }}</div>
    @endif
    @if(session('warning'))
        <div class="alert alert-warning mb-3">{{ session('warning') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger mb-3">{{ session('error') }}</div>
    @endif

    @if(auth()->user()->hasAdminPrivileges())
    <div class="d-flex justify-content-end mb-3">
        <div class="text-muted small me-3">
            <i class="fas fa-info-circle"></i>
            Select items then stage or mark as issued.
        </div>
    </div>
    @endif

    <!-- Two-column layout: left list, right audit history -->
    <div class="row g-4">
        <div class="col-lg-12">
            <div class="table-responsive">
              <table class="table table-sm table-bordered align-middle">
                <thead class="table-light">
                  <tr>
                    @if(auth()->user()->hasAdminPrivileges())
                      <th class="text-center">Select</th>
                    @endif
                    <th>Item</th>
                    @if(auth()->user()->hasAdminPrivileges())
                      <th class="text-center">Dept Available</th>
                      <th class="text-center">Inv Available</th>
                    @else
                      <th class="text-center">Issued Qty</th>
                      <th class="text-center">Dept Available</th>
                    @endif
                    <th class="text-center">Min Level</th>
                    <th class="text-center">Max Stock</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Received By</th>
                    @if(auth()->user()->hasAdminPrivileges())
                      <th class="text-center">Pickup Qty</th>
                      <th class="text-center">Actions</th>
                    @endif
                  </tr>
                </thead>
                <tbody>
                  @foreach($allocation->items as $item)
                    @php 
                      $isExcluded = !empty($item->attributes['excluded'] ?? false);
                      if ($isExcluded) { continue; }
                      $currentDeptStock = \App\Models\IssuedItem::where('department_id', $department->id)
                          ->where('supply_id', $item->supply_id)
                          ->get()
                          ->sum(function ($issued) { return (int) ($issued->available_quantity ?? 0); });
                      $overrideActual = $item->attributes['actual_available'] ?? null;
                      $displayAvailable = isset($overrideActual) ? (int)$overrideActual : (int)$currentDeptStock;
                    @endphp
                    <tr>
                      @if(auth()->user()->hasAdminPrivileges())
                        <td class="text-center">
                          <input type="checkbox" name="items[]" value="{{ $item->id }}" class="form-check-input row-check" form="issueForm" data-item-name="{{ optional($item->supply)->name ?? 'Unknown' }}" />
                        </td>
                      @endif
                      <td class="text-nowrap">{{ optional($item->supply)->name ?? '—' }}</td>
                      @if(auth()->user()->hasAdminPrivileges())
                        <td class="text-center">
                          {{ $displayAvailable }}
                          @if(isset($overrideActual))
                            <span class="badge bg-secondary ms-1">Audited</span>
                          @endif
                        </td>
                        <td class="text-center">{{ (int) (optional($item->supply)->availableQuantity() ?? 0) }}</td>
                      @else
                        <td class="text-center">{{ (int)($item->issued_qty ?? 0) }}</td>
                        <td class="text-center">
                          {{ $displayAvailable }}
                          @if(isset($overrideActual))
                            <span class="badge bg-secondary ms-1">Audited</span>
                          @endif
                        </td>
                      @endif
                      <td class="text-center">
                        @if(auth()->user()->hasAdminPrivileges())
                          <form method="POST" action="{{ route('dean.allocations.items.update-min', $item->id) }}" class="d-inline-flex align-items-center justify-content-center">
                            @csrf
                            @method('PATCH')
                            <input type="number" name="min_stock_level" value="{{ (int)$item->min_stock_level }}" min="0" class="form-control form-control-sm w-24 me-2" />
                            <button type="submit" class="btn btn-sm btn-outline-secondary">Update</button>
                          </form>
                        @else
                          {{ (int)$item->min_stock_level }}
                        @endif
                      </td>
                      <td class="text-center">
                        @if(auth()->user()->hasAdminPrivileges())
                          <form method="POST" action="{{ route('admin.allocations.items.update-max', $item->id) }}" class="d-inline-flex align-items-center justify-content-center">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="month" value="{{ $month }}" />
                            <input type="number" name="max_limit" value="{{ (int)($item->max_limit ?? 0) }}" min="0" class="form-control form-control-sm w-24 me-2" />
                            <button type="submit" class="btn btn-sm btn-outline-secondary">Update Max</button>
                          </form>
                        @else
                          {{ $item->max_limit !== null ? (int)$item->max_limit : '—' }}
                        @endif
                      </td>
                      <td class="text-center">
                        @if($item->issue_status === 'issued')
                          <span class="badge bg-success">Issued</span>
                        @elseif($item->issue_status === 'ready')
                          <span class="badge bg-info text-dark">Ready to Pick Up</span>
                          @if($item->staged_issue_qty)
                            <span class="ms-1 text-muted small">({{ (int)$item->staged_issue_qty }})</span>
                          @endif
                        @else
                          <span class="badge bg-secondary">—</span>
                        @endif
                      </td>
                      <td class="text-center">
                        @if($item->issue_status === 'issued')
                          @php
                            $monthDate = \Carbon\Carbon::parse($month . '-01');
                            $lastIssuedItem = \App\Models\IssuedItem::with('user')
                              ->where('department_id', $department->id)
                              ->where('supply_id', $item->supply_id)
                              ->whereMonth('issued_on', $monthDate->month)
                              ->whereYear('issued_on', $monthDate->year)
                              ->whereNotNull('user_id')
                              ->where('notes', 'like', 'Allocation issuance (' . $month . ')%')
                              ->orderByDesc('issued_on')
                              ->first();
                          @endphp
                          @if($lastIssuedItem && $lastIssuedItem->user)
                            <span class="text-nowrap">{{ $lastIssuedItem->user->name }}</span>
                          @else
                            <span class="text-muted">—</span>
                          @endif
                        @else
                          <span class="text-muted">—</span>
                        @endif
                      </td>
                      @if(auth()->user()->hasAdminPrivileges())
                        <td class="text-center">
                          @if($item->issue_status === 'ready' && (int)($item->staged_issue_qty ?? 0) > 0)
                            <input type="number"
                                   name="pickup_quantities[{{ $item->id }}]"
                                   min="1"
                                   max="{{ (int)$item->staged_issue_qty }}"
                                   value="{{ (int)$item->staged_issue_qty }}"
                                   class="form-control form-control-sm w-24"
                                   form="issueForm" />
                          @else
                            <span class="text-muted">—</span>
                          @endif
                        </td>
                        <td class="text-center">
                          <div class="d-inline-flex flex-wrap gap-2">
                            <form method="POST" action="{{ route('admin.allocations.items.remove', $item->id) }}" class="d-inline-flex align-items-center" onsubmit="return confirm('Remove {{ optional($item->supply)->name ?? 'this item' }} from allocation for {{ $month }}?');">
                              @csrf
                              @method('PATCH')
                              <input type="hidden" name="month" value="{{ $month }}" />
                              <button type="submit" class="btn btn-sm btn-outline-danger">Remove</button>
                            </form>
                          </div>
                        </td>
                      @endif
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
                @if(auth()->user()->hasAdminPrivileges())
                <form id="stageForm" method="POST" class="mb-2">
                    @csrf
                    <input type="hidden" name="month" value="{{ $month }}" />
                    <div class="d-flex justify-content-end gap-2">
                        <button type="submit"
                                formaction="{{ route('admin.allocations.stage-issue', $department->id) }}"
                                class="btn btn-sm btn-warning" formnovalidate>
                            <i class="fas fa-box-open me-1"></i> Ready to Pick Up
                        </button>
                    </div>
                </form>
                <form id="issueForm" method="POST">
                    @csrf
                    <input type="hidden" name="month" value="{{ $month }}" />
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#issueReviewModal">
                            <i class="fas fa-check me-1"></i> Review & Issue
                        </button>
                    </div>

                    <!-- Issue Review Modal (Admins only) -->
                    <div class="modal fade" id="issueReviewModal" tabindex="-1" aria-labelledby="issueReviewModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-scrollable">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="issueReviewModalLabel">Issue Selected Items</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <h6 class="mb-2">Selected Items</h6>
                                        <ul class="list-group" id="selectedItemsList">
                                            <!-- Populated when opening modal -->
                                        </ul>
                                        <div id="noItemsSelectedNote" class="text-muted small mt-2" style="display:none;">
                                            No items selected. Please select items before issuing.
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <label for="receiver_id" class="form-label m-0">Receiver</label>
                                        <select name="receiver_id" id="receiver_id" class="form-select form-select-sm w-auto" required>
                                            <option value="">— Select receiver —</option>
                                            @foreach(($departmentUsers ?? []) as $u)
                                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" formaction="{{ route('admin.allocations.issue-selected', $department->id) }}" class="btn btn-success" id="confirmIssueBtn">
                                        <i class="fas fa-check me-1"></i> Issue Now
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
                @endif
            </div>
        </div>
    </div>

{{-- Update Stocks Modal --}}
<div class="modal fade" id="updateStocksModal" tabindex="-1" aria-labelledby="updateStocksModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="updateStocksModalLabel">Update Department Stocks</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        @if(Auth::user()->hasAdminPrivileges())
        <div class="mb-4">
          <h6 class="mb-2">Add Consumables from Inventory</h6>
          <input type="text" id="consumableSearch" class="form-control mb-2" placeholder="Search consumables..." />
          <div id="consumableList" class="list-group" style="max-height: 240px; overflow:auto;">
            @php $initialConsumables = ($consumables ?? collect())->take(5); @endphp
            @foreach($initialConsumables as $s)
              <label class="list-group-item d-flex align-items-center">
                <input type="checkbox" class="form-check-input me-2 consumable-select" value="{{ $s->id }}" />
                <span>{{ $s->name }}</span>
                <span class="ms-auto text-muted">{{ $s->unit }}</span>
              </label>
            @endforeach
            @if(($consumables ?? collect())->isEmpty())
              <div class="text-muted">No consumables available.</div>
            @endif
          </div>
          <button id="addSelectedConsumablesBtn" class="btn btn-primary mt-2">Add to Allocations</button>
          <input type="hidden" id="allocDeptId" value="{{ $department->id }}" />
          <input type="hidden" id="allocMonth" value="{{ $month }}" />
        </div>
        @endif

        <form method="POST" action="{{ route('dean.allocations.update-actual', $department->id) }}">
          @csrf
          <input type="hidden" name="month" value="{{ $month }}" />
          <div class="table-responsive">
            <table class="table table-sm table-bordered align-middle">
              <thead class="table-light">
                <tr>
                  <th>Item</th>
                  <th>Issued Qty</th>
                  <th>Dept Available</th>
                  <th>Actual Available</th>
                </tr>
              </thead>
              <tbody>
                @foreach($allocation->items as $item)
                  @php $isExcluded = !empty($item->attributes['excluded'] ?? false); @endphp
                  @if($isExcluded)
                    @continue
                  @endif
                  <tr>
                    <td>{{ optional($item->supply)->name ?? '—' }}</td>
                    <td>{{ (int)($item->issued_qty ?? 0) }}</td>
                    <td>{{ (int)($item->dept_available ?? 0) }}</td>
                    <td>
                      <input type="number" name="actual_available[{{ $item->id }}]" class="form-control form-control-sm" min="0" placeholder="Enter count" value="{{ $item->attributes['actual_available'] ?? '' }}" />
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
          <div class="mt-3 d-flex justify-content-end">
            <button type="submit" class="btn btn-success">Save Actual Availability</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
(function(){
  const searchInput = document.getElementById('consumableSearch');
  const listContainer = document.getElementById('consumableList');
  const addBtn = document.getElementById('addSelectedConsumablesBtn');
  const deptIdEl = document.getElementById('allocDeptId');
  const monthEl = document.getElementById('allocMonth');

  if (!searchInput || !listContainer || !addBtn) return;

  const allConsumables = @json(($consumables ?? collect())->map(fn($c) => ['id'=>$c->id,'name'=>$c->name,'unit'=>$c->unit]));

  function renderList(items, limit){
    listContainer.innerHTML = '';
    if (!items.length){
      const empty = document.createElement('div');
      empty.className = 'text-muted';
      empty.textContent = 'No consumables found.';
      listContainer.appendChild(empty);
      return;
    }
    const slice = typeof limit === 'number' ? items.slice(0, limit) : items;
    slice.forEach(s => {
      const label = document.createElement('label');
      label.className = 'list-group-item d-flex align-items-center';
      label.innerHTML = `
        <input type="checkbox" class="form-check-input me-2 consumable-select" value="${s.id}" />
        <span>${s.name}</span>
        <span class=\"ms-auto text-muted\">${s.unit ?? ''}</span>
      `;
      listContainer.appendChild(label);
    });
  }

  // Do not re-render initially; server rendered first 5.

  // Dynamic filtering
  searchInput.addEventListener('input', function(){
    const q = this.value.trim().toLowerCase();
    const filtered = allConsumables.filter(s => s.name.toLowerCase().includes(q));
    if (q === '') {
      // Show initial 5 when query is empty
      renderList(allConsumables, 5);
    } else {
      // Show all matches when searching
      renderList(filtered);
    }
  });

  // Handle add to allocations
  addBtn.addEventListener('click', function(){
    const checked = Array.from(listContainer.querySelectorAll('.consumable-select:checked')).map(el => parseInt(el.value, 10));
    if (!checked.length){
      alert('Select at least one consumable to add.');
      return;
    }
    const deptId = deptIdEl.value;
    const month = monthEl.value;

    // Build a form and submit to add-multi route
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `{{ route('admin.allocations.items.add-multi', $department->id) }}`;

    const csrf = document.createElement('input');
    csrf.type = 'hidden';
    csrf.name = '_token';
    csrf.value = '{{ csrf_token() }}';
    form.appendChild(csrf);

    const monthIn = document.createElement('input');
    monthIn.type = 'hidden';
    monthIn.name = 'month';
    monthIn.value = month;
    form.appendChild(monthIn);

    checked.forEach(id => {
      const input = document.createElement('input');
      input.type = 'hidden';
      input.name = 'supply_ids[]';
      input.value = id;
      form.appendChild(input);
    });

    document.body.appendChild(form);
    form.submit();
  });
})();
</script>
@endpush

<!-- Audit History Modal -->
<div class="modal fade" id="auditHistoryModal" tabindex="-1" aria-labelledby="auditHistoryModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="auditHistoryModalLabel">Audit History</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        @php
          $allAuditEntries = [];
          foreach ($allocation->items as $aItem) {
            $entries = $aItem->attributes['actual_available_audit'] ?? [];
            if (is_array($entries)) {
              foreach ($entries as $entry) {
                $allAuditEntries[] = [
                  'item_name' => optional($aItem->supply)->name ?? 'Unknown',
                  'item_id' => $aItem->id,
                  'when' => $entry['when'] ?? null,
                  'user_name' => $entry['user_name'] ?? null,
                  'user_id' => $entry['user_id'] ?? null,
                  'value' => $entry['value'] ?? null,
                  'previous' => $entry['previous'] ?? null,
                ];
              }
            }
          }
          usort($allAuditEntries, function($a, $b) {
            $at = isset($a['when']) ? \Carbon\Carbon::parse($a['when'])->timestamp : 0;
            $bt = isset($b['when']) ? \Carbon\Carbon::parse($b['when'])->timestamp : 0;
            return $bt <=> $at; // newest first
          });
          $auditCount = count($allAuditEntries);
        @endphp

        @if($auditCount > 0)
          <div class="table-responsive">
            <table class="table table-sm mb-0 align-middle table-bordered">
              <thead>
                <tr>
                  <th>Date & Time</th>
                  <th>Item</th>
                  <th>User</th>
                  <th class="text-center">Updated To</th>
                  <th class="text-center">Previous</th>
                </tr>
              </thead>
              <tbody>
                @foreach($allAuditEntries as $entry)
                  <tr>
                    <td class="text-nowrap">{{ $entry['when'] ? \Carbon\Carbon::parse($entry['when'])->format('M d, Y h:i A') : '—' }}</td>
                    <td class="text-nowrap">{{ $entry['item_name'] }}</td>
                    <td class="text-nowrap">{{ $entry['user_name'] ?? ('User #' . ($entry['user_id'] ?? '—')) }}</td>
                    <td class="text-center">{{ isset($entry['value']) ? (int)$entry['value'] : '—' }}</td>
                    <td class="text-center">{{ isset($entry['previous']) ? (int)$entry['previous'] : '—' }}</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @else
          <div class="text-muted">No audit entries yet.</div>
        @endif
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Reminder Settings Modal -->
<div class="modal fade" id="reminderSettingsModal" tabindex="-1" aria-labelledby="reminderSettingsModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="reminderSettingsModalLabel">Weekly Stock Update Reminders</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST" action="{{ route('dean.allocations.update-reminder-day', $department->id) }}">
        @csrf
        <input type="hidden" name="month" value="{{ $month }}" />
        <div class="modal-body">
          <p class="text-muted">
            Choose a day of the week to receive a reminder to update your department's actual stock availability.
            Reminders appear in your navbar notifications and link back to this page for quick updates.
          </p>
          <div class="mb-3">
            <label for="reminder_day" class="form-label">Reminder Day</label>
            <select class="form-select" id="reminder_day" name="reminder_day">
              <option value="" {{ $department->stock_update_reminder_day ? '' : 'selected' }}>Disabled</option>
              <option value="1" {{ $department->stock_update_reminder_day === 1 ? 'selected' : '' }}>Monday</option>
              <option value="2" {{ $department->stock_update_reminder_day === 2 ? 'selected' : '' }}>Tuesday</option>
              <option value="3" {{ $department->stock_update_reminder_day === 3 ? 'selected' : '' }}>Wednesday</option>
              <option value="4" {{ $department->stock_update_reminder_day === 4 ? 'selected' : '' }}>Thursday</option>
              <option value="5" {{ $department->stock_update_reminder_day === 5 ? 'selected' : '' }}>Friday</option>
              <option value="6" {{ $department->stock_update_reminder_day === 6 ? 'selected' : '' }}>Saturday</option>
              <option value="7" {{ $department->stock_update_reminder_day === 7 ? 'selected' : '' }}>Sunday</option>
            </select>
          </div>
          <div class="alert alert-info">
            When enabled, reminders send at 9:00 AM on the selected day.
            You can change or disable this anytime.
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Save Reminder</button>
        </div>
      </form>
    </div>
</div>
</div>

@if(auth()->user()->hasAdminPrivileges())
<!-- Manage Items Modal (Admins) -->
<div class="modal fade" id="manageItemsModal" tabindex="-1" aria-labelledby="manageItemsModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="manageItemsModalLabel">Add Allocation Item</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST" action="{{ route('admin.allocations.items.add', $department->id) }}">
        @csrf
        <input type="hidden" name="month" value="{{ $month }}" />
        <div class="modal-body">
          @php
            $excludedItems = $allocation->items->filter(function($i){ return !empty($i->attributes['excluded']); });
          @endphp
          @if($excludedItems->count() > 0)
            <div class="mb-3">
              <label for="supply_id" class="form-label">Excluded Items</label>
              <select name="supply_id" id="supply_id" class="form-select">
                @foreach($excludedItems as $ex)
                  <option value="{{ $ex->supply_id }}">{{ optional($ex->supply)->name ?? '—' }}</option>
                @endforeach
              </select>
            </div>
            <div class="text-muted small">
              Select an excluded item to add it back to this month's allocation.
            </div>
          @else
            <div class="text-muted">No excluded items to add.</div>
          @endif
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
    document.getElementById('select-all')?.addEventListener('change', function(e) {
        document.querySelectorAll('.row-check').forEach(cb => { cb.checked = e.target.checked; });
    });
    function fillSelectedItems(formId) {
        const form = document.getElementById(formId);
        if (!form) return;
        // Remove previous hidden item inputs
        Array.from(form.querySelectorAll('input[name="items[]"]')).forEach(el => el.remove());
        // Append current selections
        document.querySelectorAll('.row-check:checked').forEach(cb => {
            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = 'items[]';
            hidden.value = cb.value;
            form.appendChild(hidden);
        });
    }
    document.getElementById('stageForm')?.addEventListener('submit', function() { fillSelectedItems('stageForm'); });

    // Populate selected items in Issue modal
    document.addEventListener('DOMContentLoaded', function () {
        const modalEl = document.getElementById('issueReviewModal');
        if (!modalEl) return;
        modalEl.addEventListener('show.bs.modal', function () {
            const list = document.getElementById('selectedItemsList');
            const note = document.getElementById('noItemsSelectedNote');
            const confirmBtn = document.getElementById('confirmIssueBtn');
            list.innerHTML = '';
            const selected = Array.from(document.querySelectorAll('.row-check:checked'));
            if (selected.length === 0) {
                note.style.display = 'block';
                confirmBtn.disabled = true;
            } else {
                note.style.display = 'none';
                confirmBtn.disabled = false;
                selected.forEach(cb => {
                    const itemId = cb.value;
                    const itemName = cb.dataset.itemName || ('Item #' + itemId);
                    const qtyInput = document.querySelector(`input[name="pickup_quantities[${itemId}]"]`);
                    const qtyVal = qtyInput ? qtyInput.value : null;
                    const li = document.createElement('li');
                    li.className = 'list-group-item d-flex justify-content-between align-items-center';
                    li.textContent = itemName;
                    if (qtyVal !== null) {
                        const badge = document.createElement('span');
                        badge.className = 'badge bg-primary rounded-pill';
                        badge.textContent = qtyVal;
                        li.appendChild(badge);
                    }
                    list.appendChild(li);
                });
            }
        });
    });
</script>
@endif
@endsection