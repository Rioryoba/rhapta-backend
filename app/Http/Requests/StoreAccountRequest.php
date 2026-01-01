<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAccountRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Allow authenticated users; policy/controller will enforce roles (same behavior as StoreExpenseRequest)
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
            'code' => 'required|string|max:50|unique:accounts,code',
            'name' => 'required|string|max:255',
            'category' => 'required|in:Assets,Liabilities,Equity,Income,Expenses',
            'type' => 'required|in:Debit,Credit',
            'balance' => 'nullable|numeric|min:0',
            'parentId' => 'nullable|exists:accounts,id',
            // Legacy fields (optional)
            'account_number' => 'nullable|integer|unique:accounts,account_number',
            'account_name' => 'nullable|string|max:255',
            'account_description' => 'nullable|string',
            'account_type' => 'nullable|in:asset,liability,equity,revenue,expense',
            'bank_name' => 'nullable|string|max:255',
        ];
    }

    /**
     * Prepare input data for validation by converting camelCase to snake_case.
     */
    protected function prepareForValidation()
    {
        $data = [];
        
        // New fields
        if ($this->has('code')) {
            $data['code'] = $this->input('code');
        }
        if ($this->has('name')) {
            $data['account_name'] = $this->input('name');
        }
        if ($this->has('category')) {
            $data['category'] = $this->input('category');
        }
        if ($this->has('type')) {
            $data['type'] = $this->input('type');
        }
        if ($this->has('balance')) {
            $data['balance'] = $this->input('balance');
        }
        if ($this->has('parentId')) {
            $data['parent_id'] = $this->input('parentId');
        }
        
        // Legacy fields
        if ($this->has('accountNumber')) {
            $data['account_number'] = $this->input('accountNumber');
        }
        if ($this->has('accountName')) {
            $data['account_name'] = $this->input('accountName');
        }
        if ($this->has('accountDescription')) {
            $data['account_description'] = $this->input('accountDescription');
        }
        if ($this->has('accountType')) {
            $data['account_type'] = $this->input('accountType');
        }
        if ($this->has('bankName')) {
            $data['bank_name'] = $this->input('bankName');
        }

        if (!empty($data)) {
            $this->merge($data);
        }
    }
}
