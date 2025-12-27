<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'month',
        'pay_date',
        'basic_salary',
        'allowances',
        'deductions',
        'net_salary',
        'status',
    ];

    protected $casts = [
        'pay_date' => 'date',
        'basic_salary' => 'integer',
        'allowances' => 'integer',
        'deductions' => 'integer',
        'net_salary' => 'integer',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
