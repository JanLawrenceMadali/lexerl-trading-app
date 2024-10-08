<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Purchase;
use App\Models\Subcategory;
use App\Models\Supplier;
use App\Models\Transaction;
use App\Models\UnitMeasure;
use Illuminate\Http\Request;

class PurchaseInController extends Controller
{
    public function index()
    {
        return inertia('Transactions/PurchaseIn/Index', [
            'units' => UnitMeasure::all(),
            'categories' => Category::all(),
            'transactions' => Transaction::all(),
            'subcategories' => Subcategory::all(),
            'suppliers' => Supplier::orderBy('name')->get(),
            'purchases' => Purchase::with('transaction', 'category', 'subcategory', 'supplier', 'unit_measure')->latest()->get(),
        ]);
    }

    public function create()
    {
        return inertia('Transactions/PurchaseIn/Create', [
            'units' => UnitMeasure::all(),
            'categories' => Category::all(),
            'transactions' => Transaction::all(),
            'subcategories' => Subcategory::all(),
            'suppliers' => Supplier::latest()->get(),
        ]);
    }

    public function store(Request $request)
    {
        sleep(1);
        $request->validate([
            'cost' => 'required',
            'quantity' => 'required',
            'category_id' => 'required',
            'supplier_id' => 'required',
            'subcategory_id' => 'required',
            'transaction_id' => 'required',
            'unit_measure_id' => 'required',
            'transaction_number' => 'required | unique:purchases',
        ], [
            'cost.required' => 'The cost field is required.',
            'quantity.required' => 'The quantity field is required.',
            'category_id.required' => 'The category field is required.',
            'supplier_id.required' => 'The supplier field is required.',
            'subcategory_id.required' => 'The subcategory field is required.',
            'transaction_id.required' => 'The transaction type field is required.',
            'unit_measure_id.required' => 'The unit measure field is required.',
            'transaction_number.required' => 'The transaction number field is required.',
        ]);

        Purchase::create([
            'cost' => $request->cost,
            'notes' => $request->notes,
            'quantity' => $request->quantity,
            'category_id' => $request->category_id,
            'supplier_id' => $request->supplier_id,
            'subcategory_id' => $request->subcategory_id,
            'transaction_id' => $request->transaction_id,
            'unit_measure_id' => $request->unit_measure_id,
            'transaction_number' => $request->transaction_number,
        ]);

        return redirect()->route('purchase-in')->with('message', 'Purchase In created successfully');
    }

    public function update(Request $request, Purchase $purchase)
    {
        sleep(1);
        $request->validate([
            'cost' => 'required',
            'quantity' => 'required',
            'category_id' => 'required',
            'supplier_id' => 'required',
            'subcategory_id' => 'required',
            'transaction_id' => 'required',
            'unit_measure_id' => 'required',
            'transaction_number' => 'required',
        ]);

        $purchase->update([
            'cost' => $request->cost,
            'notes' => $request->notes,
            'quantity' => $request->quantity,
            'category_id' => $request->category_id,
            'supplier_id' => $request->supplier_id,
            'subcategory_id' => $request->subcategory_id,
            'transaction_id' => $request->transaction_id,
            'unit_measure_id' => $request->unit_measure_id,
            'transaction_number' => $request->transaction_number,
        ]);

        return redirect()->route('purchase-in')->with('message', 'Purchase In updated successfully');
    }

    public function destroy(Purchase $purchase)
    {
        sleep(1);
        $purchase->delete();
        return redirect()->route('purchase-in')->with('message', 'Purchase In deleted successfully');
    }
}
