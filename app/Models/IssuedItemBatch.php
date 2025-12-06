<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IssuedItemBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'department_id',
        'user_id',
        'issued_by',
        'issued_on',
        'notes',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function issuer()
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function items()
    {
        return $this->hasMany(IssuedItem::class, 'batch_id');
    }
}