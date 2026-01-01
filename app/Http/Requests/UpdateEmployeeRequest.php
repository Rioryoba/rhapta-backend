<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class UpdateEmployeeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {   
        $method = $this->method();
        if ($method === 'PUT') {
            return [
                'first_name' => ['sometimes', 'string', 'max:255'],
                'last_name' => ['sometimes', 'string', 'max:255'],
                'email' => ['sometimes', 'string', 'email', 'max:255', 'unique:employees,email,' . $this->route('employee')->id],
                'phone' => ['nullable', 'string', 'max:20'],
                'date_of_birth' => ['nullable', 'date'],
                'gender' => ['nullable', 'in:male,female'],
                'hire_date' => ['sometimes', 'date'],
                'salary' => ['sometimes', 'numeric', 'min:0'],
                'position_id' => ['sometimes', 'exists:positions,id'],
                'department_id' => ['sometimes', 'exists:departments,id'],
                'status' => ['sometimes', 'in:active,inactive,terminated'],
                'password' => ['nullable', 'string', 'min:6'],
                'role_id' => ['nullable', 'exists:roles,id'],
                'profilePicture' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            ];
        } else if ($method === 'PATCH') {
            return [
                'first_name' => ['sometimes', 'string', 'max:255'],
                'last_name' => ['sometimes', 'string', 'max:255'],
                'email' => ['sometimes', 'string', 'email', 'max:255', 'unique:employees,email,' . $this->route('employee')->id],
                'phone' => ['nullable', 'string', 'max:20'],
                'date_of_birth' => ['nullable', 'date'],
                'gender' => ['nullable', 'in:male,female'],
                'hire_date' => ['sometimes', 'date'],
                'salary' => ['sometimes', 'numeric', 'min:0'],
                'position_id' => ['nullable', 'exists:positions,id'],
                'department_id' => ['nullable', 'exists:departments,id'],
                'status' => ['nullable', 'in:active,inactive,terminated'],
                'password' => ['nullable', 'string', 'min:6'],
                'role_id' => ['nullable', 'exists:roles,id'],
                'profilePicture' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            ];
        }
        return [];
}

    public function prepareForValidation()
    {
        $data = [];
        if ($this->has('firstName')) $data['first_name'] = $this->input('firstName');
        if ($this->has('lastName')) $data['last_name'] = $this->input('lastName');
        if ($this->has('dateOfBirth')) $data['date_of_birth'] = $this->input('dateOfBirth');
        if ($this->has('hireDate')) $data['hire_date'] = $this->input('hireDate');
        if ($this->has('positionId')) $data['position_id'] = $this->input('positionId');
        if ($this->has('departmentId')) $data['department_id'] = $this->input('departmentId');
        if ($this->has('roleId')) $data['role_id'] = $this->input('roleId');
        if (!empty($data)) {
            $this->merge($data);
        }
        Log::info('Prepared for validation:', $this->all());
    }
}
