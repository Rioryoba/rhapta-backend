<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class StoreEmployeeContractRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'firstName' => ['required', 'string', 'max:255'],
            'lastName' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:employees,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'dateOfBirth' => ['nullable', 'date'], 
            'gender' => ['nullable', 'enum:male,female'],
            'hireDate' => ['required', 'date'],
            'salary' => ['required', 'numeric', 'min:0'],
            'positionId' => ['required', 'exists:positions,id'],
            'departmentId' => ['required', 'exists:departments,id'],
            'status' => ['required', 'enum:active,inactive,terminated'],
        ];
    }
    public function prepareForValidation()
    {
        $this->merge([
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'date_of_birth' => $this->dateOfBirth,
            'hire_date' => $this->hireDate,
            'position_id' => $this->positionId,
            'department_id' => $this->departmentId,
        ]);
    }
}
