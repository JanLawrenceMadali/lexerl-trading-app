<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Subcategory;
use App\Models\Supplier;
use App\Models\Transaction;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Inventory>
 */
class InventoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = fake()->numberBetween(1, 100);
        $landed_cost = fake()->numberBetween(100, 1000);
        return [
            'quantity' => $quantity,
            'landed_cost' => $landed_cost,
            'amount' => $quantity * $landed_cost,
            'description' => fake()->sentence(),
            'purchase_date' => fake()->date(format: 'm/d/Y'),
            'unit_id' => Unit::inRandomOrder()->first()->id,
            'product_id' => Product::inRandomOrder()->first()->id,
            'supplier_id' => Supplier::inRandomOrder()->first()->id,
            'created_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'updated_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'transaction_number' => fake()->regexify('[A-Z]{5}[0-4]{3}'),
            'transaction_id' => Transaction::inRandomOrder()->first()->id,
        ];
    }
}
