<?php

namespace App\Policies;

use App\Models\Leave;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Log;

class LeavePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Allow HR, admin, and managers to view all leaves
        return in_array($user->role?->name, ['hr', 'admin', 'manager']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Leave $leave): bool
    {
        // Allow viewing if:
        // - User is HR or admin
        // - User is the employee who created the leave
        // - User is a manager of the employee's department
        if (in_array($user->role?->name, ['hr', 'admin'])) {
            return true;
        }
        
        if ($user->employee_id && $user->employee_id === $leave->employee_id) {
            return true;
        }
        
        // Check if user is manager of the employee's department
        if ($user->employee_id && $leave->employee) {
            $department = $leave->employee->department;
            if ($department && $department->manager_id === $user->employee_id) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Allow all authenticated users to create leave requests
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Leave $leave): bool
    {
        \Log::info('LeavePolicy::update check', [
            'user_id' => $user->id,
            'user_role' => $user->role?->name,
            'user_employee_id' => $user->employee_id,
            'leave_id' => $leave->id,
            'leave_employee_id' => $leave->employee_id,
            'leave_status' => $leave->status,
        ]);
        
        // Allow updating if:
        // - User is HR or admin (can approve/reject any leave)
        if (in_array($user->role?->name, ['hr', 'admin'])) {
            \Log::info('LeavePolicy::update - allowed: HR or Admin');
            return true;
        }
        
        // Check if user is manager of the employee's department
        if ($user->employee_id && $leave->employee) {
            $leave->load('employee.department');
            $department = $leave->employee->department;
            if ($department && $department->manager_id === $user->employee_id) {
                \Log::info('LeavePolicy::update - allowed: Department Manager');
                return true;
            }
        }
        
        // Allow employee to edit their own pending leave
        if ($user->employee_id && $user->employee_id === $leave->employee_id) {
            if ($leave->status === 'pending') {
                \Log::info('LeavePolicy::update - allowed: Employee editing own pending leave');
                return true;
            }
        }
        
        \Log::warning('LeavePolicy::update - denied');
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Leave $leave): bool
    {
        // Allow deletion if:
        // - User is admin or HR
        // - User is the employee who created the leave (only if pending)
        if (in_array($user->role?->name, ['hr', 'admin'])) {
            return true;
        }
        
        if ($user->employee_id && $user->employee_id === $leave->employee_id) {
            return $leave->status === 'pending';
        }
        
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Leave $leave): bool
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Leave $leave): bool
    {
        //
    }
}
