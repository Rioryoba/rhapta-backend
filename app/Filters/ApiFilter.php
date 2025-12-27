<?php

namespace App\Filters;
use Illuminate\Http\Request;

class ApiFilter
{
    protected $safeParms = [];
    protected $columnMap = [];
    protected $operatorMap = [];
    protected $modelQuery;

    public function setModelQuery($query)
    {
        $this->modelQuery = $query;
    }

    public function transform(Request $request)
    {
        $eloQuery = $this->modelQuery;
        foreach ($this->safeParms as $parm => $operators) {
            $query = $request->query($parm);
            if (!isset($query)) {
                continue;
            }
            $column = $this->columnMap[$parm] ?? $parm;
            foreach ($operators as $operator) {
                if (isset($query[$operator])) {
                    $eloQuery->where($column, $this->operatorMap[$operator], $query[$operator]);
                }
            }
        }
        return $eloQuery;
    }
}