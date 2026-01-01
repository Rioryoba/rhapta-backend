<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpenseItem extends Model
{
    use HasFactory;

    protected $fillable = ['expense_id', 'description', 'quantity', 'unit_price', 'total', 'taxed'];

    public function expense()
    {
        return $this->belongsTo(Expense::class);
    }
}
