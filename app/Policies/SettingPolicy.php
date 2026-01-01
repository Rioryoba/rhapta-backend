<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Setting;

class SettingPolicy
{
    public function create(User $user)
    {
        return optional($user->role)->name === 'admin';
    }

    public function update(User $user, Setting $setting)
    {
        return optional($user->role)->name === 'admin';
    }

    public function viewAny(User $user)
    {
        return true;
    }

    public function view(User $user, Setting $setting)
    {
        return true;
    }
}
