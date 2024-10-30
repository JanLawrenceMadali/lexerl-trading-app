<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Customer;
use App\Models\DueDate;
use App\Models\Product;
use App\Models\Status;
use App\Models\Subcategory;
use App\Models\Transaction;
use App\Models\Unit;
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
            'description' => fake()->sentence(),
            'sale_date' => fake()->date(format: 'm/d/Y'),
            'status_id' => Status::inRandomOrder()->first()->id,
            'due_date_id' => DueDate::inRandomOrder()->first()->id,
            'customer_id' => Customer::inRandomOrder()->first()->id,
            'total_amount' => fake()->randomFloat(2, 100, 1000),
            'created_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'updated_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'transaction_number' => fake()->regexify('[A-Z]{5}[0-4]{3}'),
            'transaction_id' => Transaction::inRandomOrder()->first()->id,
        ];
    }
}
