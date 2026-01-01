<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgressUpdate extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'employee_id',
        'progress_description',
        'time_spent',
        'remarks',
        'update_date',
        'attachments',
    ];

    protected $casts = [
        'attachments' => 'array',
        'update_date' => 'date',
        'time_spent' => 'decimal:2',
    ];

    public function task()
    {
        return $this->belongsTo(Tasks::class, 'task_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
