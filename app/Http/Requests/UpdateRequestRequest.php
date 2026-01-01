<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequestRequest extends FormRequest
{
    protected function prepareForValidation()
    {
        $data = [];
    if ($this->has('employeeId')) $data['employee_id'] = $this->input('employeeId');
    if ($this->has('departmentId')) $data['department_id'] = $this->input('departmentId');
    if ($this->has('type')) $data['type'] = $this->input('type');
    if ($this->has('details')) $data['details'] = $this->input('details');
    if ($this->has('status')) $data['status'] = $this->input('status');
    if ($this->has('currentApproverId')) $data['current_approver_id'] = $this->input('currentApproverId');
    if ($this->has('approvedAt')) $data['approved_at'] = $this->input('approvedAt');
    if ($this->has('rejectedAt')) $data['rejected_at'] = $this->input('rejectedAt');
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
            'employee_id' => ['sometimes', 'exists:employees,id'],
            'department_id' => ['sometimes', 'exists:departments,id'],
            'type' => ['sometimes', 'string', 'max:255'],
            'details' => ['nullable', 'string'],
            'status' => ['sometimes', 'string'],
            'current_approver_id' => ['sometimes', 'exists:employees,id'],
            'approved_at' => ['nullable', 'date'],
            'rejected_at' => ['nullable', 'date'],
            'next_approver_role' => ['nullable', 'string', 'max:255'],
            'next_approver_type' => ['nullable', 'string', 'max:255'],
        ];
    }
}
