<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;

class CreateAccountantUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure accountant role exists
        $accountantRole = Role::firstOrCreate(['name' => 'accountant']);

        // Create or update the user
        $user = User::updateOrCreate(
            ['email' => 'francobannerz@gmail.com'],
            [
                'user_name' => 'Franco Bannerz',
                'password' => Hash::make('acctpass'),
                'role_id' => $accountantRole->id,
            ]
        );

        $this->command->info('User created successfully!');
        $this->command->info('Email: ' . $user->email);
        $this->command->info('Password: acctpass');
        $this->command->info('Role: ' . $user->role->name);
    }
}









