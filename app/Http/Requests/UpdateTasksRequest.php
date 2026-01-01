<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTasksRequest extends FormRequest
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
            'projectId' => ['nullable','integer','exists:projects,id'],
            'assignedTo' => ['nullable','integer','exists:employees,id'],
            'title' => ['sometimes','required','string','max:191'],
            'description' => ['nullable','string'],
            'startDate' => ['sometimes','required','date'],
            'endDate' => ['nullable','date','after_or_equal:startDate'],
            'status' => ['nullable','in:not_started,in_progress,completed,on_hold'],
        ];
    }

    protected function prepareForValidation()
    {
        // Convert empty strings to null for nullable fields
        $projectId = $this->input('projectId');
        $assignedTo = $this->input('assignedTo');
        
        $this->merge([
            'projectId' => $projectId === '' || $projectId === null ? null : $projectId,
            'assignedTo' => $assignedTo === '' || $assignedTo === null ? null : $assignedTo,
            'project_id' => $projectId === '' || $projectId === null ? null : $projectId,
            'assigned_to' => $assignedTo === '' || $assignedTo === null ? null : $assignedTo,
            'start_date' => $this->input('startDate'),
            'end_date' => $this->input('endDate'),
        ]);
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);
        
        // Map camelCase keys to snake_case for database
        // Only include fields that are actually present in the request
        $mapped = [];
        
        if (isset($data['projectId'])) {
            $mapped['project_id'] = $data['projectId'];
        }
        if (isset($data['assignedTo'])) {
            $mapped['assigned_to'] = $data['assignedTo'];
        }
        if (isset($data['title'])) {
            $mapped['title'] = $data['title'];
        }
        if (isset($data['description'])) {
            $mapped['description'] = $data['description'];
        }
        if (isset($data['startDate'])) {
            $mapped['start_date'] = $data['startDate'];
        }
        if (isset($data['endDate'])) {
            $mapped['end_date'] = $data['endDate'];
        }
        if (isset($data['status'])) {
            $mapped['status'] = $data['status'];
        }
        
        return $mapped;
    }
}
