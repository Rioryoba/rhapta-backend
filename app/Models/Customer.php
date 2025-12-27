<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'company_name',
    ];

    public function contracts()
    {
        return $this->hasMany(Contract::class);
    }
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
    public function salesOrders()
    {
        return $this->hasMany(SalesOrder::class);
    }
}
