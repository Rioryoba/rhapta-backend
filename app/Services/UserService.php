<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function create(array $data)
    {
        // Set password to hashed email and password_configured to false
        $data['password'] = Hash::make($data['email']);
        $data['password_configured'] = false;
        return User::create($data);
    }
    public function update(User $user, array $data)
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }
        $user->update($data);
        return $user;
    }
    public function delete(User $user)
    {
        $user->delete();
    }
}
