<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSaleRequest extends FormRequest
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
            'saleDate' => ['sometimes', 'required', 'date'],
            'mineSite' => ['nullable', 'string', 'max:255'],
            'mineralType' => ['nullable', 'string', 'max:255'],
            'quantity' => ['sometimes', 'required', 'numeric', 'min:0'],
            'unitPrice' => ['sometimes', 'required', 'numeric', 'min:0'],
            'customerName' => ['sometimes', 'required', 'string', 'max:255'],
            'paymentStatus' => ['sometimes', 'required', 'in:Paid,Pending,Overdue'],
            'region' => ['nullable', 'string', 'max:255'],
            'account_id' => ['nullable', 'exists:accounts,id'],
            'product_id' => ['nullable', 'exists:products,id'],
            'description' => ['nullable', 'string'],
            'reference' => ['nullable', 'string'],
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'sale_date' => $this->input('saleDate'),
            'mine_site' => $this->input('mineSite'),
            'mineral_type' => $this->input('mineralType'),
            'unit_price' => $this->input('unitPrice'),
            'total_amount' => ($this->input('quantity', 0) * $this->input('unitPrice', 0)),
            'customer_name' => $this->input('customerName'),
            'payment_status' => $this->input('paymentStatus'),
            'region' => $this->input('region'),
            'account_id' => $this->input('accountId'),
            'product_id' => $this->input('productId'),
        ]);
    }
}
