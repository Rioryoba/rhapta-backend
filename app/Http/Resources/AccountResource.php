<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AccountResource extends JsonResource
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
            'code' => $this->code ?? $this->account_number,
            'name' => $this->account_name,
            'category' => $this->category ?? ucfirst($this->account_type ?? 'Assets'),
            'type' => $this->type ?? ($this->account_type === 'asset' || $this->account_type === 'expense' ? 'Debit' : 'Credit'),
            'balance' => (float) $this->balance,
            'parentId' => $this->parent_id,
            'accountNumber' => $this->account_number,
            'accountName' => $this->account_name,
            'accountDescription' => $this->account_description,
            'accountType' => $this->account_type,
            'bankName' => $this->bank_name,
            'createdAt' => optional($this->created_at)?->toDateTimeString(),
            'updatedAt' => optional($this->updated_at)?->toDateTimeString(),
        ];
    }
}
