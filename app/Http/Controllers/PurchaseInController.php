<?php

namespace App\Http\Controllers;

use App\Http\Requests\PurchaseInRequest;
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

    public function store(Request $request, PurchaseInRequest $purchaseInRequest)
    {
        sleep(1);
        $validated = $purchaseInRequest->validated();

        Purchase::create([
            ...$validated,
            'notes' => $request->notes,
            'amount' => $request->amount,
        ]);

        return redirect()->route('purchase-in')->with('message', 'Purchase In created successfully');
    }

    public function update(Request $request, PurchaseInRequest $purchaseInRequest, Purchase $purchase)
    {
        sleep(1);
        $validated = $purchaseInRequest->validated();

        $purchase->update([
            ...$validated,
            'notes' => $request->notes,
            'amount' => $request->amount
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
