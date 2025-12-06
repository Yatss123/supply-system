<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepartmentCart extends Model
{
    use HasFactory;

    protected $fillable = [
        'department_id',
        'status',
        'created_by',
        'updated_by',
        'admin_notes',
    ];

    protected $casts = [
        'department_id' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function items()
    {
        return $this->hasMany(DepartmentCartItem::class, 'cart_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public static function forDepartment(int $departmentId): self
    {
        return static::firstOrCreate(
            ['department_id' => $departmentId, 'status' => 'active'],
            ['created_by' => auth()->id(), 'updated_by' => auth()->id()]
        );
    }
}