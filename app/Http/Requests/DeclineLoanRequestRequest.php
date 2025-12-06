<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DeclineLoanRequestRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $loanRequest = $this->route('loanRequest');
        
        // Only admin users can decline loan requests and the request must be pending
        return auth()->user()->hasAdminPrivileges() && 
               $loanRequest && 
               $loanRequest->isPending();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'decline_reason' => [
                'required',
                'string',
                'max:1000',
                'min:10'
            ]
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'decline_reason.required' => 'Please provide a reason for declining this loan request.',
            'decline_reason.min' => 'Decline reason must be at least 10 characters long.',
            'decline_reason.max' => 'Decline reason cannot exceed 1000 characters.'
        ];
    }

    /**
     * Get custom attribute names for validation errors.
     */
    public function attributes(): array
    {
        return [
            'decline_reason' => 'decline reason'
        ];
    }

    /**
     * Handle a failed authorization attempt.
     */
    protected function failedAuthorization()
    {
        abort(403, 'This loan request has already been processed or you do not have permission to decline it.');
    }
}