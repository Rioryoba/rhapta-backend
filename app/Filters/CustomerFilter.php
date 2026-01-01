<?php

namespace App\Filters;
use Illuminate\Http\Request;

class CustomerFilter extends ApiFilter
{
    protected $safeParms = [
        'name' => ['eq'],
        'email' => ['eq'],
        'hasInvoice' => ['eq'],
    ];
    protected $columnMap = [
        'name' => 'name',
        'email' => 'email',
    ];
    protected $operatorMap = [
        'eq' => '=',
    ];
    public function transform(Request $request)
    {
        $query = \App\Models\Customer::query();
        $query = parent::transform($request);
        if ($request->has('hasInvoice')) {
            $hasInvoice = $request->input('hasInvoice');
            if ($hasInvoice) {
                $query->whereHas('invoices');
            } else {
                $query->whereDoesntHave('invoices');
            }
        }
        return $query;
    }
}
