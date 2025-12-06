<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'contact_person',
        'phone1',
        'phone2',
        'email',
        'facebook_messenger',
        'preferred_contact_method',
        'address1',
        'address2',
        'status',
    ];

    /**
     * Scope a query to only include active suppliers.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include inactive suppliers.
     */
    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    /**
     * Check if the supplier is active.
     */
    public function isActive()
    {
        return $this->status === 'active';
    }

    /**
     * Check if the supplier is inactive.
     */
    public function isInactive()
    {
        return $this->status === 'inactive';
    }

    /**
     * Activate the supplier.
     */
    public function activate()
    {
        return $this->update(['status' => 'active']);
    }

    /**
     * Deactivate the supplier.
     */
    public function deactivate()
    {
        return $this->update(['status' => 'inactive']);
    }

    public function supplies()
    {
        return $this->belongsToMany(Supply::class, 'supply_supplier');
    }
}
