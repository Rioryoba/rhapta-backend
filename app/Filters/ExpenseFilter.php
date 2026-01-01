<?php

namespace App\Filters;
use Illuminate\Http\Request;
use App\Filters\ApiFilter;

class ExpenseFilter extends ApiFilter
{
    protected $safeParms = [
        'amount' => ['eq', 'gt', 'lt'],
        'status' => ['eq'],
        'accountId' => ['eq'],
        'requestedBy' => ['eq'],
        'receivedBy' => ['eq'],
        'expenseDate' => ['from', 'to'],
        'reference' => ['eq'],
    ];

    protected $columnMap = [
        'accountId' => 'account_id',
        'requestedBy' => 'requested_by',
        'receivedBy' => 'received_by',
        'expenseDate' => 'expense_date',
    ];

    protected $operatorMap = [
        'eq' => '=',
        'gt' => '>',
        'lt' => '<',
        'from' => '>=',
        'to' => '<=',
    ];

    public function transform(Request $request)
    {
        $this->setModelQuery(\App\Models\Expense::query());
        return parent::transform($request);
    }
}
