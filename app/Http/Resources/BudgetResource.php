<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BudgetResource extends JsonResource
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
            'type' => $this->type,
            'name' => $this->name,
            'department_id' => $this->department_id,
            'project_id' => $this->project_id,
            'budgetAmount' => (float) $this->budget_amount,
            'actualAmount' => (float) $this->actual_amount,
            'period' => $this->period,
            'periodValue' => $this->period_value,
            'created_at' => $this->created_at ? $this->created_at->format('Y-m-d') : null,
            'department' => $this->whenLoaded('department', fn() => optional($this->department)->name),
            'project' => $this->whenLoaded('project', fn() => optional($this->project)->name),
        ];
    }
}
