<?php

namespace App\Policies;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Auth\Access\Response;

use Illuminate\Support\Facades\Log;

class EmployeePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
    Log::debug('EmployeePolicy: viewAny entered', [
            'user_id' => $user->id,
            'user_email' => $user->email,
        ]);
        Log::debug('EmployeePolicy: viewAny called', [
            'role' => $user->role,
            'role_name' => optional($user->role)->name,
        ]);
        // Allow accountants to view employees for payroll purposes
        return in_array(optional($user->role)->name, ['hr', 'admin', 'accountant']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Employee $employee): bool
    {
    Log::info('User role for policy check (view):', ['role' => $user->role]);
    // Allow accountants to view employees for payroll purposes
    return in_array($user->role->name, ['hr', 'admin', 'accountant']);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
    Log::info('User role for policy check (create):', ['role' => $user->role]);
    return in_array($user->role->name, ['hr', 'admin']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Employee $employee): bool
    {
    Log::info('User role for policy check (update):', ['role' => $user->role]);
    return in_array($user->role->name, ['hr', 'admin']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Employee $employee): bool
    {
    Log::info('User role for policy check (delete):', ['role' => $user->role]);
    return in_array($user->role->name, ['hr', 'admin']);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Employee $employee): bool
    {
    Log::info('User role for policy check (restore):', ['role' => $user->role]);
    return in_array($user->role->name, ['hr', 'admin']);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Employee $employee): bool
    {
    Log::info('User role for policy check (forceDelete):', ['role' => $user->role]);
    return in_array($user->role->name, ['hr', 'admin']);
    }
}
