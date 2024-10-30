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
        
        Subcategory::create(['name' => 'Chuck', 'category_id' => 1]);
        Subcategory::create(['name' => 'Rib', 'category_id' => 1]);
        Subcategory::create(['name' => 'Loin', 'category_id' => 1]);
        Subcategory::create(['name' => 'Round', 'category_id' => 1]);
        Subcategory::create(['name' => 'Flank', 'category_id' => 1]);
        Subcategory::create(['name' => 'Short Plate', 'category_id' => 1]);
        Subcategory::create(['name' => 'Brisket', 'category_id' => 1]);
        Subcategory::create(['name' => 'Shank', 'category_id' => 1]);
        
        Subcategory::create(['name' => 'Head', 'category_id' => 2]);
        Subcategory::create(['name' => 'Shoulder Arm Picnic', 'category_id' => 2]);
        Subcategory::create(['name' => 'Loin', 'category_id' => 2]);
        Subcategory::create(['name' => 'Fatback', 'category_id' => 2]);
        Subcategory::create(['name' => 'Spareribs', 'category_id' => 2]);
        Subcategory::create(['name' => 'Belly/side', 'category_id' => 2]);
        Subcategory::create(['name' => 'Legs', 'category_id' => 2]);
        Subcategory::create(['name' => 'Ham', 'category_id' => 2]);

        Subcategory::create(['name' => 'Whole', 'category_id' => 3]);
        Subcategory::create(['name' => 'Breast', 'category_id' => 3]);
        Subcategory::create(['name' => 'Tenderloin', 'category_id' => 3]);
        Subcategory::create(['name' => 'Back', 'category_id' => 3]);
        Subcategory::create(['name' => 'Wing', 'category_id' => 3]);
        Subcategory::create(['name' => 'Leg', 'category_id' => 3]);
        Subcategory::create(['name' => 'Drumstick', 'category_id' => 3]);
        Subcategory::create(['name' => 'Thigh', 'category_id' => 3]);
        Subcategory::create(['name' => 'Uncommon (neck, tail, giblets, feet)', 'category_id' => 3]);
        
        Subcategory::create(['name' => 'French Fries', 'category_id' => 4]);
        Subcategory::create(['name' => 'Kikiam', 'category_id' => 4]);
        Subcategory::create(['name' => 'Squid Ball', 'category_id' => 4]);
        Subcategory::create(['name' => 'Chicken Ball', 'category_id' => 4]);
        Subcategory::create(['name' => 'Cold Cuts', 'category_id' => 4]);
    }
}
