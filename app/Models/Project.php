<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'description',
        'start_date',
        'end_date',
        'manager_id',
        'department_id',
        'status',
    ];

    public function manager()
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }
    public function department()
    {
        return $this->belongsTo(Department::class);
    }
    public function activities()
    {
        return $this->hasMany(Activity::class, 'project_id');
    }
    
    // Keep tasks() as an alias for backward compatibility if needed
    public function tasks()
    {
        return $this->activities();
    }
}
