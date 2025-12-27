<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Request extends Model
{
	use HasFactory;

	protected $fillable = [
		'employee_id',
		'department_id',
		'type',
		'details',
		'status',
		'current_approver_id',
		'next_approver_role',
		'next_approver_type',
		'approved_at',
		'rejected_at',
	];

	public function employee()
	{
		return $this->belongsTo(Employee::class, 'employee_id');
	}

	public function department()
	{
		return $this->belongsTo(Department::class, 'department_id');
	}

	public function currentApprover()
	{
		return $this->belongsTo(Employee::class, 'current_approver_id');
	}
}

