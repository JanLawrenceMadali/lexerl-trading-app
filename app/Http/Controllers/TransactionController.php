<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Purchase;
use App\Models\Subcategory;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index()
    {
        return inertia('Transactions/Index', [
            'purchases' => Purchase::with('transaction', 'category', 'subcategory', 'supplier')->latest('created_at')->get(),
            'categories' => Category::latest()->get(),
            'subcategories' => Subcategory::latest()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'tx_doc_id' => 'required',
            'category_id' => 'required',
            'subcategory_id' => 'required',
            'supplier_id' => 'required',
            'amount' => 'required',
        ]);
        dd($request->all());
        // Purchase::create([
        //     'tx_doc_id' => $request->tx_doc_id,
        //     'category_id' => $request->category_id,
        //     'subcategory_id' => $request->subcategory_id,
        //     'supplier' => $request->supplier,
        //     'amount' => $request->amount,
        // ]);

        return redirect()->route('transactions.index');
    }
}
