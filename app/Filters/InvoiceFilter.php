<?php

namespace App\Filters;
use Illuminate\Http\Request;

class InvoiceFilter extends ApiFilter
{
    protected $safeParms = [
        'invoiceNo' => ['eq'],
        'customerId' => ['eq'],
        'status' => ['eq'],
        'invoiceDate' => ['eq', 'gt', 'lt', 'gte'],
        'dueDate' => ['eq', 'gt', 'lt', 'gte'],
    ];
    protected $columnMap = [
        'invoiceNo' => 'invoice_no',
        'customerId' => 'customer_id',
        'invoiceDate' => 'invoice_date',
        'dueDate' => 'due_date',
    ];
    protected $operatorMap = [
        'eq' => '=',
        'gt' => '>',
        'lt' => '<',
        'gte' => '>=',
    ];
    public function transform(Request $request)
    {
        $this->setModelQuery(\App\Models\Invoice::query());
        return parent::transform($request);
    }
}
