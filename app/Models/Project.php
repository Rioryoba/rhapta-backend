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
    public function tasks()
    {
        return $this->hasMany(Tasks::class, 'project_id');
    }
}
