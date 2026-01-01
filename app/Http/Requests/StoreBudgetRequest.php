<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBudgetRequest extends FormRequest
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
            'type' => ['required', 'in:Department,Project'],
            'name' => ['required', 'string', 'max:255'],
            'departmentId' => ['nullable', 'integer', 'exists:departments,id'],
            'projectId' => ['nullable', 'integer', 'exists:projects,id'],
            'budgetAmount' => ['required', 'numeric', 'min:0'],
            'actualAmount' => ['nullable', 'numeric', 'min:0'],
            'period' => ['required', 'in:Month,Quarter,Year'],
            'periodValue' => ['required', 'string', 'max:255'],
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'department_id' => $this->input('departmentId'),
            'project_id' => $this->input('projectId'),
            'budget_amount' => $this->input('budgetAmount'),
            'actual_amount' => $this->input('actualAmount') ?? 0,
            'period_value' => $this->input('periodValue'),
        ]);
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);
        
        return [
            'type' => $data['type'],
            'name' => $data['name'],
            'department_id' => $data['departmentId'] ?? null,
            'project_id' => $data['projectId'] ?? null,
            'budget_amount' => $data['budgetAmount'],
            'actual_amount' => $data['actualAmount'] ?? 0,
            'period' => $data['period'],
            'period_value' => $data['periodValue'],
            'created_by' => auth()->id(),
        ];
    }
}
