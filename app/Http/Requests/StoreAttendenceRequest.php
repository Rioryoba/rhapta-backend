<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAttendenceRequest extends FormRequest
{
    protected function prepareForValidation()
    {
        $data = [];
        if ($this->has('employeeId')) $data['employee_id'] = $this->input('employeeId');
        if ($this->has('date')) $data['date'] = $this->input('date');
        if ($this->has('checkIn')) $data['check_in'] = $this->input('checkIn');
        if ($this->has('checkOut')) $data['check_out'] = $this->input('checkOut');
        if ($this->has('status')) $data['status'] = $this->input('status');
        if ($this->has('biometric')) $data['biometric'] = $this->input('biometric');
        if (!empty($data)) {
            $this->merge($data);
        }
    }

    public function authorize(): bool
    {
    return auth()->check();
    }

    public function rules(): array
    {
        return [
            'employee_id' => ['nullable', 'exists:employees,id'],
            'date' => ['nullable', 'date'],
            'check_in' => ['nullable', 'date_format:H:i:s'],
            'check_out' => ['nullable', 'date_format:H:i:s'],
            'status' => ['nullable', 'in:present,absent,leave,late'],
            'biometric' => ['nullable', 'string', 'max:255'],
        ];
    }
}
