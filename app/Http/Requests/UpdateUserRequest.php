<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        $data = [];
        if ($this->has('userName')) {
            $data['user_name'] = $this->input('userName');
        }
        if ($this->has('employeeId')) {
            $data['employee_id'] = $this->input('employeeId');
        }
        if ($this->has('roleId')) {
            $data['role_id'] = $this->input('roleId');
        }
        if (!empty($data)) {
            $this->merge($data);
        }
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role?->name === 'admin';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $id = $this->route('user') instanceof \App\Models\User ? $this->route('user')->id : $this->route('user');
        return [
            'user_name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255', 'unique:users,email,' . $id],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
            'employee_id' => ['nullable', 'exists:employees,id'],
            'role_id' => ['sometimes', 'exists:roles,id'],
        ];
    }
}
    /**
     * Determine if the user is authorized to make this request.
     */
