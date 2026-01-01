<?php


namespace App\Policies;

use Illuminate\Support\Facades\Log;

use App\Models\Request;
use App\Models\User;

class RequestPolicy
{
	// Allow users with these roles to view any requests (for index/listing)
	public function viewAny(User $user): bool
	{
		Log::info('RequestPolicy@viewAny', ['user_id' => $user->id, 'role_id' => $user->role_id, 'role_name' => $user->role?->name]);
		return in_array($user->role?->name, ['admin', 'hr', 'manager', 'ceo', 'accountant', 'user', 'employee', 'staff']);
	}

	// Allow viewing if:
	// - The user is the employee who created the request
	// - The user is the current approver (manager/ceo/accountant)
	// - The user is admin or hr
	public function view(User $user, Request $request): bool
	{
		return ($user->employee_id && $user->employee_id === $request->employee_id)
			|| ($user->employee_id && $user->employee_id === $request->current_approver_id)
			|| in_array($user->role?->name, ['admin', 'hr']);
	}

	// Allow creation by all main roles and regular users
	public function create(User $user): bool
	{
		return in_array($user->role?->name, ['admin', 'hr', 'manager', 'ceo', 'accountant', 'user', 'employee', 'staff']);
	}

	// Allow update if:
	// - The user is the current approver (manager/ceo/accountant)
	// - The user is admin or hr
	public function update(User $user, Request $request): bool
	{
		return ($user->employee_id && $user->employee_id === $request->current_approver_id)
			|| in_array($user->role?->name, ['admin', 'hr']);
	}

	// Allow delete if:
	// - The user is the employee who created the request
	// - The user is admin
	public function delete(User $user, Request $request): bool
	{
		return ($user->employee_id && $user->employee_id === $request->employee_id)
			|| in_array($user->role?->name, ['admin']);
	}
}

