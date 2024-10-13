<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Sale>
 */
class SaleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category_id' => rand(1, 4),
            'customer_id' => rand(1, 30),
            'is_paid' => fake()->boolean(),
            'notes' => fake()->sentence(),
            'subcategory_id' => rand(1, 2),
            'due_date_id' => rand(1, 3),
            'transaction_id' => rand(2, 3),
            'unit_measure_id' => rand(1, 7),
            'amount' => fake()->randomFloat(2, 1, 1000),
            'quantity' => fake()->randomNumber(2, true),
            'selling_price' => fake()->randomFloat(2, 1, 1000),
            'sales_date' => fake()->dateTimeBetween('-1 year', 'now'),
            'transaction_number' => fake()->unique()->randomNumber(8, true)
        ];
    }
}
