<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class LoanRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'supply_id',
        'supply_variant_id',
        'department_id', 
        'requested_by',
        'batch_id',
        'quantity_requested',
        'purpose',
        'needed_from_date',
        'expected_return_date',
        'status',
        'approved_by',
        'approved_at',
        'approval_notes',
        'dean_approved_by',
        'dean_approved_at',
        'dean_approval_notes',
        'decline_reason',
        'borrowed_item_id'
    ];

    protected $casts = [
        'needed_from_date' => 'date',
        'expected_return_date' => 'date',
        'approved_at' => 'datetime',
        'dean_approved_at' => 'datetime'
    ];

    // Relationships
    public function supply()
    {
        return $this->belongsTo(Supply::class);
    }

    public function variant()
    {
        return $this->belongsTo(SupplyVariant::class, 'supply_variant_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function deanApprovedBy()
    {
        return $this->belongsTo(User::class, 'dean_approved_by');
    }

    public function borrowedItem()
    {
        return $this->belongsTo(BorrowedItem::class);
    }

    public function batch()
    {
        return $this->belongsTo(LoanRequestBatch::class, 'batch_id');
    }

    // Status scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeDeclined($query)
    {
        return $query->where('status', 'declined');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    // Status helper methods
    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isApproved()
    {
        return $this->status === 'approved';
    }

    public function isDeclined()
    {
        return $this->status === 'declined';
    }

    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    public function isDeanApproved()
    {
        return !is_null($this->dean_approved_by);
    }

    public function needsDeanApproval()
    {
        // If the requester is a dean OR has admin privileges, no dean approval is needed
        if ($this->requestedBy && ($this->requestedBy->isDean() || $this->requestedBy->hasAdminPrivileges())) {
            return false;
        }
        
        return $this->isPending() && is_null($this->dean_approved_by);
    }

    // Status update methods
    public function approve($approvedBy, $notes = null)
    {
        $this->update([
            'status' => 'approved',
            'approved_by' => $approvedBy,
            'approved_at' => now(),
            'approval_notes' => $notes
        ]);
    }

    public function decline($declineReason)
    {
        $this->update([
            'status' => 'declined',
            'decline_reason' => $declineReason
        ]);
    }

    public function deanApprove($deanApprovedBy, $notes = null)
    {
        $this->update([
            'dean_approved_by' => is_object($deanApprovedBy) ? $deanApprovedBy->id : $deanApprovedBy,
            'dean_approved_at' => now(),
            'dean_approval_notes' => $notes
        ]);
    }

    public function complete($borrowedItemId)
    {
        $this->update([
            'status' => 'completed',
            'borrowed_item_id' => $borrowedItemId
        ]);
    }

    // Helper methods
    public function canBeApproved()
    {
        // Allow approval if request is pending, stock is sufficient for selected variant (if any),
        // and either dean has approved OR dean approval is not required (e.g., requester is a dean)
        $deanGateSatisfied = $this->isDeanApproved() || !$this->needsDeanApproval();
        $available = $this->supply_variant_id ? ($this->variant->quantity ?? 0) : $this->supply->availableQuantity();
        return $this->isPending() && $deanGateSatisfied && $available >= $this->quantity_requested;
    }

    public function isOverdue()
    {
        return $this->isApproved() && Carbon::now()->gt($this->expected_return_date);
    }

    public function getDaysUntilReturn()
    {
        if ($this->expected_return_date) {
            return Carbon::now()->diffInDays($this->expected_return_date, false);
        }
        return null;
    }
}
