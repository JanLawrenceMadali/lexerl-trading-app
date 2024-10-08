<?php

namespace Database\Seeders;

use App\Models\UnitMeasure;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UnitMeasureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        UnitMeasure::create(['name' => 'Pieces', 'abbreviation' => 'pc(s)']);
        UnitMeasure::create(['name' => 'Boxes', 'abbreviation' => 'box(es)']);
        UnitMeasure::create(['name' => 'Grams', 'abbreviation' => 'gm(s)']);
        UnitMeasure::create(['name' => 'Kilograms', 'abbreviation' => 'kg(s)']);
        UnitMeasure::create(['name' => 'Packs', 'abbreviation' => 'pack(s)']);
        UnitMeasure::create(['name' => 'Cans', 'abbreviation' => 'can(s)']);
        UnitMeasure::create(['name' => 'Bottles', 'abbreviation' => 'bottle(s)']);
    }
}
