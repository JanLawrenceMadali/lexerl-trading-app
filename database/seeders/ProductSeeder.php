<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Product::create(['category_id' => 1, 'subcategory_id' => 1]);
        Product::create(['category_id' => 1, 'subcategory_id' => 2]);
        Product::create(['category_id' => 1, 'subcategory_id' => 3]);
        Product::create(['category_id' => 1, 'subcategory_id' => 4]);
        Product::create(['category_id' => 1, 'subcategory_id' => 5]);
        Product::create(['category_id' => 1, 'subcategory_id' => 6]);
        Product::create(['category_id' => 1, 'subcategory_id' => 7]);
        Product::create(['category_id' => 1, 'subcategory_id' => 8]);
        Product::create(['category_id' => 2, 'subcategory_id' => 9]);
        Product::create(['category_id' => 2, 'subcategory_id' => 10]);
        Product::create(['category_id' => 2, 'subcategory_id' => 11]);
        Product::create(['category_id' => 2, 'subcategory_id' => 12]);
        Product::create(['category_id' => 2, 'subcategory_id' => 13]);
        Product::create(['category_id' => 2, 'subcategory_id' => 14]);
        Product::create(['category_id' => 2, 'subcategory_id' => 15]);
        Product::create(['category_id' => 2, 'subcategory_id' => 16]);
        Product::create(['category_id' => 3, 'subcategory_id' => 17]);
        Product::create(['category_id' => 3, 'subcategory_id' => 18]);
        Product::create(['category_id' => 3, 'subcategory_id' => 19]);
        Product::create(['category_id' => 3, 'subcategory_id' => 20]);
        Product::create(['category_id' => 3, 'subcategory_id' => 21]);
        Product::create(['category_id' => 3, 'subcategory_id' => 22]);
        Product::create(['category_id' => 3, 'subcategory_id' => 23]);
        Product::create(['category_id' => 3, 'subcategory_id' => 24]);
        Product::create(['category_id' => 3, 'subcategory_id' => 25]);
        Product::create(['category_id' => 4, 'subcategory_id' => 26]);
        Product::create(['category_id' => 4, 'subcategory_id' => 27]);
        Product::create(['category_id' => 4, 'subcategory_id' => 28]);
        Product::create(['category_id' => 4, 'subcategory_id' => 29]);
        Product::create(['category_id' => 4, 'subcategory_id' => 30]);


    }
}
