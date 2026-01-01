<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DepartmentResource extends JsonResource
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
            'name' => $this->name,
            'description' => $this->description,
            'head' => $this->manager ? ($this->manager->first_name . ' ' . $this->manager->last_name) : null,
            'managerId' => $this->manager_id,
            'manager_id' => $this->manager_id, // Keep for backward compatibility
            'employeeCount' => $this->employees()->count(),
        ];
    }
}
