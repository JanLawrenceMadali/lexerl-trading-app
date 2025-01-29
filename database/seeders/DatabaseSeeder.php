<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {

        // Customer::factory(5)->create();
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            DueDateSeeder::class,
            CategorySeeder::class,
            SubcategorySeeder::class,
            TransactionSeeder::class,
            // SupplierSeeder::class,
            StatusSeeder::class,
            UnitSeeder::class,
            // ProductSeeder::class,
            // SaleSeeder::class,
            // InventorySeeder::class,
        ]);
    }
}
