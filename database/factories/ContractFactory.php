<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Contract>
 */
class ContractFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type_id' => \App\Models\ContractType::factory(),
            'contract_number' => $this->faker->unique()->numerify('CT-#####'),
            'customer_id' => \App\Models\Customer::factory(),
            'employee_id' => \App\Models\Employee::factory(),
            'position_id' => \App\Models\Position::factory(),
            'start_date' => $this->faker->date(),
            'end_date' => $this->faker->date(),
            'salary' => $this->faker->randomFloat(2, 30000, 100000),
            'status' => $this->faker->randomElement(['active', 'expired', 'terminated']),
            'file_path' => $this->faker->optional()->filePath(),
        ];
    }
}
