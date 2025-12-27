<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RequestResource extends JsonResource
{
	/**
	 * Transform the resource into an array.
	 *
	 * @return array<string, mixed>
	 */
	public function toArray($request): array
	{
		return [
			'requestId' => $this->id,
			'employeeId' => $this->employee_id,
			'employeeName' => $this->employee ? $this->employee->first_name . ' ' . $this->employee->last_name : null,
			'departmentId' => $this->department_id,
			'departmentName' => $this->department ? $this->department->name : null,
			'type' => $this->type,
			'details' => $this->details,
			'status' => $this->status,
			'currentApproverId' => $this->current_approver_id,
			'currentApproverName' => $this->currentApprover ? $this->currentApprover->first_name . ' ' . $this->currentApprover->last_name : null,
			   'nextApproverRole' => $this->next_approver_role,
			   'nextApproverType' => $this->next_approver_type,
			'approvedAt' => $this->approved_at,
			'rejectedAt' => $this->rejected_at,

		];
	}
}