<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BorrowedItemLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'borrowed_item_id',
        'user_id',
        'action',
        'quantity',
        'notes',
        'photo_path',
    ];

    public function borrowedItem(): BelongsTo
    {
        return $this->belongsTo(BorrowedItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}