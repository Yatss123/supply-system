<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepartmentMonthlyAllocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'department_id',
        'month',
        'status',
        'created_by',
        'updated_by',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function items()
    {
        return $this->hasMany(DepartmentMonthlyAllocationItem::class, 'allocation_id');
    }
}