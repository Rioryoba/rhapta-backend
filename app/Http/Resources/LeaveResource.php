<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeaveResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'employee' => $this->whenLoaded('employee', function () {
                return $this->employee ? trim($this->employee->first_name . ' ' . $this->employee->last_name) : null;
            }),
            'start_date' => $this->start_date ? (is_string($this->start_date) ? $this->start_date : $this->start_date->format('Y-m-d')) : null,
            'startDate' => $this->start_date ? (is_string($this->start_date) ? $this->start_date : $this->start_date->format('Y-m-d')) : null,
            'end_date' => $this->end_date ? (is_string($this->end_date) ? $this->end_date : $this->end_date->format('Y-m-d')) : null,
            'endDate' => $this->end_date ? (is_string($this->end_date) ? $this->end_date : $this->end_date->format('Y-m-d')) : null,
            'type' => $this->type,
            'status' => ucfirst($this->status), // Capitalize first letter for frontend
            'days' => $this->days ?? 0,
        ];
    }
}









