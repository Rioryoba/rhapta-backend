<?php

namespace App\Policies;

use App\Models\Attendence;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AttendencePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role->name, ['admin', 'hr']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Attendence $attendence): bool
    {
        return in_array($user->role->name, ['admin', 'hr']);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
    // Allow any authenticated user to check in
    return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Attendence $attendence): bool
    {
    // Allow any authenticated user to check out (update their attendance)
    return true;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Attendence $attendence): bool
    {
        return in_array($user->role->name, ['admin', 'hr']);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Attendence $attendence): bool
    {
        return in_array($user->role->name, ['admin', 'hr']);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Attendence $attendence): bool
    {
        return in_array($user->role->name, ['admin', 'hr']);
    }
}
