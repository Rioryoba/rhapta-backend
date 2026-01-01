<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $userId = auth()->id();
        $user = \App\Models\User::find($userId);
        $employeeId = $user ? $user->employee_id : null;
        
        $rules = [
            'user_name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'email', 'max:255', 'unique:users,email,' . $userId],
        ];
        
        // Allow phone update if user has an employee_id
        if ($employeeId) {
            $rules['phone'] = ['sometimes', 'nullable', 'string', 'max:20'];
        }
        
        return $rules;
    }

    public function messages(): array
    {
        return [
            'user_name.required' => 'Name is required.',
            'user_name.string' => 'Name must be a valid string.',
            'user_name.max' => 'Name must not exceed 255 characters.',
            'email.required' => 'Email is required.',
            'email.email' => 'Email must be a valid email address.',
            'email.unique' => 'This email is already taken.',
        ];
    }
}



