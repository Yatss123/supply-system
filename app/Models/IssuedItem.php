<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class IssuedItem extends Model
{   
    use HasFactory;

    protected $fillable = [
        'batch_id',
        'supply_id', 
        'department_id', 
        'supply_variant_id',
        'user_id',
        'quantity', 
        'issued_on',
        'notes',
        'issued_by',
        'available_for_borrowing',
        'borrowed_quantity'
    ];

    protected $casts = [
        'issued_on' => 'date',
        'quantity' => 'integer',
        'available_for_borrowing' => 'boolean',
        'borrowed_quantity' => 'integer'
    ];

    protected $dates = [
        'issued_on'
    ];

    // Relationships
    public function supply()
    {
        return $this->belongsTo(Supply::class, 'supply_id');
    }   

    public function supplyVariant()
    {
        return $this->belongsTo(SupplyVariant::class, 'supply_variant_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function batch()
    {
        return $this->belongsTo(IssuedItemBatch::class, 'batch_id');
    }

    public function issuedBy()
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Inter-departmental borrowing relationships
    public function interDepartmentLoanRequests()
    {
        return $this->hasMany(InterDepartmentLoanRequest::class, 'issued_item_id');
    }

    public function interDepartmentBorrowedItems()
    {
        return $this->hasMany(InterDepartmentBorrowedItem::class, 'issued_item_id');
    }

    // Accessors & Mutators
    public function getAvailableQuantityAttribute()
    {
        // Include inter-department borrowed quantities (active and overdue)
        $interDeptBorrowed = (int) $this->interDepartmentBorrowedItems()
            ->whereIn('status', ['active', 'overdue'])
            ->sum('quantity_borrowed');

        $regularBorrowed = (int) ($this->borrowed_quantity ?? 0);
        $baseQuantity = (int) ($this->quantity ?? 0);

        $available = $baseQuantity - $regularBorrowed - $interDeptBorrowed;
        return max(0, $available);
    }

    public function getIsBorrowableAttribute()
    {
        return $this->available_for_borrowing && $this->getAvailableQuantityAttribute() > 0;
    }

    public function getBorrowingStatusAttribute()
    {
        if (!$this->available_for_borrowing) {
            return 'Not Available for Borrowing';
        }
        
        $availableQty = $this->getAvailableQuantityAttribute();
        if ($availableQty <= 0) {
            return 'Fully Borrowed';
        }

        // Compute total borrowed from difference to show partial state consistently
        $totalBorrowed = max(0, (int) ($this->quantity ?? 0) - $availableQty);
        if ($totalBorrowed > 0) {
            return "Partially Borrowed ({$availableQty} available)";
        }

        return 'Available for Borrowing';
    }

    public function getTotalValueAttribute()
    {
        if ($this->supply && $this->supply->unit_price) {
            return $this->quantity * $this->supply->unit_price;
        }
        return 0;
    }

    public function getFormattedIssuedOnAttribute()
    {
        return $this->issued_on ? $this->issued_on->format('M d, Y') : null;
    }

    public function getFormattedQuantityAttribute()
    {
        return number_format($this->quantity) . ' ' . ($this->supply->unit ?? 'units');
    }

    public function getIssueIdAttribute()
    {
        return $this->batch_id ?? $this->id;
    }

    // Scopes
    public function scopeToday($query)
    {
        return $query->whereDate('issued_on', today());
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('issued_on', now()->month)
                    ->whereYear('issued_on', now()->year);
    }

    public function scopeThisYear($query)
    {
        return $query->whereYear('issued_on', now()->year);
    }

    public function scopeByDepartment($query, $departmentId)
    {
        return $query->where('departments_id', $departmentId);
    }

    public function scopeBySupply($query, $supplyId)
    {
        return $query->where('supplies_id', $supplyId);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('issued_on', [$startDate, $endDate]);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('issued_on', '>=', now()->subDays($days));
    }

    // Helper Methods
    public function canBeDeleted()
    {
        // Can be deleted if issued within the last 30 days or by admin
        return $this->issued_on >= now()->subDays(30) || 
               (auth()->check() && auth()->user()->hasRole('admin'));
    }

    public function getStatusBadgeClass()
    {
        $daysSinceIssued = $this->issued_on->diffInDays(now());
        
        if ($daysSinceIssued <= 1) {
            return 'badge-success'; // Recent
        } elseif ($daysSinceIssued <= 7) {
            return 'badge-info'; // This week
        } elseif ($daysSinceIssued <= 30) {
            return 'badge-warning'; // This month
        } else {
            return 'badge-secondary'; // Older
        }
    }

    public function getStatusText()
    {
        $daysSinceIssued = $this->issued_on->diffInDays(now());
        
        if ($daysSinceIssued == 0) {
            return 'Today';
        } elseif ($daysSinceIssued == 1) {
            return 'Yesterday';
        } elseif ($daysSinceIssued <= 7) {
            return $daysSinceIssued . ' days ago';
        } elseif ($daysSinceIssued <= 30) {
            return $this->issued_on->format('M d');
        } else {
            return $this->issued_on->format('M d, Y');
        }
    }
}
