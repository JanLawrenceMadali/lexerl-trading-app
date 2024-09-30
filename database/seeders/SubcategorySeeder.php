<?php

namespace Database\Seeders;

use App\Models\Subcategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SubcategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Subcategory::create(['name' => 'Beef Shanks', 'category_id' => 1]);
        Subcategory::create(['name' => 'Beef Cubes', 'category_id' => 1]);
    }
}
