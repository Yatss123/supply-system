<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BorrowedItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'supply_id', 'department_id', 'user_id', 'quantity', 'borrowed_at', 'returned_at',
        'returned_status', 'missing_count', 'damaged_count', 'damage_severity', 'damage_description',
        'return_note', 'return_photo_path',
        'return_pending_at', 'return_verification_notes', 'return_verified_by', 'return_verified_at'
    ];

    protected $casts = [
        'borrowed_at' => 'datetime',
        'returned_at' => 'datetime',
        'return_pending_at' => 'datetime',
        'return_verified_at' => 'datetime'
    ];

    // Relationships
    public function supply()
    {
        return $this->belongsTo(Supply::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function logs()
    {
        return $this->hasMany(BorrowedItemLog::class);
    }

    public function loanRequest()
    {
        return $this->hasOne(LoanRequest::class);
    }

    // Status helper methods
    public function isReturned()
    {
        return !is_null($this->returned_at);
    }

    public function isBorrowed()
    {
        return is_null($this->returned_at);
    }

    // Helper methods
    public function getDaysOverdue()
    {
        if ($this->loanRequest && $this->loanRequest->expected_return_date && $this->isBorrowed()) {
            $expectedReturn = $this->loanRequest->expected_return_date;
            $now = now();
            
            if ($now->gt($expectedReturn)) {
                return $now->diffInDays($expectedReturn);
            }
        }
        return 0;
    }

    public function isOverdue()
    {
        return $this->getDaysOverdue() > 0;
    }

    public function returnItem(?string $note = null, ?string $photoPath = null)
    {
        // Initiate return: mark as pending verification, store note/photo
        $this->update([
            'return_pending_at' => now(),
            'return_note' => $note,
            'return_photo_path' => $photoPath,
        ]);
    }

    public function verifyReturn(\App\Models\User $verifier, ?string $verificationNotes = null)
    {
        $this->update([
            'returned_at' => now(),
            'returned_status' => $this->returned_status ?? 'returned',
            'return_verification_notes' => $verificationNotes,
            'return_verified_by' => $verifier->id,
            'return_verified_at' => now(),
        ]);

        // Update loan request status if exists
        if ($this->loanRequest) {
            $this->loanRequest->complete($this->id);
        }
    }

    public function verifyReturnWithStatus(\App\Models\User $verifier, string $status, ?int $missingCount = null, ?int $damagedCount = null, ?string $verificationNotes = null, ?string $severity = null, ?string $description = null)
    {
        // Guard against double verification to avoid duplicate stock adjustments
        $firstVerification = is_null($this->return_verified_at);

        // Normalize counts
        $missing = max(0, (int) ($missingCount ?? 0));
        $damaged = max(0, (int) ($damagedCount ?? 0));

        $this->update([
            'returned_at' => now(),
            'returned_status' => $status,
            'missing_count' => $missingCount,
            'damaged_count' => $damagedCount,
            'damage_severity' => $severity,
            'damage_description' => $description,
            'return_verification_notes' => $verificationNotes,
            'return_verified_by' => $verifier->id,
            'return_verified_at' => now(),
        ]);

        // If there are missing or damaged items, adjust stock once on first verification
        $adjustment = $missing + $damaged;
        if ($firstVerification && $adjustment > 0) {
            // Prefer adjusting the variant if this loan was variant-specific
            if ($this->loanRequest && $this->loanRequest->supply_variant_id) {
                $variant = $this->loanRequest->variant;
                if ($variant && method_exists($variant, 'reduceStock')) {
                    $variant->reduceStock($adjustment);
                }
            } else {
                // Fallback to supply-level quantity adjustment
                if ($this->supply) {
                    $newQty = max(0, (int) ($this->supply->quantity ?? 0) - $adjustment);
                    $this->supply->update(['quantity' => $newQty]);
                }
            }
        }

        if ($this->loanRequest) {
            $this->loanRequest->complete($this->id);
        }
    }
}
