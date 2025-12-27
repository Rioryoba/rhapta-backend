<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBudgetRequest extends FormRequest
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
            'type' => ['sometimes', 'required', 'in:Department,Project'],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'departmentId' => ['nullable', 'integer', 'exists:departments,id'],
            'projectId' => ['nullable', 'integer', 'exists:projects,id'],
            'budgetAmount' => ['sometimes', 'required', 'numeric', 'min:0'],
            'actualAmount' => ['nullable', 'numeric', 'min:0'],
            'period' => ['sometimes', 'required', 'in:Month,Quarter,Year'],
            'periodValue' => ['sometimes', 'required', 'string', 'max:255'],
        ];
    }

    protected function prepareForValidation()
    {
        $data = [];
        if ($this->has('departmentId')) {
            $data['department_id'] = $this->input('departmentId');
        }
        if ($this->has('projectId')) {
            $data['project_id'] = $this->input('projectId');
        }
        if ($this->has('budgetAmount')) {
            $data['budget_amount'] = $this->input('budgetAmount');
        }
        if ($this->has('actualAmount')) {
            $data['actual_amount'] = $this->input('actualAmount');
        }
        if ($this->has('periodValue')) {
            $data['period_value'] = $this->input('periodValue');
        }
        
        if (!empty($data)) {
            $this->merge($data);
        }
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);
        $result = [];
        
        if (isset($data['type'])) $result['type'] = $data['type'];
        if (isset($data['name'])) $result['name'] = $data['name'];
        if (isset($data['departmentId'])) $result['department_id'] = $data['departmentId'];
        if (isset($data['projectId'])) $result['project_id'] = $data['projectId'];
        if (isset($data['budgetAmount'])) $result['budget_amount'] = $data['budgetAmount'];
        if (isset($data['actualAmount'])) $result['actual_amount'] = $data['actualAmount'];
        if (isset($data['period'])) $result['period'] = $data['period'];
        if (isset($data['periodValue'])) $result['period_value'] = $data['periodValue'];
        
        return $result;
    }
}
