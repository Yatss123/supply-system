<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkUserActionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->isSuperAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = [
            'user_ids' => [
                'required',
                'array',
                'min:1'
            ],
            'user_ids.*' => [
                'integer',
                'exists:users,id',
                'not_in:' . auth()->id() // Prevent self-action
            ],
            'action' => [
                'required',
                'string',
                Rule::in(['delete', 'activate', 'deactivate', 'assign_role'])
            ],
        ];

        // Add role_id validation when action is assign_role
        if ($this->input('action') === 'assign_role') {
            $rules['role_id'] = [
                'required',
                'integer',
                'exists:roles,id'
            ];
        }

        return $rules;
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'user_ids.required' => 'Please select at least one user.',
            'user_ids.array' => 'Invalid user selection format.',
            'user_ids.min' => 'Please select at least one user.',
            'user_ids.*.integer' => 'Invalid user ID format.',
            'user_ids.*.exists' => 'One or more selected users do not exist.',
            'user_ids.*.not_in' => 'You cannot perform bulk actions on yourself.',
            
            'action.required' => 'Please select an action to perform.',
            'action.in' => 'The selected action is invalid.',
            
            'role_id.required' => 'Please select a role to assign.',
            'role_id.exists' => 'The selected role is invalid.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $this->validateBulkAction($validator);
        });
    }

    /**
     * Validate bulk action constraints.
     */
    protected function validateBulkAction($validator)
    {
        $userIds = $this->input('user_ids', []);
        $action = $this->input('action');

        // Prevent deletion of super admins by non-super admins
        if ($action === 'delete') {
            $superAdminRole = \App\Models\Role::where('name', 'super_admin')->first();
            if ($superAdminRole) {
                $superAdminCount = \App\Models\User::whereIn('id', $userIds)
                    ->where('role_id', $superAdminRole->id)
                    ->count();
                
                if ($superAdminCount > 0) {
                    $validator->errors()->add(
                        'user_ids',
                        'Super Admin users cannot be deleted through bulk actions.'
                    );
                }
            }
        }

        // Validate Dean assignment constraints for assign_role action
        if ($action === 'assign_role') {
            $roleId = $this->input('role_id');
            if ($roleId) {
                $role = \App\Models\Role::find($roleId);
                if ($role && $role->name === 'dean') {
                    $validator->errors()->add(
                        'role_id',
                        'Dean role cannot be assigned through bulk actions due to department constraints. Please assign Dean roles individually.'
                    );
                }
            }
        }
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'user_ids' => 'selected users',
            'action' => 'bulk action',
            'role_id' => 'role',
        ];
    }
}