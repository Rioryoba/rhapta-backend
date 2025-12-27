<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    protected function prepareForValidation()
    {
        $data = [];
        if ($this->has('userName')) $data['user_name'] = $this->input('userName');
        if ($this->has('employeeId')) $data['employee_id'] = $this->input('employeeId');
        if ($this->has('roleId')) $data['role_id'] = $this->input('roleId');
        if (!empty($data)) {
            $this->merge($data);
        }
    }
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role?->name === 'admin';
    }
    public function rules(): array
    {
        return [
            'user_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'employee_id' => ['nullable', 'exists:employees,id'],
            'role_id' => ['required', 'exists:roles,id'],
        ];
    }
}
