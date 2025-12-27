<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PositionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
            return [
                'positionId' => $this->id,
                'departmentId' => $this->whenLoaded('department', fn() => $this->department?->id),
                'departmentName' => $this->whenLoaded('department', fn() => $this->department?->name),
                'title' => $this->title,
                'description' => $this->description,
            ];
    }
}
