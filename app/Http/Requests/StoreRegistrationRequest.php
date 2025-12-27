<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRegistrationRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'user_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'role_id' => 'required|exists:roles,id',
            'employee_id' => 'nullable|exists:employees,id',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'user_name' => $this->input('userName'),
            'role_id' => $this->input('roleId'),   
            'employee_id' => $this->input('employeeId'),
        ]);
    }
}
