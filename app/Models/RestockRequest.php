<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RestockRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'supply_id', 'quantity', 'status', 'supplier_id', 'requested_department_id', 'items_json'
    ];

    public function supply()
    {
        return $this->belongsTo(Supply::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function requestedDepartment()
    {
        return $this->belongsTo(Department::class, 'requested_department_id');
    }
}
