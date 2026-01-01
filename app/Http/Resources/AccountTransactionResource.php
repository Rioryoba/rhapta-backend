<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AccountTransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'accountId' => $this->account_id,
            'accountName' => $this->whenLoaded('account', fn() => optional($this->account)->account_name),
            'type' => $this->type,
            'amount' => $this->amount,
            'balanceAfter' => $this->balance_after,
            'referenceType' => $this->reference_type,
            'description' => $this->description,
            'createdAt' => $this->created_at,
        ];
    }
}
