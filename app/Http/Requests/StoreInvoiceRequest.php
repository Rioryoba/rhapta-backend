<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInvoiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
    return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'invoiceNo' => ['nullable', 'string', 'max:50', 'unique:invoices,invoice_no'],
            'customerId' => ['nullable', 'exists:customers,id'],
            'customerName' => ['nullable', 'string', 'max:255', 'required_without:customerId', 'min:1'],
            'invoiceDate' => ['required', 'date'],
            'dueDate' => ['required', 'date'],
            'status' => ['required', 'in:unpaid,paid,overdue'],
            'total' => ['required', 'numeric', 'min:0'],
            'subtotal' => ['nullable', 'numeric', 'min:0'],
            'tax' => ['nullable', 'numeric', 'min:0'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'items' => ['nullable', 'array'],
            'items.*.description' => ['required_with:items', 'string'],
            'items.*.quantity' => ['required_with:items', 'integer', 'min:1'],
            'items.*.unitPrice' => ['required_with:items', 'numeric', 'min:0'],
        ];
    }
}
