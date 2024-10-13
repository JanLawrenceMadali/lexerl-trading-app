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
        Subcategory::create(['name' => 'Beef Brisket', 'category_id' => 1]);
        Subcategory::create(['name' => 'Beef Cuberoll', 'category_id' => 1]);
        Subcategory::create(['name' => 'Beef Feet', 'category_id' => 1]);
        Subcategory::create(['name' => 'Beef Fat', 'category_id' => 1]);
        Subcategory::create(['name' => 'Beef Shortplate', 'category_id' => 1]);
        Subcategory::create(['name' => 'Beef Knuckles', 'category_id' => 1]);
        Subcategory::create(['name' => 'Beef Shin', 'category_id' => 1]);
        Subcategory::create(['name' => 'Beef Rib Shortplate', 'category_id' => 1]);
        Subcategory::create(['name' => 'Beef Tendon', 'category_id' => 1]);
        Subcategory::create(['name' => 'Chicken Breast', 'category_id' => 3]);
        Subcategory::create(['name' => 'Chicken Backbone', 'category_id' => 3]);
        Subcategory::create(['name' => 'Chicken Feet', 'category_id' => 3]);
        Subcategory::create(['name' => 'Chicken Gizzard', 'category_id' => 3]);
        Subcategory::create(['name' => 'Chicken Liver', 'category_id' => 3]);
        Subcategory::create(['name' => 'Chicken Leg Meat', 'category_id' => 3]);
        Subcategory::create(['name' => 'Chicken Leg Meat Fillet', 'category_id' => 3]);
        Subcategory::create(['name' => 'Pork Jowls', 'category_id' => 2]);
        Subcategory::create(['name' => 'Pork Picnic Shoulder', 'category_id' => 2]);
        Subcategory::create(['name' => 'Pork Eardrum', 'category_id' => 2]);
        Subcategory::create(['name' => 'Pork Belly', 'category_id' => 2]);
        Subcategory::create(['name' => 'Pork Trimmings', 'category_id' => 2]);
        Subcategory::create(['name' => 'Pork Flowers', 'category_id' => 2]);
        Subcategory::create(['name' => 'Pork Liver', 'category_id' => 2]);
        Subcategory::create(['name' => 'Pork Carcass', 'category_id' => 2]);
        Subcategory::create(['name' => 'Pork Riblets', 'category_id' => 2]);
        Subcategory::create(['name' => 'Pork Back Skin', 'category_id' => 2]);
        Subcategory::create(['name' => 'Chicken MDM', 'category_id' => 3]);
        Subcategory::create(['name' => 'Chicken Neck', 'category_id' => 3]);
        Subcategory::create(['name' => 'Chicken Whole', 'category_id' => 3]);
        Subcategory::create(['name' => 'Chicken Wings', 'category_id' => 3]);
        Subcategory::create(['name' => 'Chicken Tail', 'category_id' => 3]);
        Subcategory::create(['name' => 'Bacon', 'category_id' => 4]);
        Subcategory::create(['name' => 'Tender Juicy Hotdog', 'category_id' => 4]);
        Subcategory::create(['name' => 'Fish', 'category_id' => 4]);
    }
}
