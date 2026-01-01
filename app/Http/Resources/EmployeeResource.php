<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'employeeId' => $this->id,
            'firstName' => $this->first_name,
            'lastName' => $this->last_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'dateOfBirth' => $this->date_of_birth,
            'gender' => $this->gender,
            'salary' => $this->salary,
            'position' => $this->whenLoaded('position', fn() => $this->position?->title),
            'positionId' => $this->position_id,
            'department' => $this->whenLoaded('department', fn() => $this->department?->name),
            'departmentId' => $this->department_id,
            'hireDate' => $this->hire_date,
            'status' => $this->status,
            'roleId' => $this->whenLoaded('user.role', fn() => $this->user?->role_id),
            'hasUserAccount' => $this->whenLoaded('user', fn() => $this->user !== null),
            'profilePicture' => $this->profile_picture ? asset('storage/' . $this->profile_picture) : null,
        ];
    }
}
