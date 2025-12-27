<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PayrollResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Load employee if not already loaded
        if (!$this->relationLoaded('employee')) {
            $this->load('employee');
        }

        return [
            'id' => $this->id,
            'employeeId' => $this->employee_id,
            'employee' => $this->employee ? $this->employee->first_name . ' ' . $this->employee->last_name : null,
            'period' => $this->month,
            'payDate' => $this->pay_date?->format('Y-m-d'),
            'basicSalary' => $this->basic_salary,
            'allowances' => $this->allowances ?? 0,
            'deductions' => $this->deductions ?? 0,
            'netSalary' => $this->net_salary,
            'status' => ucfirst($this->status),
            'createdAt' => $this->created_at?->format('Y-m-d H:i:s'),
            'updatedAt' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}

