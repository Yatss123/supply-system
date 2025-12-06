<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Role;
use App\Models\User;

class UpdateUserRequest extends FormRequest
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
        $user = $this->route('user');
        
        $rules = [
            'name' => ['required', 'string', 'max:255', 'min:2'],
            'email' => [
                'required', 
                'string', 
                'email', 
                'max:255',
                Rule::unique('users')->ignore($user->id),
                'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/'
            ],
            'password' => [
                'nullable', 
                'string', 
                'min:8', 
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/'
            ],
            'role_id' => [
                'required',
                'integer',
                'exists:roles,id'
            ],
            'department_id' => [
                'nullable',
                'integer',
                'exists:departments,id'
            ],
            'email_verified' => ['boolean'],
        ];

        // Add department requirement for specific roles
        $roleId = $this->input('role_id');
        if ($roleId) {
            $role = Role::find($roleId);
            if ($role && in_array($role->name, ['dean', 'adviser', 'student'])) {
                $rules['department_id'] = [
                    'required',
                    'integer',
                    'exists:departments,id'
                ];
            }
        }

        return $rules;
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The name field is required.',
            'name.min' => 'The name must be at least 2 characters.',
            'name.max' => 'The name may not be greater than 255 characters.',
            
            'email.required' => 'The email field is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email address is already registered.',
            'email.regex' => 'Please enter a valid email format.',
            
            'password.min' => 'The password must be at least 8 characters.',
            'password.confirmed' => 'The password confirmation does not match.',
            'password.regex' => 'The password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.',
            
            'role_id.required' => 'Please select a role for the user.',
            'role_id.exists' => 'The selected role is invalid.',
            
            'department_id.required' => 'A department is required for this role.',
            'department_id.exists' => 'The selected department is invalid.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $this->validateDeanUniqueness($validator);
        });
    }

    /**
     * Validate Dean uniqueness constraint.
     */
    protected function validateDeanUniqueness($validator)
    {
        $user = $this->route('user');
        $roleId = $this->input('role_id');
        $departmentId = $this->input('department_id');

        if ($roleId && $departmentId) {
            $role = Role::find($roleId);
            
            if ($role && $role->name === 'dean') {
                $existingDean = User::where('role_id', $roleId)
                    ->where('department_id', $departmentId)
                    ->where('id', '!=', $user->id)
                    ->exists();

                if ($existingDean) {
                    $validator->errors()->add(
                        'role_id',
                        'A Dean is already assigned to this department. Each department can only have one Dean.'
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
            'name' => 'full name',
            'email' => 'email address',
            'password' => 'password',
            'role_id' => 'role',
            'department_id' => 'department',
        ];
    }
}