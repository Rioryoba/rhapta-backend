<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Employee>
 */
class EmployeeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'date_of_birth' => $this->faker->date('Y-m-d', '-20 years'),
            'gender' => $this->faker->randomElement(['male', 'female']),
            'hire_date' => $this->faker->date('Y-m-d', 'now'),
            'salary' => $this->faker->randomFloat(2, 30000, 100000),
            // Assign existing department and position IDs if available
            'department_id' => \App\Models\Department::inRandomOrder()->first()?->id,
            'position_id' => \App\Models\Position::inRandomOrder()->first()?->id,
            'status' => $this->faker->randomElement(['active', 'inactive', 'terminated']),
        ];
    }
}
