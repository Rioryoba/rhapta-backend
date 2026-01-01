<?php

namespace App\Filters;
use Illuminate\Http\Request;
use App\Filters\ApiFilter;

class EmployeeFilter extends ApiFilter
{
    protected $safeParms = [
        'salary' => ['eq', 'gt', 'lt'],
        'firstName' => ['eq'],
        'lastName' => ['eq'],
        'email' => ['eq'],
        'status' => ['eq'],
        'positionId' => ['eq'],
        'departmentId' => ['eq'],
    ];
    protected $columnMap = [
        'firstName' => 'first_name',
        'lastName' => 'last_name',
        'salary' => 'salary',
        'positionId' => 'position_id',
        'departmentId' => 'department_id',
    ];
    protected $operatorMap = [
        'eq' => '=',
        'gt' => '>',
        'lt' => '<',
    ];
    public function transform(Request $request)
    {
        $this->setModelQuery(\App\Models\Employee::query());
        return parent::transform($request);
    }
}