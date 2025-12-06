<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InterDepartmentLoanRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'issued_item_id',
        'lending_department_id',
        'borrowing_department_id',
        'requested_by',
        'quantity_requested',
        'purpose',
        'expected_return_date',
        'planned_start_date',
        'status',
        'lending_approved_by',
        'lending_approved_at',
        'lending_approval_notes',
        'borrowing_confirmed_by',
        'borrowing_confirmed_at',
        'borrowing_confirmation_notes',
        'admin_approved_by',
        'admin_approved_at',
        'admin_approval_notes',
        'dean_approved_by',
        'dean_approved_at',
        'dean_approval_notes',
        'declined_by',
        'declined_at',
        'decline_reason',
        'completed_at',
        'returned_at',
        'notes',
    ];

    protected $casts = [
        'expected_return_date' => 'date',
        'planned_start_date' => 'date',
        'lending_approved_at' => 'datetime',
        'borrowing_confirmed_at' => 'datetime',
        'admin_approved_at' => 'datetime',
        'dean_approved_at' => 'datetime',
        'declined_at' => 'datetime',
        'completed_at' => 'datetime',
        'returned_at' => 'datetime',
    ];

    // Relationships
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

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function lendingApprovedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'lending_approved_by');
    }

    public function borrowingConfirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'borrowing_confirmed_by');
    }

    public function adminApprovedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_approved_by');
    }

    public function deanApprovedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dean_approved_by');
    }

    public function declinedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'declined_by');
    }

    public function interDepartmentBorrowedItems(): HasMany
    {
        return $this->hasMany(InterDepartmentBorrowedItem::class);
    }

    public function requestItems(): HasMany
    {
        return $this->hasMany(InterDepartmentLoanRequestItem::class, 'inter_department_loan_request_id');
    }

    public function approvalLogs(): HasMany
    {
        return $this->hasMany(InterDepartmentLoanApprovalLog::class, 'inter_department_loan_request_id')
            ->orderBy('created_at');
    }

    // Status helper methods
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isLendingApproved(): bool
    {
        return $this->status === 'lending_approved';
    }

    public function isBorrowingConfirmed(): bool
    {
        return $this->status === 'borrowing_confirmed';
    }

    public function isAdminApproved(): bool
    {
        return $this->status === 'admin_approved';
    }

    public function isDeclined(): bool
    {
        return $this->status === 'declined';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isBorrowed(): bool
    {
        return $this->status === 'borrowed';
    }

    public function isReturned(): bool
    {
        return $this->status === 'returned';
    }

    public function isReturnPending(): bool
    {
        return $this->status === 'return_pending';
    }

    public function isDeanApproved(): bool
    {
        return !is_null($this->dean_approved_by);
    }

    public function needsDeanApproval(): bool
    {
        // If the requester is a dean, no dean approval is needed
        if ($this->requestedBy && $this->requestedBy->isDean()) {
            return false;
        }
        
        return $this->isPending() && is_null($this->dean_approved_by);
    }

    public function needsLendingDeanApproval(): bool
    {
        // Check if lending department dean approval is needed
        return $this->status === 'dean_approved' && is_null($this->lending_approved_by);
    }

    public function isLendingDeanApproved(): bool
    {
        return !is_null($this->lending_approved_by);
    }

    public function canProceedToReview(): bool
    {
        // Can proceed if dean initiated OR dean has approved
        return $this->requestedBy->isDean() || $this->isDeanApproved();
    }

    // Workflow methods
    public function approveLending(User $user, string $notes = null): bool
    {
        // Lending approval can happen after dean approval
        if (!$this->isDeanApproved()) {
            return false;
        }

        $this->update([
            'status' => 'lending_approved',
            'lending_approved_by' => $user->id,
            'lending_approved_at' => now(),
            'lending_approval_notes' => $notes,
        ]);

        return true;
    }

    public function lendingDeanApprove(User $user, string $notes = null): bool
    {
        // Lending department dean approval - must be dean of lending department
        if (!$this->isDeanApproved() || !$user->isDeanOf($this->lendingDepartment)) {
            return false;
        }

        $this->update([
            'status' => 'lending_dean_approved',
            'lending_approved_by' => $user->id,
            'lending_approved_at' => now(),
            'lending_approval_notes' => $notes,
        ]);

        return true;
    }



    public function adminApprove(User $user, string $notes = null): bool
    {
        // Admin can approve if both dean and lending department dean have approved
        if (!$this->isDeanApproved() || !$this->isLendingDeanApproved()) {
            return false;
        }

        $this->update([
            // Set status to BORROWED upon admin approval
            'status' => 'borrowed',
            'admin_approved_by' => $user->id,
            'admin_approved_at' => now(),
            'admin_approval_notes' => $notes,
        ]);

        return true;
    }

    public function deanApprove(User $user, string $notes = null): bool
    {
        // Dean approval is the first step, so only check if it's pending
        if (!$this->isPending()) {
            return false;
        }

        $this->update([
            'status' => 'dean_approved',
            'dean_approved_by' => $user->id,
            'dean_approved_at' => now(),
            'dean_approval_notes' => $notes,
        ]);

        return true;
    }

    /**
     * Auto-approve request when created by a dean
     * This bypasses the normal dean approval step and moves directly to lending dean approval
     */
    public function autoApproveByDean(User $user): bool
    {
        // Only allow auto-approval if request is pending and user is a dean
        if (!$this->isPending() || !$user->isDean()) {
            return false;
        }

        // Verify the dean is from the borrowing department
        if (!$user->isDeanOf($this->borrowingDepartment)) {
            return false;
        }

        $this->update([
            'status' => 'dean_approved',
            'dean_approved_by' => $user->id,
            'dean_approved_at' => now(),
            'dean_approval_notes' => 'Auto-approved: Request created by department dean',
        ]);

        // Send notification to lending department dean
        $this->sendAutoApprovalNotifications();

        return true;
    }

    /**
     * Send notifications when auto-approval occurs
     */
    private function sendAutoApprovalNotifications(): void
    {
        // Get lending department dean(s)
        $lendingDeans = User::whereHas('role', function($query) {
            $query->where('name', 'dean');
        })->where('department_id', $this->lending_department_id)->get();

        // Send notification to lending department dean(s)
        foreach ($lendingDeans as $dean) {
            $dean->notify(new \App\Notifications\InterDepartmentLoanNotification($this, 'auto_approved'));
        }

        // Also notify admin users about the auto-approval
        $adminUsers = User::whereHas('role', function($query) {
            $query->whereIn('name', ['admin', 'super_admin']);
        })->get();

        foreach ($adminUsers as $admin) {
            $admin->notify(new \App\Notifications\InterDepartmentLoanNotification($this, 'auto_approved'));
        }
    }

    public function decline(User $user, string $reason): bool
    {
        if ($this->isCompleted() || $this->isReturned()) {
            return false;
        }

        $this->update([
            'status' => 'declined',
            'declined_by' => $user->id,
            'declined_at' => now(),
            'decline_reason' => $reason,
        ]);

        return true;
    }

    public function complete(): bool
    {
        // Only mark as completed after all borrowed items are returned
        // and request is currently in a state consistent with returns in progress
        // Allow completion from either 'borrowed' or 'return_pending'.
        if (!($this->isBorrowed() || $this->isReturnPending())) {
            return false;
        }

        $hasUnreturnedItems = $this->interDepartmentBorrowedItems()
            ->whereNotIn('status', ['returned'])
            ->exists();

        if ($hasUnreturnedItems) {
            return false;
        }

        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'returned_at' => now(),
        ]);

        return true;
    }

    public function markAsReturned(): bool
    {
        // Backward compatibility: treat markAsReturned as completion after physical return
        return $this->complete();
    }

    /**
     * Get status badge information for display
     */
    public function getStatusBadge(): array
    {
        $badges = [
            'pending' => ['class' => 'bg-warning text-dark', 'text' => 'Pending Dean Approval'],
            'dean_approved' => ['class' => 'bg-info text-white', 'text' => 'Pending Lending Approval'],
            'lending_approved' => ['class' => 'bg-primary text-white', 'text' => 'Pending Admin Approval'],
            'admin_approved' => ['class' => 'bg-success text-white', 'text' => 'Admin Approved'],
            'borrowed' => ['class' => 'bg-dark text-white', 'text' => 'Borrowed'],
            'return_pending' => ['class' => 'bg-warning text-dark', 'text' => 'Return Pending'],
            'completed' => ['class' => 'bg-success text-white', 'text' => 'Completed'],
            'declined' => ['class' => 'bg-danger text-white', 'text' => 'Declined']
        ];
        
        return $badges[$this->status] ?? ['class' => 'bg-secondary text-white', 'text' => ucfirst($this->status)];
    }

    /**
     * Get status display information for detailed view
     */
    public function getStatusDisplay(): array
    {
        switch ($this->status) {
            case 'pending':
                return [
                    'text' => 'Pending Dean Approval',
                    'class' => 'badge bg-warning text-dark'
                ];
            case 'dean_approved':
                return [
                    'text' => 'Pending Lending Department Approval',
                    'class' => 'badge bg-info text-white'
                ];
            case 'lending_approved':
                return [
                    'text' => 'Pending Final Admin Approval',
                    'class' => 'badge bg-primary text-white'
                ];
            case 'admin_approved':
                return [
                    'text' => 'Admin Approved - Ready for Transfer',
                    'class' => 'badge bg-success text-white'
                ];
            case 'borrowed':
                return [
                    'text' => 'Borrowed - In Possession',
                    'class' => 'badge bg-dark text-white'
                ];
            case 'return_pending':
                return [
                    'text' => 'Return Pending - Awaiting Verification',
                    'class' => 'badge bg-warning text-dark'
                ];
            case 'completed':
                return [
                    'text' => 'Completed',
                    'class' => 'badge bg-success text-white'
                ];
            case 'declined':
                return [
                    'text' => 'Declined',
                    'class' => 'badge bg-danger text-white'
                ];
            default:
                return [
                    'text' => ucfirst($this->status),
                    'class' => 'badge bg-secondary text-white'
                ];
        }
    }

    /**
     * Get available status options for filtering
     */
    public static function getStatusOptions(): array
    {
        return [
            'pending' => 'Pending Dean Approval',
            'dean_approved' => 'Pending Lending Approval',
            'lending_approved' => 'Pending Admin Approval',
            'admin_approved' => 'Admin Approved',
            'borrowed' => 'Borrowed',
            'return_pending' => 'Return Pending',
            'completed' => 'Completed',
            'declined' => 'Declined'
        ];
    }

    /**
     * Create statistics card data
     */
    public static function createStatsCard($title, $count, $borderColor, $textColor, $icon): array
    {
        return [
            'title' => $title,
            'count' => $count,
            'borderColor' => $borderColor,
            'textColor' => $textColor,
            'icon' => $icon
        ];
    }
}
