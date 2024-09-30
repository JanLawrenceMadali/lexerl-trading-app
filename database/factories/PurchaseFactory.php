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
            'amount' => fake()->randomFloat(2, 1, 1000),
            'cost' => fake()->randomFloat(2, 1, 1000),
            'notes' => fake()->sentence(),
            'transaction_number' => fake()->unique()->randomNumber(5, true),
            'transaction_id' => rand(1, 3),
            'category_id' => rand(1, 4),
            'subcategory_id' => rand(1, 2),
            'supplier_id' => rand(1, 50),
        ];
    }
}
