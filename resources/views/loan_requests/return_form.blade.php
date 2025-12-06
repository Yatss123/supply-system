@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Initiate Multi-Item Return</h4>
        <a href="{{ route('loan-requests.show', $loanRequest) }}" class="btn btn-outline-secondary">Back to Request</a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('loan-requests.return', $loanRequest) }}" enctype="multipart/form-data" id="multiItemReturnForm">
                @csrf
                @method('PATCH')

                <div class="mb-3">
                    <label for="return-notes" class="form-label fw-semibold">
                        <i class="fas fa-sticky-note me-2 text-primary"></i>Notes (Optional)
                    </label>
                    <textarea name="return_notes" id="return-notes" class="form-control" rows="3" placeholder="General notes for this return transaction..."></textarea>
                </div>

                <div class="mb-3">
                    <label for="return-photo" class="form-label fw-semibold">
                        <i class="fas fa-camera me-2 text-primary"></i>Photo (Optional)
                    </label>
                    <input type="file" name="return_photo" id="return-photo" class="form-control" accept="image/*" />
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Item</th>
                                <th class="text-center" style="width: 140px;">Borrowed Qty</th>
                                <th class="text-center" style="width: 160px;">Quantity Returned</th>
                                <th class="text-center" style="width: 180px;">Damaged/Missing?</th>
                                <th class="text-center missing-col" style="width: 150px;">Missing Count</th>
                                <th class="text-center damaged-col" style="width: 150px;">Damaged Count</th>
                                <th class="severity-col" style="width: 180px;">Damage Severity</th>
                                <th class="description-col">Damage Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($activeItems as $idx => $bi)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $bi->supply->name ?? 'N/A' }}</div>
                                        @php($lr = $bi->loanRequest ?? null)
                                        @if($lr && $lr->variant)
                                            <div class="small mt-1">
                                                <span class="badge bg-info text-dark">
                                                    {{ $lr->variant->display_name ?? $lr->variant->variant_name ?? ('Variant #'.$lr->variant->id) }}
                                                </span>
                                            </div>
                                        @elseif($bi->supply?->hasVariants())
                                            <div class="text-muted small">Variant: —</div>
                                        @else
                                            <div class="text-muted small">Variant: N/A</div>
                                        @endif
                                        <div class="text-muted small">Borrowed by: {{ $loanRequest->requestedBy->name ?? 'N/A' }}</div>
                                        <input type="hidden" name="items[{{ $idx }}][borrowed_item_id]" value="{{ $bi->id }}" />
                                    </td>
                                    <td class="text-center">{{ $bi->quantity }}</td>
                                    <td>
                                        <input type="number" class="form-control" name="items[{{ $idx }}][quantity_returned]" min="1" max="{{ $bi->quantity }}" value="{{ old('items.'.$idx.'.quantity_returned', $bi->quantity) }}" />
                                    </td>
                                    <td class="text-center">
                                        <select class="form-select condition-select" name="items[{{ $idx }}][condition_type]" data-row-index="{{ $idx }}">
                                            <option value="none" @selected(old('items.'.$idx.'.condition_type')==='none')>None</option>
                                            <option value="damaged" @selected(old('items.'.$idx.'.condition_type')==='damaged')>Damaged</option>
                                            <option value="missing" @selected(old('items.'.$idx.'.condition_type')==='missing')>Missing</option>
                                            <option value="both" @selected(old('items.'.$idx.'.condition_type')==='both')>Damaged + Missing</option>
                                        </select>
                                        <input type="hidden" class="is-damaged-hidden" name="items[{{ $idx }}][is_damaged]" value="{{ old('items.'.$idx.'.is_damaged') ? 1 : 0 }}" />
                                    </td>
                                    <td class="missing-col">
                                        <input type="number" class="form-control missing-count" name="items[{{ $idx }}][missing_count]" min="0" max="{{ $bi->quantity }}" value="{{ old('items.'.$idx.'.missing_count', 0) }}" />
                                        <div class="form-text">Missing items not returned</div>
                                    </td>
                                    <td class="damaged-col">
                                        <input type="number" class="form-control damaged-count" name="items[{{ $idx }}][damaged_count]" min="0" max="{{ $bi->quantity }}" value="{{ old('items.'.$idx.'.damaged_count', 0) }}" />
                                    </td>
                                    <td class="severity-col">
                                        <select class="form-select damage-severity-select" name="items[{{ $idx }}][damage_severity]" data-severity-for="{{ $idx }}">
                                            <option value="">Select severity (optional)</option>
                                            <option value="minor" @selected(old('items.'.$idx.'.damage_severity')==='minor')>Minor</option>
                                            <option value="moderate" @selected(old('items.'.$idx.'.damage_severity')==='moderate')>Moderate</option>
                                            <option value="severe" @selected(old('items.'.$idx.'.damage_severity')==='severe')>Severe</option>
                                            <option value="total_loss" @selected(old('items.'.$idx.'.damage_severity')==='total_loss')>Total Loss</option>
                                        </select>
                                        <div class="form-text severity-help">Shown only when damaged &gt; 0.</div>
                                    </td>
                                    <td class="description-col">
                                        <textarea class="form-control damage-description" name="items[{{ $idx }}][damage_description]" rows="2" placeholder="Describe damages for this item..."></textarea>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted">No active items to return.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-undo me-2"></i>Initiate Return
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    function parseIntSafe(val, fallback = 0) {
        const n = parseInt(val, 10);
        return isNaN(n) ? fallback : n;
    }

    function includesDamaged(mode) { return mode === 'damaged' || mode === 'both'; }
    function includesMissing(mode) { return mode === 'missing' || mode === 'both'; }

    // Toggle entire columns (header + all cells)
    function setColumnDisplay(className, show) {
        document.querySelectorAll('th.' + className + ', td.' + className).forEach(function(el) {
            el.style.display = show ? '' : 'none';
        });
    }

    // Determine if any row requires Missing or Damaged columns, then toggle globally
    function updateColumnVisibility() {
        const rows = Array.from(document.querySelectorAll('#multiItemReturnForm tbody tr'));
        let anyMissing = false;
        let anyDamaged = false;
        rows.forEach(function(row) {
            const mode = row.querySelector('.condition-select')?.value || 'none';
            if (includesMissing(mode)) anyMissing = true;
            if (includesDamaged(mode)) anyDamaged = true;
        });

        setColumnDisplay('missing-col', anyMissing);
        setColumnDisplay('damaged-col', anyDamaged);
        setColumnDisplay('severity-col', anyDamaged);
        setColumnDisplay('description-col', anyDamaged);
    }

    function clampCounts(row) {
        const borrowedCell = row.querySelector('td:nth-child(2)');
        const borrowedQty = parseIntSafe(borrowedCell?.textContent || '0');
        const returnedInput = row.querySelector('input[name*="quantity_returned"]');
        const returnedQty = parseIntSafe(returnedInput?.value || '0');
        const mode = row.querySelector('.condition-select')?.value || 'none';
        const missingInput = row.querySelector('.missing-count');
        const damagedInput = row.querySelector('.damaged-count');

        // Missing cannot exceed items not returned
        const maxMissing = Math.max(0, borrowedQty - returnedQty);
        if (missingInput) {
            missingInput.max = String(maxMissing);
            let missingVal = parseIntSafe(missingInput.value, 0);
            if (!includesMissing(mode)) missingVal = 0;
            if (missingVal > maxMissing) missingVal = maxMissing;
            missingInput.value = String(missingVal);
        }

        // Damaged cannot exceed returned minus missing
        const missingValCurrent = parseIntSafe(missingInput?.value || '0', 0);
        const maxDamaged = Math.max(0, returnedQty - missingValCurrent);
        if (damagedInput) {
            damagedInput.max = String(maxDamaged);
            let damagedVal = parseIntSafe(damagedInput.value, 0);
            if (!includesDamaged(mode)) damagedVal = 0;
            if (damagedVal > maxDamaged) damagedVal = maxDamaged;
            damagedInput.value = String(damagedVal);
        }
    }

    function updateRowState(row) {
        const mode = row.querySelector('.condition-select')?.value || 'none';
        const isDamagedHidden = row.querySelector('.is-damaged-hidden');
        const missingInput = row.querySelector('.missing-count');
        const damagedInput = row.querySelector('.damaged-count');
        const severitySelect = row.querySelector('.damage-severity-select');
        const severityContainer = severitySelect?.parentElement;
        const descriptionInput = row.querySelector('.damage-description');

        // Toggle hidden is_damaged field
        if (isDamagedHidden) isDamagedHidden.value = includesDamaged(mode) ? '1' : '0';

        // Show/hide missing and damaged inputs
        if (missingInput) {
            missingInput.closest('td').style.display = includesMissing(mode) ? '' : 'none';
            if (!includesMissing(mode)) missingInput.value = '0';
        }
        if (damagedInput) {
            damagedInput.closest('td').style.display = includesDamaged(mode) ? '' : 'none';
            if (!includesDamaged(mode)) damagedInput.value = '0';
        }

        // Severity and description shown only when damaged count > 0 and mode includes damaged
        const damagedVal = parseIntSafe(damagedInput?.value || '0', 0);
        const showSeverity = includesDamaged(mode) && damagedVal > 0;
        if (severityContainer) {
            severityContainer.style.display = showSeverity ? '' : 'none';
            if (!showSeverity) severitySelect.value = '';
        }
        if (descriptionInput) {
            descriptionInput.closest('td').style.display = includesDamaged(mode) ? '' : 'none';
            if (!includesDamaged(mode)) descriptionInput.value = '';
        }

        clampCounts(row);

        // Refresh global column visibility after row-level updates
        updateColumnVisibility();
    }

    document.querySelectorAll('#multiItemReturnForm tbody tr').forEach(function(row) {
        const modeSelect = row.querySelector('.condition-select');
        const returnedInput = row.querySelector('input[name*="quantity_returned"]');
        const missingInput = row.querySelector('.missing-count');
        const damagedInput = row.querySelector('.damaged-count');

        updateRowState(row);

        if (modeSelect) modeSelect.addEventListener('change', function() { updateRowState(row); });
        if (returnedInput) {
            returnedInput.addEventListener('input', function() { clampCounts(row); updateRowState(row); });
            returnedInput.addEventListener('change', function() { clampCounts(row); updateRowState(row); });
        }
        if (missingInput) {
            function adjustReturnedFromMissing() {
                const borrowedCell = row.querySelector('td:nth-child(2)');
                const borrowedQty = parseIntSafe(borrowedCell?.textContent || '0');
                const returnedInput = row.querySelector('input[name*="quantity_returned"]');
                const missingVal = parseIntSafe(missingInput.value || '0', 0);
                let newReturned = Math.max(0, borrowedQty - missingVal);
                // Respect minimum of 1 returned (form constraint)
                if (newReturned < 1 && borrowedQty > 0) {
                    newReturned = 1;
                    // Keep the relationship consistent: returned + missing = borrowed
                    const adjustedMissing = Math.max(0, borrowedQty - newReturned);
                    missingInput.value = String(adjustedMissing);
                }
                if (returnedInput) returnedInput.value = String(newReturned);
                clampCounts(row);
                updateRowState(row);
            }
            missingInput.addEventListener('input', adjustReturnedFromMissing);
            missingInput.addEventListener('change', adjustReturnedFromMissing);
        }
        if (damagedInput) {
            damagedInput.addEventListener('input', function() { clampCounts(row); updateRowState(row); });
            damagedInput.addEventListener('change', function() { clampCounts(row); updateRowState(row); });
        }
    });

    // Initial global column visibility pass
    updateColumnVisibility();
});
</script>
@endsection