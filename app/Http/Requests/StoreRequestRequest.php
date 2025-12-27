<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequestRequest extends FormRequest
{
    protected function prepareForValidation()
    {
        $data = [];
    if ($this->has('employeeId')) $data['employee_id'] = $this->input('employeeId');
    if ($this->has('departmentId')) $data['department_id'] = $this->input('departmentId');
    if ($this->has('type')) $data['type'] = $this->input('type');
    if ($this->has('details')) $data['details'] = $this->input('details');
    if ($this->has('nextApproverRole')) $data['next_approver_role'] = $this->input('nextApproverRole');
    if ($this->has('nextApproverType')) $data['next_approver_type'] = $this->input('nextApproverType');
        if (!empty($data)) {
            $this->merge($data);
        }
    }

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'employee_id' => ['nullable', 'exists:employees,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'type' => ['required', 'string', 'max:255'],
            'details' => ['nullable', 'string'],
            'next_approver_role' => ['nullable', 'string', 'max:255'],
            'next_approver_type' => ['nullable', 'string', 'max:255'],
        ];
    }
}
