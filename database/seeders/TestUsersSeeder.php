<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;

class TestUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure roles exist
        $hrRole = Role::firstOrCreate(['name' => 'hr']);
        $accountantRole = Role::firstOrCreate(['name' => 'accountant']);

        // Create HR user
        User::updateOrCreate(
            ['email' => 'hr@example.com'],
            [
                'user_name' => 'HR Admin',
                'password' => Hash::make('hrpass'),
                'role_id' => $hrRole->id,
            ]
        );

        // Create Accountant user
        User::updateOrCreate(
            ['email' => 'accountant@example.com'],
            [
                'user_name' => 'Accounts Manager',
                'password' => Hash::make('acctpass'),
                'role_id' => $accountantRole->id,
            ]
        );

        $this->command->info('Test users created successfully!');
        $this->command->info('HR User: hr@example.com / hrpass');
        $this->command->info('Accountant User: accountant@example.com / acctpass');
    }
}

