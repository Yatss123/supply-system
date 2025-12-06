<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepartmentCartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'cart_id',
        'supply_request_id',
        'supply_id',
        'item_name',
        'unit',
        'quantity',
        'item_type', // consumable | grantable
        'attributes', // JSON
        'status',     // pending | edited | removed
    ];

    protected $casts = [
        'cart_id' => 'integer',
        'supply_request_id' => 'integer',
        'supply_id' => 'integer',
        'quantity' => 'integer',
        'attributes' => 'array',
    ];

    public const TYPE_CONSUMABLE = 'consumable';
    public const TYPE_GRANTABLE = 'grantable';

    public function cart()
    {
        return $this->belongsTo(DepartmentCart::class, 'cart_id');
    }

    public function supplyRequest()
    {
        return $this->belongsTo(SupplyRequest::class, 'supply_request_id');
    }

    public function supply()
    {
        return $this->belongsTo(Supply::class, 'supply_id');
    }
}