<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InterDepartmentReturnRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'inter_department_borrowed_item_id',
        'quantity_returned',
        'is_damaged',
        'initiated_by',
        'verified_by',
        'notes',
        'photo_path',
        'missing_count',
        'damaged_count',
        'damage_severity',
        'damage_description',
        'verified_at',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
    ];

    public function borrowedItem(): BelongsTo
    {
        return $this->belongsTo(InterDepartmentBorrowedItem::class, 'inter_department_borrowed_item_id');
    }

    public function initiatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiated_by');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}