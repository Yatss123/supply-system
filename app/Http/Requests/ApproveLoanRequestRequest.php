<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApproveLoanRequestRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $loanRequest = $this->route('loanRequest');
        
        // Only admin users can approve loan requests and the request must be approvable
        return auth()->user()->hasAdminPrivileges() && 
               $loanRequest && 
               $loanRequest->canBeApproved();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'approval_notes' => [
                'nullable',
                'string',
                'max:1000'
            ]
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'approval_notes.max' => 'Approval notes cannot exceed 1000 characters.'
        ];
    }

    /**
     * Get custom attribute names for validation errors.
     */
    public function attributes(): array
    {
        return [
            'approval_notes' => 'approval notes'
        ];
    }

    /**
     * Handle a failed authorization attempt.
     */
    protected function failedAuthorization()
    {
        abort(403, 'This loan request cannot be approved. Check stock availability or your permissions.');
    }
}