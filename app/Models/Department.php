<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'department_name', // Allow mass assignment for department_name
        'dean_id',         // Allow mass assignment for dean_id (foreign key to users table)
        'status',          // Active/inactive status for department
        'stock_update_reminder_day', // ISO weekday 1-7 (nullable)
    ];

    /**
     * Get the name attribute (alias for department_name).
     */
    public function getNameAttribute()
    {
        return $this->department_name;
    }

    /**
     * Get the dean (current head) of this department.
     */
    public function dean()
    {
        return $this->belongsTo(User::class, 'dean_id');
    }

    /**
     * Get all users belonging to this department.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the current head name (for backward compatibility).
     */
    public function getCurrentHeadAttribute()
    {
        return $this->dean ? $this->dean->name : 'No Dean Assigned';
    }

    /**
     * Check if a specific user is the dean of this department.
     */
    public function isDeanedBy($user)
    {
        // Handle both User model instance and user ID
        $userId = $user instanceof User ? $user->id : $user;
        
        return $this->dean_id == $userId;
    }

    /**
     * Check if a user has dean privileges for this department.
     */
    public function userIsDean($user)
    {
        return $user instanceof User && $user->isDeanOf($this);
    }

    /**
     * Status helpers and scopes
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isInactive(): bool
    {
        return $this->status === 'inactive';
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    public function activate(): bool
    {
        return $this->update(['status' => 'active']);
    }

    public function deactivate(): bool
    {
        return $this->update(['status' => 'inactive']);
    }
}
