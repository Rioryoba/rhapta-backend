<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePerformanceRequest extends FormRequest
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
        return [
            'employee_id' => 'sometimes|exists:employees,id',
            'period' => 'sometimes|string|max:255',
            'rating' => 'sometimes|numeric|min:1|max:5',
            'goals' => 'sometimes|string',
            'feedback' => 'nullable|string',
            'status' => 'sometimes|in:In Progress,Completed',
        ];
    }
}
