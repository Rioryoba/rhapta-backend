<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Core roles for employee permissions
        $coreRoles = [
            'admin' => 'Administrator - Full system access',
            'accountant' => 'Accountant - Financial and accounting access',
            'hr' => 'Human Resources - HR management access',
            'staff' => 'Staff - Basic employee access',
        ];
        
        foreach ($coreRoles as $name => $description) {
            \App\Models\Role::updateOrCreate(
                ['name' => $name],
                ['name' => $name] // In case we add description field later
            );
        }
    }
}
