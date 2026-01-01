<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Department;
use App\Models\Employee;
use Illuminate\Support\Facades\File;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Path to the frontend db.json file
        $jsonPath = base_path('../frontend/db.json');
        
        if (!File::exists($jsonPath)) {
            $this->command->warn('db.json file not found. Skipping department migration.');
            return;
        }

        $jsonData = json_decode(File::get($jsonPath), true);
        
        if (!isset($jsonData['departments']) || !is_array($jsonData['departments'])) {
            $this->command->warn('No departments found in db.json. Skipping department migration.');
            return;
        }

        foreach ($jsonData['departments'] as $deptData) {
            // Find manager by name if head is provided
            $managerId = null;
            if (!empty($deptData['head']) && trim($deptData['head']) !== '') {
                $headName = trim($deptData['head']);
                // Try to find employee by full name (first_name + last_name)
                $nameParts = explode(' ', $headName, 2);
                if (count($nameParts) === 2) {
                    $employee = Employee::where('first_name', trim($nameParts[0]))
                        ->where('last_name', trim($nameParts[1]))
                        ->first();
                    if ($employee) {
                        $managerId = $employee->id;
                    }
                }
            }

            // Check if department already exists by name
            $existingDept = Department::where('name', trim($deptData['name']))->first();
            
            if (!$existingDept) {
                Department::create([
                    'name' => trim($deptData['name']),
                    'description' => $deptData['description'] ?? null,
                    'manager_id' => $managerId,
                ]);
                $this->command->info("Created department: {$deptData['name']}");
            } else {
                $this->command->warn("Department '{$deptData['name']}' already exists. Skipping.");
            }
        }
    }
}
