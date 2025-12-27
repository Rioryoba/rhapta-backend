<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JournalEntryResource extends JsonResource
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
            'date' => $this->date ? $this->date->format('Y-m-d') : null,
            'description' => $this->description,
            'debitAccount' => $this->debitAccount ? ($this->debitAccount->account_name ?? $this->debitAccount->name ?? null) : null,
            'debitAccountId' => $this->debit_account_id,
            'debitAccountCode' => $this->debitAccount ? ($this->debitAccount->code ?? $this->debitAccount->account_number ?? null) : null,
            'creditAccount' => $this->creditAccount ? ($this->creditAccount->account_name ?? $this->creditAccount->name ?? null) : null,
            'creditAccountId' => $this->credit_account_id,
            'creditAccountCode' => $this->creditAccount ? ($this->creditAccount->code ?? $this->creditAccount->account_number ?? null) : null,
            'amount' => (float) $this->amount,
            'reference' => $this->reference,
            'createdAt' => optional($this->created_at)?->toDateTimeString(),
            'updatedAt' => optional($this->updated_at)?->toDateTimeString(),
        ];
    }
}
