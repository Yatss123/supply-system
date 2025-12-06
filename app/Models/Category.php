<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'description'
    ];

    public function supplies()
    {
        return $this->belongsToMany(Supply::class, 'supply_category');
    }
}
