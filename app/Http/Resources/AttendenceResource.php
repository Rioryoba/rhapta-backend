<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendenceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'attendenceId' => $this->id,
            'employeeId' => $this->employee_id,
            'employeeName' => $this->employee?->first_name . ' ' . $this->employee?->last_name,
            'date' => $this->date,
            'checkIn' => $this->check_in,
            'checkOut' => $this->check_out,
            'status' => $this->status,
            'biometric' => $this->biometric,
        ];
    }
}
