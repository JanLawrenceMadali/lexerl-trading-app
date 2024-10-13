<?php

namespace Database\Seeders;

use App\Models\DueDate;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DueDateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DueDate::create(['days' => '7 days']);
        DueDate::create(['days' => '15 days']);
        DueDate::create(['days' => '30 days']);
    }
}
