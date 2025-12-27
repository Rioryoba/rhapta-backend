<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ExpenseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'account_id' => $this->account_id,
            'accountName' => $this->whenLoaded('account', fn() => optional($this->account)->account_name),
            'requestedBy' => $this->whenLoaded('requester', function () {
                return trim((optional($this->requester)->first_name ?? '') . ' ' . (optional($this->requester)->last_name ?? '')) ?: null;
            }),
            'receivedBy' => $this->whenLoaded('receiver', function () {
                return trim((optional($this->receiver)->first_name ?? '') . ' ' . (optional($this->receiver)->last_name ?? '')) ?: null;
            }),
            'description' => $this->description,
            'expenseDate' => $this->expense_date,
            'date' => $this->expense_date, // Alias for frontend compatibility
            'reference' => $this->reference,
            'subtotal' => $this->subtotal,
            'tax' => $this->tax,
            'discount' => $this->discount,
            'total' => $this->total,
            'amount' => $this->total ?? $this->amount, // Use total if available, fallback to amount
            'status' => $this->status,
            'site_id' => $this->site_id,
            'site' => $this->whenLoaded('site', fn() => optional($this->site)->name),
            'department_id' => $this->department_id,
            'department' => $this->whenLoaded('department', fn() => optional($this->department)->name),
            'category' => $this->category,
            'notes' => $this->description, // Alias for frontend
            'items' => ExpenseItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
