<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePayrollRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'employee_id' => ['sometimes', 'required', 'integer', 'exists:employees,id'],
            'month' => ['sometimes', 'required', 'string', 'regex:/^\d{4}-\d{2}$/'], // Format: YYYY-MM
            'pay_date' => ['sometimes', 'required', 'date'],
            'basic_salary' => ['sometimes', 'required', 'integer', 'min:0'],
            'allowances' => ['nullable', 'integer', 'min:0'],
            'deductions' => ['nullable', 'integer', 'min:0'],
            'status' => ['sometimes', 'required', 'string', Rule::in(['paid', 'pending', 'failed'])],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convert camelCase to snake_case if needed
        if ($this->has('employeeId')) {
            $this->merge(['employee_id' => $this->employeeId]);
        }
        if ($this->has('payDate')) {
            $this->merge(['pay_date' => $this->payDate]);
        }
        if ($this->has('basicSalary')) {
            $this->merge(['basic_salary' => $this->basicSalary]);
        }
        if ($this->has('netSalary')) {
            $this->merge(['net_salary' => $this->netSalary]);
        }
        if ($this->has('period')) {
            $this->merge(['month' => $this->period]);
        }
    }
}
