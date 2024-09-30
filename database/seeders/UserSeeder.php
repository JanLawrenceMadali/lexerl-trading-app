<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'username' => 'LexerlSuperAdmin',
            'email' => 'superadmin@lexerl.com',
            'password' => bcrypt('Lexerl@2024'),
            'role_id' => 1
        ]);

        User::create([
            'username' => 'LexerlAdmin',
            'email' => 'admin@lexerl.com',
            'password' => bcrypt('Lexerl@2024'),
            'role_id' => 2
        ]);

        User::create([
            'username' => 'LexerlEmployee',
            'email' => 'employee@lexerl.com',
            'password' => bcrypt('Lexerl@2024'),
            'role_id' => 3
        ]);
    }
}
