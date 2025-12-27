<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePositionRequest extends FormRequest
{
    protected function prepareForValidation()
    {
        $data = [];
        if ($this->has('departmentId')) $data['department_id'] = $this->input('departmentId');
        if ($this->has('title')) $data['title'] = $this->input('title');
        if ($this->has('description')) $data['description'] = $this->input('description');
        if (!empty($data)) {
            $this->merge($data);
        }
    }

    public function authorize(): bool
    {
        return auth()->check() && in_array(auth()->user()->role->name, ['admin', 'hr']);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255', 'unique:positions,title'],
            'description' => ['nullable', 'string'],
            'department_id' => ['required', 'exists:departments,id'],
        ];
    }
}
