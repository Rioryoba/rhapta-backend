<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTasksRequest extends FormRequest
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
            'projectId' => ['required','integer','exists:projects,id'],
            'assignedTo' => ['nullable','integer','exists:employees,id'],
            'title' => ['required','string','max:191'],
            'description' => ['nullable','string'],
            'startDate' => ['required','date'],
            'endDate' => ['nullable','date','after_or_equal:startDate'],
            'status' => ['nullable','in:not_started,in_progress,completed,on_hold'],
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'project_id' => $this->input('projectId'),
            'assigned_to' => $this->input('assignedTo'),
            'start_date' => $this->input('startDate'),
            'end_date' => $this->input('endDate'),
        ]);
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);
        
        // Map camelCase keys to snake_case for database
        return [
            'project_id' => $data['projectId'] ?? null,
            'assigned_to' => $data['assignedTo'] ?? null,
            'title' => $data['title'] ?? null,
            'description' => $data['description'] ?? null,
            'start_date' => $data['startDate'] ?? null,
            'end_date' => $data['endDate'] ?? null,
            'status' => $data['status'] ?? null,
        ];
    }
}
