<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function before($authUser, $ability)
    {
        // Only admin can manage users
        return $authUser->role?->name === 'admin';
    }

    // Allow all users to set their own password
    public function setPassword(User $user, User $model): bool
    {
        return $user->id === $model->id;
    }

    public function viewAny(User $user): bool { return true; }
    public function view(User $user, User $model): bool { return true; }
    public function create(User $user): bool { return true; }
    public function update(User $user, User $model): bool { return true; }
    public function delete(User $user, User $model): bool { return true; }
}
