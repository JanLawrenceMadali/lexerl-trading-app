<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class PurchaseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'quantity' => fake()->randomNumber(2, true),
            'landed_cost' => fake()->randomFloat(2, 1, 1000),
            'amount' => fake()->randomFloat(2, 1, 1000),
            'notes' => fake()->sentence(),
            'category_id' => rand(1, 4),
            'supplier_id' => rand(1, 30),
            'subcategory_id' => rand(1, 2),
            'transaction_id' => rand(1, 3),
            'unit_measure_id' => rand(1, 7),
            'purchase_date' => fake()->dateTimeBetween('-1 year', 'now'),
            'transaction_number' => fake()->unique()->randomNumber(5, true)
        ];
    }
}
