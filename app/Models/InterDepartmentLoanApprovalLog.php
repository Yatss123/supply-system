<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InterDepartmentLoanApprovalLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'inter_department_loan_request_id',
        'approver_id',
        'approver_role',
        'action',
        'notes',
    ];

    public function loanRequest(): BelongsTo
    {
        return $this->belongsTo(InterDepartmentLoanRequest::class, 'inter_department_loan_request_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}