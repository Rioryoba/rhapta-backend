<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    public function toArray($request)
    {
        // Map status to match frontend expectations
        $statusMap = [
            'unpaid' => 'Pending',
            'paid' => 'Paid',
            'overdue' => 'Overdue',
        ];

        return [
            'id' => $this->id,
            'invoiceNumber' => $this->invoice_no,
            'invoiceNo' => $this->invoice_no,
            'customerId' => $this->customer_id,
            'customerName' => $this->customer ? $this->customer->name : '',
            'dateIssued' => $this->invoice_date,
            'invoiceDate' => $this->invoice_date,
            'dueDate' => $this->due_date,
            'amount' => (float) $this->total,
            'subtotal' => (float) $this->subtotal,
            'tax' => (float) $this->tax,
            'discount' => (float) $this->discount,
            'total' => (float) $this->total,
            'paymentStatus' => $statusMap[$this->status] ?? $this->status,
            'status' => $this->status,
            'saleId' => null, // Can be added if there's a relationship
            'createdBy' => $this->created_by,
            'invoiceItems' => \App\Http\Resources\InvoiceItemResource::collection($this->whenLoaded('invoiceItems')),
        ];
    }
}
