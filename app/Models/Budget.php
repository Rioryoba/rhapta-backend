<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'name',
        'department_id',
        'project_id',
        'budget_amount',
        'actual_amount',
        'period',
        'period_value',
        'created_by',
    ];

    protected $casts = [
        'budget_amount' => 'decimal:2',
        'actual_amount' => 'decimal:2',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
