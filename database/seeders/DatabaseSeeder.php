<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {        
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            DueDateSeeder::class,
            CategorySeeder::class,
            SubcategorySeeder::class,
            TransactionSeeder::class,
            UnitMeasureSeeder::class
        ]);
        User::factory(30)->create();
        Customer::factory(30)->create();
        Sale::factory(30)->create();
        Supplier::factory(30)->create();
        Purchase::factory(30)->create();
    }
}
