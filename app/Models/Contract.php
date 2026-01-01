<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    use HasFactory;

    public function type()
    {
        return $this->belongsTo(ContractType::class, 'type_id');
    }
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
    public function position()
    {
        return $this->belongsTo(Position::class);
    }
}
