<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Unit::create(['name' => 'Kilograms', 'abbreviation' => 'kg(s)']);
        Unit::create(['name' => 'Pieces', 'abbreviation' => 'pc(s)']);
        Unit::create(['name' => 'Grams', 'abbreviation' => 'gm(s)']);
        Unit::create(['name' => 'Cans', 'abbreviation' => 'can(s)']);
        Unit::create(['name' => 'Boxes', 'abbreviation' => 'box(es)']);
        Unit::create(['name' => 'Packs', 'abbreviation' => 'pack(s)']);
        Unit::create(['name' => 'Bottles', 'abbreviation' => 'bottle(s)']);
    }
}
