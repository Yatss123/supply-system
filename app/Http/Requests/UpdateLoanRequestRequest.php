<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Supply;
use App\Models\LoanRequest;

class UpdateLoanRequestRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $loanRequest = $this->route('loan_request');
        
        // Only allow updating if request is pending and user is the requester or admin
        return $loanRequest && $loanRequest->isPending() && 
               (auth()->user()->hasAdminPrivileges() || $loanRequest->requested_by === auth()->id());
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'supply_id' => [
                'required',
                'integer',
                'exists:supplies,id',
                function ($attribute, $value, $fail) {
                    $supply = Supply::find($value);
                    if ($supply && !$supply->isBorrowable()) {
                        $fail('This supply item cannot be borrowed. Only borrowable items are allowed for loan requests.');
                    }
                }
            ],
            'department_id' => [
                'required',
                'integer',
                'exists:departments,id'
            ],
            'quantity_requested' => [
                'required',
                'integer',
                'min:1',
                function ($attribute, $value, $fail) {
                    $supplyId = $this->input('supply_id');
                    if ($supplyId) {
                        $supply = Supply::find($supplyId);
                        if ($supply && $supply->availableQuantity() < $value) {
                            $fail('Requested quantity exceeds available stock. Available: ' . $supply->availableQuantity());
                        }
                    }
                }
            ],
            'purpose' => [
                'nullable',
                'string',
                'max:1000'
            ],
            'expected_return_date' => [
                'required',
                'date',
                'after:today'
            ]
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'supply_id.required' => 'Please select a supply item.',
            'supply_id.exists' => 'The selected supply item does not exist.',
            'department_id.required' => 'Please select a department.',
            'department_id.exists' => 'The selected department does not exist.',
            'quantity_requested.required' => 'Please specify the quantity requested.',
            'quantity_requested.min' => 'Quantity requested must be at least 1.',
            'purpose.max' => 'Purpose cannot exceed 1000 characters.',
            'expected_return_date.required' => 'Please specify the expected return date.',
            'expected_return_date.after' => 'Expected return date must be after today.'
        ];
    }

    /**
     * Get custom attribute names for validation errors.
     */
    public function attributes(): array
    {
        return [
            'supply_id' => 'supply item',
            'department_id' => 'department',
            'quantity_requested' => 'quantity requested',
            'expected_return_date' => 'expected return date'
        ];
    }

    /**
     * Handle a failed authorization attempt.
     */
    protected function failedAuthorization()
    {
        abort(403, 'You cannot edit this loan request.');
    }
}