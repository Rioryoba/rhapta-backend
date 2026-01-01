<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyActivity extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'project_id',
        'employee_id',
        'submission_date',
        'activity_description',
        'materials_used',
        'issues_challenges',
        'status',
        'supervisor_comments',
    ];

    protected $casts = [
        'submission_date' => 'date',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}

