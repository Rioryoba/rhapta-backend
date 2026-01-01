<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectRequest extends FormRequest
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
            'name' => ['required','string','max:191'],
            'description' => ['nullable','string'],
            'startDate' => ['required','date'],
            'endDate' => ['nullable','date','after_or_equal:startDate'],
            'managerId' => ['nullable','integer','exists:employees,id'],
            'departmentId' => ['nullable','integer','exists:departments,id'],
            'status' => ['nullable','in:not_started,in_progress,completed,on_hold'],
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'start_date' => $this->input('startDate'),
            'end_date' => $this->input('endDate'),
            'manager_id' => $this->input('managerId'),
            'department_id' => $this->input('departmentId'),
        ]);
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);
        
        // Map camelCase keys to snake_case for database
        return [
            'name' => $data['name'] ?? null,
            'description' => $data['description'] ?? null,
            'start_date' => $data['startDate'] ?? null,
            'end_date' => $data['endDate'] ?? null,
            'manager_id' => $data['managerId'] ?? null,
            'department_id' => $data['departmentId'] ?? null,
            'status' => $data['status'] ?? null,
        ];
    }
}
