<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Position;
use App\Models\Department;
use Illuminate\Support\Facades\File;

class PositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Path to db.json file - try multiple possible locations
        $possiblePaths = [
            base_path('../frontend/db.json'),
            base_path('../../frontend/db.json'),
            storage_path('app/db.json'),
        ];
        
        $dbJsonPath = null;
        foreach ($possiblePaths as $path) {
            if (File::exists($path)) {
                $dbJsonPath = $path;
                break;
            }
        }
        
        if (!$dbJsonPath) {
            $this->command->warn('db.json file not found. Tried: ' . implode(', ', $possiblePaths));
            return;
        }

        // Read and decode JSON file
        $jsonContent = File::get($dbJsonPath);
        $data = json_decode($jsonContent, true);

        if (!isset($data['positions']) || !is_array($data['positions'])) {
            $this->command->warn('No positions found in db.json');
            return;
        }

        $importedCount = 0;

        // Import each position from db.json
        foreach ($data['positions'] as $positionData) {
            // Find department by name (trim whitespace)
            $departmentName = trim($positionData['department'] ?? '');
            $department = null;

            if (!empty($departmentName)) {
                $department = Department::where('name', 'like', '%' . $departmentName . '%')->first();
                
                // If not found, try exact match
                if (!$department) {
                    $department = Department::where('name', $departmentName)->first();
                }
            }

            // Create or update position
            Position::updateOrCreate(
                ['title' => trim($positionData['title'] ?? '')],
                [
                    'title' => trim($positionData['title'] ?? ''),
                    'description' => $positionData['description'] ?? null,
                    'department_id' => $department ? $department->id : null,
                ]
            );

            $importedCount++;
        }

        $this->command->info("Imported {$importedCount} position(s) from db.json to the database.");
    }
}
