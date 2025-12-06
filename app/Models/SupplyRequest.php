<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplyRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_name',
        'quantity',
        'unit',
        'description',
        'department_id',
        'status',
        'supply_id',
        'supply_variant_id',
        'user_id',
        'batch_id',
        'admin_note',
        'rejection_reason',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function supply()
    {
        return $this->belongsTo(Supply::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function batch()
    {
        return $this->belongsTo(SupplyRequestBatch::class, 'batch_id');
    }

    public function audits()
    {
        return $this->hasMany(SupplyRequestAudit::class, 'supply_request_id');
    }
}
