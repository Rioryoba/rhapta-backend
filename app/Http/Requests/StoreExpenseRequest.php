<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExpenseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Allow authenticated users; policy/controller will enforce roles
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
            'account_id' => ['required', 'exists:accounts,id'],
            'expense_date' => ['required', 'date'],
            'description' => ['nullable', 'string'],
            'reference' => ['nullable', 'string'],
            'requested_by' => ['nullable', 'exists:employees,id'],
            'received_by' => ['nullable', 'exists:employees,id'],
            'site_id' => ['nullable', 'exists:sites,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'category' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unitPrice' => ['required', 'numeric', 'min:0'],
            'items.*.taxed' => ['nullable', 'boolean'],
            'tax' => ['nullable', 'numeric', 'min:0'],
            'discount' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'account_id' => $this->input('accountId') ?? $this->input('account_id'),
            'expense_date' => $this->input('expenseDate') ?? $this->input('expense_date') ?? $this->input('date'),
            'requested_by' => $this->input('requestedBy') ?? $this->input('requested_by'),
            'received_by' => $this->input('receivedBy') ?? $this->input('received_by'),
            'site_id' => $this->input('siteId') ?? $this->input('site_id'),
            'department_id' => $this->input('departmentId') ?? $this->input('department_id'),
            'category' => $this->input('category'),
            'items' => $this->input('items'),
            'tax' => $this->input('tax'),
            'discount' => $this->input('discount'),
            'description' => $this->input('description') ?? $this->input('notes'),
            'reference' => $this->input('reference'),
        ]);
    }
}
