<?php

namespace App\Http\Controllers;

use App\Exports\PurchaseInExport;
use App\Http\Requests\InventoryRequest;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Subcategory;
use App\Models\Supplier;
use App\Models\Transaction;
use App\Models\Unit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class InventoryController extends Controller
{
    public function index()
    {
        $units = Unit::all();
        $categories = Category::all();
        $subcategories = Subcategory::all();
        $transactions = Transaction::orderBy('type')->get();
        $suppliers = Supplier::orderBy('name')->get();
        $inventories = DB::table('inventories')
            ->join('units', 'inventories.unit_id', '=', 'units.id')
            ->join('suppliers', 'inventories.supplier_id', '=', 'suppliers.id')
            ->join('transactions', 'inventories.transaction_id', '=', 'transactions.id')
            ->join('categories', 'inventories.category_id', '=', 'categories.id')
            ->join('subcategories', 'inventories.subcategory_id', '=', 'subcategories.id')
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
            ->where('inventories.quantity', '>', 0)
            ->get();

        // return $inventories;
        return Inertia::render('Transactions/PurchaseIn/Index', [
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

        try {
            DB::transaction(function () use ($validated) {
                $inventory = Inventory::create([
                    'amount' => $validated['amount'],
                    'unit_id' => $validated['unit_id'],
                    'quantity' => $validated['quantity'],
                    'description' => $validated['description'],
                    'landed_cost' => $validated['landed_cost'],
                    'supplier_id' => $validated['supplier_id'],
                    'category_id' => $validated['category_id'],
                    'purchase_date' => $validated['purchase_date'],
                    'subcategory_id' => $validated['subcategory_id'],
                    'transaction_id' => $validated['transaction_id'],
                    'transaction_number' => $validated['transaction_number'],
                ]);

                $this->logs('Purchase Created');

                return $inventory;
            });
        } catch (\Exception $e) {
            report($e);
        }
    }

    public function update(InventoryRequest $inventoryRequest, Inventory $inventory)
    {
        $validated = $inventoryRequest->validated();

        try {
            DB::transaction(function () use ($validated, $inventory) {
                $inventory->update($validated);
                $this->logs('Purchase Updated');
            });
        } catch (\Exception $e) {
            report($e);
        }
    }

    public function destroy(Inventory $inventory)
    {
        try {
            DB::transaction(function () use ($inventory) {
                $inventory->delete();

                $this->logs('Purchase Deleted');
            });
        } catch (\Exception $e) {
            report($e);
        }
    }

    public function export()
    {
        sleep(1);
        $date = now()->format('Ymd');
        $fileName = "purchase_in_{$date}.xlsx";
        $this->logs('PurchaseIn Exported');
        return Excel::download(new PurchaseInExport, $fileName);
    }

    private function logs(string $action)
    {
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'description' => $action . ' by ' . Auth::user()->username,
        ]);
    }
}
