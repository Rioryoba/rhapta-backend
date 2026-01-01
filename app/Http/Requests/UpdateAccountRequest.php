<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAccountRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $accountId = $this->route('account')?->id ?? null;
        $method = $this->method();

        if ($method === 'PUT') {
            return [
                'code' => [
                    'sometimes',
                    'string',
                    'max:50',
                    Rule::unique('accounts', 'code')->ignore($accountId),
                ],
                'name' => 'sometimes|string|max:255',
                'category' => 'sometimes|in:Assets,Liabilities,Equity,Income,Expenses',
                'type' => 'sometimes|in:Debit,Credit',
                'balance' => 'nullable|numeric|min:0',
                'parentId' => 'nullable|exists:accounts,id',
                // Legacy fields
                'account_number' => [
                    'sometimes',
                    'integer',
                    Rule::unique('accounts', 'account_number')->ignore($accountId),
                ],
                'account_name' => 'sometimes|string|max:255',
                'account_description' => 'nullable|string',
                'account_type' => 'sometimes|in:asset,liability,equity,revenue,expense',
                'bank_name' => 'nullable|string|max:255',
            ];
        } else if ($method === 'PATCH') {
            return [
                'code' => [
                    'sometimes',
                    'string',
                    'max:50',
                    Rule::unique('accounts', 'code')->ignore($accountId),
                ],
                'name' => 'sometimes|string|max:255',
                'category' => 'sometimes|in:Assets,Liabilities,Equity,Income,Expenses',
                'type' => 'sometimes|in:Debit,Credit',
                'balance' => 'nullable|numeric|min:0',
                'parentId' => 'nullable|exists:accounts,id',
                // Legacy fields
                'account_number' => [
                    'sometimes',
                    'integer',
                    Rule::unique('accounts', 'account_number')->ignore($accountId),
                ],
                'account_name' => 'sometimes|string|max:255',
                'account_description' => 'nullable|string',
                'account_type' => 'sometimes|in:asset,liability,equity,revenue,expense',
                'bank_name' => 'nullable|string|max:255',
            ];
        }
        return [];
    }

    /**
     * Prepare input data for validation by converting camelCase to snake_case.
     * Only merge fields that are actually present in the request.
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
