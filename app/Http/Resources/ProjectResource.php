<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\TaskResource;

class ProjectResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'startDate' => $this->start_date ? (is_string($this->start_date) ? $this->start_date : $this->start_date->format('Y-m-d')) : null,
            'endDate' => $this->end_date ? (is_string($this->end_date) ? $this->end_date : $this->end_date->format('Y-m-d')) : null,
            'managerId' => $this->manager_id,
            'managerName' => optional($this->manager)->first_name ? trim(optional($this->manager)->first_name . ' ' . optional($this->manager)->last_name) : null,
            'departmentId' => $this->department_id,
            'departmentName' => optional($this->department)->name ?? null,
            'status' => $this->status,
            'tasks' => TaskResource::collection($this->whenLoaded('tasks')),
        ];
    }
}
