<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invoice>
 */
class InvoiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'invoice_no' => $this->faker->unique()->numerify('INV-#####'),
            'customer_id' => \App\Models\Customer::factory(),
            'invoice_date' => $this->faker->date(),
            'due_date' => $this->faker->date(),
            'subtotal' => $this->faker->randomFloat(2, 100, 10000),
            'tax' => $this->faker->randomFloat(2, 0, 500),
            'discount' => $this->faker->randomFloat(2, 0, 200),
            'total' => $this->faker->randomFloat(2, 100, 10000),
            'status' => $this->faker->randomElement(['unpaid', 'paid', 'overdue']),
            'created_by' => \App\Models\User::factory(),
        ];
    }
}
