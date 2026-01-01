<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SaleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'saleDate' => $this->sale_date,
            'mineSite' => $this->mine_site,
            'mineralType' => $this->mineral_type,
            'quantity' => (float) $this->quantity,
            'unitPrice' => (float) $this->unit_price,
            'totalAmount' => (float) $this->total_amount,
            'customerName' => $this->customer_name,
            'paymentStatus' => $this->payment_status,
            'region' => $this->region,
            'accountName' => $this->whenLoaded('account', fn() => optional($this->account)->account_name),
            'productName' => $this->whenLoaded('product', fn() => optional($this->product)->name),
            'description' => $this->description,
            'reference' => $this->reference,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
        ];
    }
}
