<?php

/**
 * Script to create test users for login
 * Run this with: php artisan tinker < create_test_users.php
 * OR copy and paste the code below into php artisan tinker
 */

use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

// Ensure roles exist
$hrRole = Role::firstOrCreate(['name' => 'hr']);
$accountantRole = Role::firstOrCreate(['name' => 'accountant']);

// Create HR user
$hrUser = User::updateOrCreate(
    ['email' => 'hr@example.com'],
    [
        'user_name' => 'HR Admin',
        'password' => Hash::make('hrpass'),
        'role_id' => $hrRole->id,
    ]
);

// Create Accountant user
$accountantUser = User::updateOrCreate(
    ['email' => 'accountant@example.com'],
    [
        'user_name' => 'Accounts Manager',
        'password' => Hash::make('acctpass'),
        'role_id' => $accountantRole->id,
    ]
);

echo "Users created successfully!\n";
echo "HR User: hr@example.com / hrpass\n";
echo "Accountant User: accountant@example.com / acctpass\n";

