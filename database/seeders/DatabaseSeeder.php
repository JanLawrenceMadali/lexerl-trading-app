<?php

namespace Database\Seeders;

use App\Models\Supplier;
use App\Models\Purchase;
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
            CategorySeeder::class,
            SubcategorySeeder::class,
            TransactionSeeder::class
        ]);
        User::factory(50)->create();
        Supplier::factory(50)->create();
        Purchase::factory(50)->create();
    }
}
