<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ContractTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = ['Permanent', 'Temporary', 'Consultant', 'Internship'];
        foreach ($types as $type) {
            \App\Models\ContractType::updateOrCreate(['name' => $type]);
        }
    }
}
