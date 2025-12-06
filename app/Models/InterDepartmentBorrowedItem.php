<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class InterDepartmentBorrowedItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'inter_department_loan_request_id',
        'issued_item_id',
        'lending_department_id',
        'borrowing_department_id',
        'quantity_borrowed',
        'borrowed_date',
        'expected_return_date',
        'actual_return_date',
        'status',
        'condition_notes',
        'return_notes',
        'borrowed_by',
        'returned_to',
    ];

    protected $casts = [
        'borrowed_date' => 'date',
        'expected_return_date' => 'date',
        'actual_return_date' => 'date',
    ];

    // Relationships
    public function interDepartmentLoanRequest(): BelongsTo
    {
        return $this->belongsTo(InterDepartmentLoanRequest::class);
    }

    public function issuedItem(): BelongsTo
    {
        return $this->belongsTo(IssuedItem::class);
    }

    public function lendingDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'lending_department_id');
    }

    public function borrowingDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'borrowing_department_id');
    }

    public function borrowedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'borrowed_by');
    }

    public function returnedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'returned_to');
    }

    // Status helper methods
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isReturned(): bool
    {
        return $this->status === 'returned';
    }

    public function isReturnPending(): bool
    {
        return $this->status === 'return_pending';
    }

    public function isOverdue(): bool
    {
        return $this->status === 'overdue' || 
               ($this->isActive() && $this->expected_return_date < Carbon::today());
    }

    public function isLost(): bool
    {
        return $this->status === 'lost';
    }

    public function isDamaged(): bool
    {
        return $this->status === 'damaged';
    }

    // Utility methods
    public function getDaysOverdue(): int
    {
        if (!$this->isOverdue()) {
            return 0;
        }

        return Carbon::today()->diffInDays($this->expected_return_date);
    }

    public function getDaysBorrowed(): int
    {
        $endDate = $this->actual_return_date ?? Carbon::today();
        return $this->borrowed_date->diffInDays($endDate);
    }

    public function markAsReturned(User $returnedTo, string $returnNotes = null): bool
    {
        if (!$this->isActive() && !$this->isOverdue() && !$this->isReturnPending()) {
            return false;
        }

        $this->update([
            'status' => 'returned',
            'actual_return_date' => Carbon::today(),
            'returned_to' => $returnedTo->id,
            'return_notes' => $returnNotes,
        ]);

        return true;
    }

    public function initiateReturn(User $initiatedBy, ?string $returnNotes = null): bool
    {
        if (!$this->isActive() && !$this->isOverdue()) {
            return false;
        }

        $this->update([
            'status' => 'return_pending',
            'return_notes' => $returnNotes,
        ]);

        return true;
    }

    public function markAsLost(string $notes = null): bool
    {
        if ($this->isReturned()) {
            return false;
        }

        $this->update([
            'status' => 'lost',
            'return_notes' => $notes,
        ]);

        return true;
    }

    public function markAsDamaged(string $notes = null): bool
    {
        $this->update([
            'status' => 'damaged',
            'condition_notes' => $notes,
        ]);

        return true;
    }

    public function updateOverdueStatus(): void
    {
        if ($this->isActive() && $this->expected_return_date < Carbon::today()) {
            $this->update(['status' => 'overdue']);
        }
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'overdue')
                    ->orWhere(function ($q) {
                        $q->where('status', 'active')
                          ->where('expected_return_date', '<', Carbon::today());
                    });
    }

    public function scopeReturned($query)
    {
        return $query->where('status', 'returned');
    }

    public function scopeByLendingDepartment($query, $departmentId)
    {
        return $query->where('lending_department_id', $departmentId);
    }

    public function scopeByBorrowingDepartment($query, $departmentId)
    {
        return $query->where('borrowing_department_id', $departmentId);
    }
}
