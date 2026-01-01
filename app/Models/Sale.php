<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'product_id',
        'quantity',
        'description',
        'sale_date',
        'reference',
        'mine_site',
        'mineral_type',
        'unit_price',
        'total_amount',
        'customer_name',
        'payment_status',
        'region',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
