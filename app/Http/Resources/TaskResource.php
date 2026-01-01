<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'projectId' => $this->project_id,
            'assignedTo' => $this->assigned_to,
            'assignedToName' => optional($this->assignedTo)->first_name ? trim(optional($this->assignedTo)->first_name . ' ' . optional($this->assignedTo)->last_name) : null,
            'title' => $this->title,
            'description' => $this->description,
            'startDate' => $this->start_date ? (is_string($this->start_date) ? $this->start_date : $this->start_date->format('Y-m-d')) : null,
            'endDate' => $this->end_date ? (is_string($this->end_date) ? $this->end_date : $this->end_date->format('Y-m-d')) : null,
            'status' => $this->status,
        ];
    }
}
