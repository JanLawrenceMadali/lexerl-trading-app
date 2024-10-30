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

        Customer::factory(30)->create();
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            DueDateSeeder::class,
            CategorySeeder::class,
            SubcategorySeeder::class,
            TransactionSeeder::class,
            SupplierSeeder::class,
            StatusSeeder::class,
            UnitSeeder::class,
            ProductSeeder::class,
            SaleSeeder::class,
            InventorySeeder::class,
        ]);
        User::factory(30)->create();
    }
}
