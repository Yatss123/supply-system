<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanRequestBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'department_id',
        'purpose',
        'needed_from_date',
        'expected_return_date',
        'status',
    ];

    protected $casts = [
        'needed_from_date' => 'date',
        'expected_return_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function items()
    {
        return $this->hasMany(LoanRequest::class, 'batch_id');
    }

    public function loanRequests()
    {
        // Alias for clarity in views/controllers; keeps backward compatibility with 'items'
        return $this->hasMany(LoanRequest::class, 'batch_id');
    }
}