<?php

namespace Database\Seeders;

use App\Models\Transaction;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Transaction::create(['transaction_type' => 'Contract Number']);
        Transaction::create(['transaction_type' => 'Sales Invoice']);
        Transaction::create(['transaction_type' => 'Delivery Receipt']);
    }
}
