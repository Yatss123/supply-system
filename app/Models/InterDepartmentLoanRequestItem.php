<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InterDepartmentLoanRequestItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'inter_department_loan_request_id',
        'issued_item_id',
        'quantity_requested',
        'notes',
    ];

    public function loanRequest(): BelongsTo
    {
        return $this->belongsTo(InterDepartmentLoanRequest::class, 'inter_department_loan_request_id');
    }

    public function issuedItem(): BelongsTo
    {
        return $this->belongsTo(IssuedItem::class, 'issued_item_id');
    }
}