<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ExpenseReportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'reportDate' => $this['reportDate'],
            'dateRange' => [
                'from' => $this['dateRange']['from'],
                'to' => $this['dateRange']['to'],
            ],
            'summary' => [
                'totalExpenses' => $this['summary']['totalExpenses'],
                'totalExpenseCount' => $this['summary']['totalExpenseCount'],
                'totalItems' => $this['summary']['totalItems'],
                'byAccount' => $this['summary']['byAccount'],
                'byStatus' => $this['summary']['byStatus'],
            ],
            'expenses' => ExpenseResource::collection($this['expenses']),
            'generatedBy' => auth()->user()->email ?? 'system',
            'generatedAt' => now()->toIso8601String(),
        ];
    }
}
