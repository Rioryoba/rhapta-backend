<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDepartmentRequest extends FormRequest
{
    protected function prepareForValidation()
    {
        $data = [];
        // Handle both camelCase and snake_case for manager ID
        if ($this->has('managerId')) {
            $managerId = $this->input('managerId');
            $data['manager_id'] = ($managerId === '' || $managerId === null) ? null : $managerId;
        } elseif ($this->has('manager_id')) {
            $managerId = $this->input('manager_id');
            $data['manager_id'] = ($managerId === '' || $managerId === null) ? null : $managerId;
        }
        if ($this->has('name')) $data['name'] = $this->input('name');
        if ($this->has('description')) $data['description'] = $this->input('description');
        if (!empty($data)) {
            $this->merge($data);
        }
    }
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            return false;
        }
        
        // Load role if not already loaded
        $user = auth()->user();
        if (!$user->relationLoaded('role')) {
            $user->load('role');
        }
        
        // Only allow admin or hr
        $roleName = $user->role?->name;
        return $roleName && in_array($roleName, ['admin', 'hr']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:departments,name'],
            'description' => ['nullable', 'string'],
            'manager_id' => ['nullable', 'exists:employees,id'],
        ];
    }
}
