<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'supply_id',
        'location_id',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    public function supply()
    {
        return $this->belongsTo(Supply::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}