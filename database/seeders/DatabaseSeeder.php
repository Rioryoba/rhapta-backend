<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Roles first (no dependencies)
        // Core roles for employee permissions: admin, accountant, hr, staff
        $coreRoles = ['admin', 'accountant', 'hr', 'staff'];
        foreach ($coreRoles as $role) {
            \App\Models\Role::updateOrCreate(['name' => $role]);
        }
        
        // Additional roles (optional, for backward compatibility)
        $additionalRoles = ['user', 'manager', 'employee', 'ceo'];
        foreach ($additionalRoles as $role) {
            \App\Models\Role::updateOrCreate(['name' => $role]);
        }

        // 2. Departments (no dependencies)
        $departments = \App\Models\Department::factory(5)->create();

        // 3. Positions (needs departments)
        $positions = \App\Models\Position::factory(10)->create([
            'department_id' => fn() => $departments->random()->id,
        ]);

        // 4. Employees (needs departments and positions)
        $employees = \App\Models\Employee::factory(20)->create([
            'department_id' => fn() => $departments->random()->id,
            'position_id' => fn() => $positions->random()->id,
        ]);

        // 5. Customers (independent)
        \App\Models\Customer::factory(20)->create();

        // 6. Users (needs employees and roles)
        \App\Models\User::factory(20)->create([
            'employee_id' => fn() => $employees->random()->id,
            'role_id' => fn() => \App\Models\Role::inRandomOrder()->first()->id,
        ]);

        // Create specific admin user with known password
        \App\Models\User::updateOrCreate([
            'email' => 'admin@gmail.com'
        ], [
            'user_name' => 'Admin',
            'role_id' => \App\Models\Role::where('name', 'admin')->first()->id ?? 1,
            'password' => bcrypt('adminpassword'),
        ]);
    }
}
