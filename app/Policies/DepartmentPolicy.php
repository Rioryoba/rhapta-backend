<?php

namespace App\Policies;

use App\Models\Department;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class DepartmentPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
    // Allow users with 'admin' or 'hr' role to view departments
    return in_array($user->role->name, ['admin', 'hr']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Department $department): bool
    {
    // Allow users with 'admin' or 'hr' role to view a department
    return in_array($user->role->name, ['admin', 'hr']);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
    // Only allow users with 'admin' or 'hr' role to create departments
    return in_array($user->role->name, ['admin', 'hr']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Department $department): bool
    {
    // Only allow users with 'admin' or 'hr' role to update departments
    return in_array($user->role->name, ['admin', 'hr']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Department $department): bool
    {
    // Only allow users with 'admin' or 'hr' role to delete departments
    return in_array($user->role->name, ['admin', 'hr']);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Department $department): bool
    {
    // Only allow users with 'admin' or 'hr' role to restore departments
    return in_array($user->role->name, ['admin', 'hr']);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Department $department): bool
    {
    // Only allow users with 'admin' or 'hr' role to permanently delete departments
    return in_array($user->role->name, ['admin', 'hr']);
    }
}
