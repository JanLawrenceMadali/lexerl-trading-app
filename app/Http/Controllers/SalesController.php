<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaleRequest;
use App\Models\Category;
use App\Models\Customer;
use App\Models\DueDate;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Subcategory;
use App\Models\Supplier;
use App\Models\Transaction;
use App\Models\UnitMeasure;
use Illuminate\Http\Request;

class SalesController extends Controller
{
    public function index()
    {
        return inertia('Transactions/Sales/Index', [
            'dues' => DueDate::all(),
            'units' => UnitMeasure::all(),
            'categories' => Category::all(),
            'subcategories' => Subcategory::all(),
            'customers' => Customer::orderBy('name')->get(),
            'transactions' => Transaction::where('id', '!=', 1)->get(),
            'sales' => Sale::with('transaction', 'category', 'subcategory', 'customer', 'unit_measure', 'due_date')->latest()->get(),
        ]);
    }

    public function store(Request $request, SaleRequest $sales)
    {
        sleep(1);
        $validated = $sales->validated();

        Sale::create([
            ...$validated,
            'notes' => $request->notes,
            'amount' => $request->amount,
        ]);

        return redirect()->route('sales')->with('message', 'Purchase In created successfully');
    }

    public function update(SaleRequest $saleRequest, Sale $sale)
    {
        sleep(1);
        $validated = $saleRequest->validated();

        $sale->update($validated);

        return redirect()->route('sales')->with('message', 'Purchase In updated successfully');
    }

    public function destroy(Sale $sale)
    {
        sleep(1);
        $sale->delete();
        return redirect()->route('sales')->with('message', 'Purchase In deleted successfully');
    }
}
