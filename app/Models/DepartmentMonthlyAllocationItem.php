<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepartmentMonthlyAllocationItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'allocation_id',
        'supply_id',
        'min_stock_level',
        'issued_qty',
        'suggest_qty',
        'low_stock',
        'attributes',
        // new admin controls
        'max_limit',
        'target_issue_qty',
        // issuing workflow
        'issue_status',
        'staged_issue_qty',
    ];

    protected $casts = [
        'min_stock_level' => 'integer',
        'issued_qty' => 'integer',
        'suggest_qty' => 'integer',
        'low_stock' => 'boolean',
        'attributes' => 'array',
        // new admin controls
        'max_limit' => 'integer',
        'target_issue_qty' => 'integer',
        // issuing workflow
        'issue_status' => 'string',
        'staged_issue_qty' => 'integer',
    ];

    public function allocation()
    {
        return $this->belongsTo(DepartmentMonthlyAllocation::class, 'allocation_id');
    }

    public function supply()
    {
        return $this->belongsTo(Supply::class, 'supply_id');
    }
}