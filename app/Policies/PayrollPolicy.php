<?php

namespace App\Policies;

use App\Models\Payroll;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PayrollPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Allow admin, hr, and accountant to view payrolls
        return in_array(optional($user->role)->name, ['admin', 'hr', 'accountant']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Payroll $payroll): bool
    {
        // Allow admin, hr, and accountant to view payrolls
        return in_array(optional($user->role)->name, ['admin', 'hr', 'accountant']);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Allow admin, hr, and accountant to create payrolls
        return in_array(optional($user->role)->name, ['admin', 'hr', 'accountant']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Payroll $payroll): bool
    {
        // Allow admin, hr, and accountant to update payrolls
        return in_array(optional($user->role)->name, ['admin', 'hr', 'accountant']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Payroll $payroll): bool
    {
        // Only admin and hr can delete payrolls
        return in_array(optional($user->role)->name, ['admin', 'hr']);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Payroll $payroll): bool
    {
        // Only admin can restore payrolls
        return optional($user->role)->name === 'admin';
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Payroll $payroll): bool
    {
        // Only admin can permanently delete payrolls
        return optional($user->role)->name === 'admin';
    }
}
