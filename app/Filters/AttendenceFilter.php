<?php

namespace App\Filters;

use Illuminate\Http\Request;
use App\Filters\ApiFilter;

class AttendenceFilter extends ApiFilter
{
    protected $safeParms = [
        'employeeId' => ['eq'],
        'date' => ['eq', 'gt', 'lt', 'gte'],
        'status' => ['eq'],
        'biometric' => ['eq'],
    ];
    protected $columnMap = [
        'employeeId' => 'employee_id',
        'biometric' => 'biometric',
    ];
    protected $operatorMap = [
        'eq' => '=',
        'gt' => '>',
        'lt' => '<',
        'gte' => '>=',
    ];
    public function transform(Request $request)
    {
        $this->setModelQuery(\App\Models\Attendence::query());
        return parent::transform($request);
    }
}
