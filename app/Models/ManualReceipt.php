<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManualReceipt extends Model
{
    use HasFactory;

    protected $fillable = [
        'supply_id',
        'quantity',
        'supplier_id',
        'receipt_date',
        'reference_number',
        'cost_per_unit',
        'notes',
        'added_by',
        'status',
    ];

    protected $casts = [
        'receipt_date' => 'date',
        'cost_per_unit' => 'decimal:2',
    ];

    public function supply()
    {
        return $this->belongsTo(Supply::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by');
    }
}
