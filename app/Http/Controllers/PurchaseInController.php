<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use Illuminate\Http\Request;

class PurchaseInController extends Controller
{
    public function index()
    {
        return inertia('Transactions/PurchaseIn/Index', [
            'purchases' => Purchase::with('transaction', 'category', 'subcategory', 'supplier')->latest()->get(),
        ]);
    }
}
