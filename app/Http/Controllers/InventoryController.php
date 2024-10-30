<?php

namespace App\Http\Controllers;

use App\Http\Requests\InventoryRequest;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Subcategory;
use App\Models\Supplier;
use App\Models\Transaction;
use App\Models\Unit;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    public function index()
    {
        $units = Unit::all();
        $categories = Category::all();
        $subcategories = Subcategory::all();
        $transactions = Transaction::all();
        $suppliers = Supplier::latest()->get();
        $inventories = DB::table('inventories')
            ->join('units', 'inventories.unit_id', '=', 'units.id')
            ->join('suppliers', 'inventories.supplier_id', '=', 'suppliers.id')
            ->join('transactions', 'inventories.transaction_id', '=', 'transactions.id')
            ->join('products', 'inventories.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->leftJoin('subcategories', 'products.subcategory_id', '=', 'subcategories.id')
            ->select(
                'inventories.id',
                'inventories.quantity',
                'inventories.purchase_date',
                'inventories.amount',
                'inventories.transaction_number',
                'inventories.landed_cost',
                'inventories.description',
                'units.abbreviation',
                'units.id as unit_id',
                'suppliers.id as supplier_id',
                'suppliers.name as supplier_name',
                'suppliers.email as supplier_email',
                'suppliers.address1 as supplier_address1',
                'suppliers.address2 as supplier_address2',
                'suppliers.contact_person as supplier_contact_person',
                'suppliers.contact_number as supplier_contact_number',
                'categories.id as category_id',
                'categories.name as category_name',
                'subcategories.id as subcategory_id',
                'subcategories.name as subcategory_name',
                'transactions.type as transaction_type',
                'transactions.id as transaction_id',
            )
            ->orderBy('inventories.id', 'desc')
            ->get();

        // return $inventories;
        return inertia('Transactions/PurchaseIn/Index', [
            'units' => $units,
            'suppliers' => $suppliers,
            'inventories' => $inventories,
            'categories' => $categories,
            'transactions' => $transactions,
            'subcategories' => $subcategories,
        ]);
    }

    public function store(InventoryRequest $inventoryRequest)
    {
        $validated = $inventoryRequest->validated();

        $product_id = Product::where([
            'category_id' => $validated['category_id'],
            'subcategory_id' => $validated['subcategory_id']
        ])->first()->id;

        Inventory::create([
            'product_id' => $product_id,
            'amount' => $validated['amount'],
            'unit_id' => $validated['unit_id'],
            'quantity' => $validated['quantity'],
            'description' => $validated['description'],
            'landed_cost' => $validated['landed_cost'],
            'supplier_id' => $validated['supplier_id'],
            'purchase_date' => $validated['purchase_date'],
            'transaction_id' => $validated['transaction_id'],
            'transaction_number' => $validated['transaction_number'],
        ]);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'Created a new purchase',
            'description' => 'A new purchase was created by ' . auth()->user()->username,
        ]);

        return redirect()->route('purchase-in')->with('success', 'Inventory created successfully');
    }

    public function update(InventoryRequest $inventoryRequest, $id)
    {
        $inventory = Inventory::findOrFail($id);

        return DB::transaction(function () use ($inventoryRequest, $inventory) {
            $inventory->update($inventoryRequest->validated());

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'Updated a purchase',
                'description' => 'A purchase was updated by ' . auth()->user()->username,
            ]);

            return redirect()->route('purchase-in')
                ->with('success', 'Inventory updated successfully');
        }, 3, function () {
            return redirect()->route('purchase-in')
                ->with('error', 'Failed to update inventory. Please try again.');
        });
    }

    public function destroy($id)
    {
        $inventory = Inventory::findOrFail($id);

        return DB::transaction(function () use ($inventory) {
            $inventory->delete();

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'Deleted a purchase',
                'description' => 'A purchase was deleted by ' . auth()->user()->username,
            ]);

            return redirect()->route('purchase-in')
                ->with('success', 'Inventory deleted successfully');
        }, 3, function () {
            return redirect()->route('purchase-in')
                ->with('error', 'Failed to delete inventory. Please try again.');
        });
    }
}
