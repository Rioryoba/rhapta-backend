<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class RolePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Ensure role is loaded
        if (!$user->relationLoaded('role')) {
            $user->load('role');
        }
        // Allow admin and hr to view roles (hr needs this to assign roles to employees)
        return in_array(optional($user->role)->name, ['admin', 'hr']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Role $role): bool
    {
        // Ensure role is loaded
        if (!$user->relationLoaded('role')) {
            $user->load('role');
        }
        // Allow admin and hr to view a role
        return in_array(optional($user->role)->name, ['admin', 'hr']);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
    // Only allow users with 'admin' role to create roles
    return $user->role->name === 'admin';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Role $role): bool
    {
    // Only allow users with 'admin' role to update roles
    return $user->role->name === 'admin';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Role $role): bool
    {
    // Only allow users with 'admin' role to delete roles
    return $user->role->name === 'admin';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Role $role): bool
    {
    // Only allow users with 'admin' role to restore roles
    return $user->role->name === 'admin';
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Role $role): bool
    {
    // Only allow users with 'admin' role to permanently delete roles
    return $user->role->name === 'admin';
    }
}
