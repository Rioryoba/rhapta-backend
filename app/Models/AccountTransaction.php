<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id', 'type', 'amount', 'balance_after', 'reference_type', 'reference_id', 'description', 'created_by'
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
