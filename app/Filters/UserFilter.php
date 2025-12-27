<?php

namespace App\Filters;

use Illuminate\Http\Request;
use App\Filters\ApiFilter;

class UserFilter extends ApiFilter
{
    protected $safeParms = [
        'userName' => ['eq'],
        'email' => ['eq'],
        'roleId' => ['eq'],
        'employeeId' => ['eq'],
    ];
    protected $columnMap = [
        'userName' => 'name',
        'roleId' => 'role_id',
        'employeeId' => 'employee_id',
    ];
    protected $operatorMap = [
        'eq' => '=',
    ];
    public function transform(Request $request)
    {
        $this->setModelQuery(\App\Models\User::query());
        return parent::transform($request);
    }
}
