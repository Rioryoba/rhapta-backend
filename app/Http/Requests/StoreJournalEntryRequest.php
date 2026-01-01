<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreJournalEntryRequest extends FormRequest
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
            'date' => 'required|date',
            'description' => 'required|string|max:500',
            'debitAccount' => 'required|exists:accounts,id',
            'creditAccount' => 'required|exists:accounts,id|different:debitAccount',
            'amount' => 'required|numeric|min:0.01',
            'reference' => 'nullable|string|max:100',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'debit_account_id' => $this->input('debitAccount'),
            'credit_account_id' => $this->input('creditAccount'),
        ]);
    }
}
