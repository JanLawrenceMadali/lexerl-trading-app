<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaleRequest;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Customer;
use App\Models\DueDate;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Subcategory;
use App\Models\Supplier;
use App\Models\Transaction;
use App\Models\Unit;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SalesController extends Controller
{
    public function index()
    {
        $units = Unit::get();
        $dues = DueDate::get();
        $products = Product::get();
        $categories = Category::get();
        $subcategories = Subcategory::get();
        $customers = Customer::latest()->get();
        $transactions = Transaction::where('id', '!=', 1)->get();
        $total_quantity = Inventory::sum('quantity');
        $inventories = DB::table('inventories')
            ->join('units', 'inventories.unit_id', '=', 'units.id')
            ->join('suppliers', 'inventories.supplier_id', '=', 'suppliers.id')
            ->join('products', 'inventories.product_id', '=', 'products.id')
            ->join('transactions', 'inventories.transaction_id', '=', 'transactions.id')
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
                'units.id as unit_id',
                'units.abbreviation',
                'products.id as product_id',
                'categories.id as category_id',
                'categories.name as category_name',
                'subcategories.id as subcategory_id',
                'subcategories.name as subcategory_name',
                'suppliers.name as supplier_name',
                'suppliers.email as supplier_email',
                'transactions.type as transaction_type',
            )
            ->orderBy('inventories.id', 'desc')
            ->get();

        $sales = Sale::has('products')->with('products.categories', 'products.subcategories', 'statuses', 'customers', 'transactions')->latest()->get();

        $newSales = [];

        foreach ($sales as $sale) {
            $newSales[] = [
                'id' => $sale->id,
                'sale_date' => $sale->sale_date,
                'transaction_id' => $sale->transaction_id,
                'transaction_type' => $sale->transactions->type,
                'transaction_number' => $sale->transaction_number,
                'total_amount' => $sale->total_amount,
                'description' => $sale->description,
                'status_id' => $sale->status_id,
                'status' => $sale->statuses->name,
                'customer_id' => $sale->customer_id,
                'customer_name' => $sale->customers->name,
                'customer_email' => $sale->customers->email,
                'customer_address1' => $sale->customers->address1,
                'customer_address2' => $sale->customers->address2,
                'customer_contact_person' => $sale->customers->contact_person,
                'customer_contact_number' => $sale->customers->contact_number,
                'due_date_id' => $sale->due_date_id,
                'products' => $sale->products->map(function ($product) {
                    $unit = $product->pivot->unit_id ? Unit::find($product->pivot->unit_id) : null;
                    return [
                        'product_id' => $product->id,
                        'amount' => $product->pivot->amount,
                        'unit_id' => $unit->id,
                        'abbreviation' => $unit->abbreviation,
                        'quantity' => $product->pivot->quantity,
                        'category_id' => $product->categories->id,
                        'category_name' => $product->categories->name,
                        'subcategory_id' => $product->subcategories->id,
                        'subcategory_name' => $product->subcategories->name,
                        'selling_price' => $product->pivot->selling_price,
                    ];
                }),
            ];
        }

        return inertia('Transactions/Sales/Index', [
            'dues' => $dues,
            'units' => $units,
            'sales' => $newSales,
            'products' => $products,
            'customers' => $customers,
            'categories' => $categories,
            'inventories' => $inventories,
            'transactions' => $transactions,
            'subcategories' => $subcategories,
            'total_quantity' => $total_quantity,
        ]);
    }

    public function store(SaleRequest $saleRequest)
    {
        $validated = $saleRequest->validated();

        DB::transaction(function () use ($validated) {
            // Create the sale record
            $sale = Sale::create([
                'sale_date' => $validated['sale_date'],
                'status_id' => $validated['status_id'],
                'customer_id' => $validated['customer_id'],
                'due_date_id' => $validated['due_date_id'],
                'description' => $validated['description'],
                'total_amount' => $validated['total_amount'],
                'transaction_id' => $validated['transaction_id'],
                'transaction_number' => $validated['transaction_number'],
            ]);

            $productIds = [];
            $validationErrors = [];

            // Retrieve products and inventory data in one query
            foreach ($validated['products'] as $product) {
                $productIds[] = [
                    'category_id' => $product['category_id'],
                    'subcategory_id' => $product['subcategory_id'],
                    'unit_id' => $product['unit_id'],
                    'quantity' => $product['quantity']
                ];
            }

            $products = Product::whereIn('category_id', array_column($productIds, 'category_id'))
                ->whereIn('subcategory_id', array_column($productIds, 'subcategory_id'))
                ->get()
                ->keyBy(function ($item) {
                    return $item['category_id'] . '-' . $item['subcategory_id'];
                });

            $inventories = Inventory::whereIn('product_id', $products->pluck('id'))
                ->whereIn('unit_id', array_column($productIds, 'unit_id'))
                ->get()
                ->groupBy('product_id');

            $productAttachments = [];
            $inventoryUpdates = [];

            foreach ($validated['products'] as $index => $product) {
                $productKey = $product['category_id'] . '-' . $product['subcategory_id'];
                $productItem = $products->get($productKey);

                if (!$productItem) {
                    $validationErrors["products.{$index}.category_id"] = "Product not found for category ID {$product['category_id']} and subcategory ID {$product['subcategory_id']}.";
                    continue;
                }

                $inventory = $inventories->get($productItem->id)->firstWhere('unit_id', $product['unit_id']);

                if (!$inventory || $inventory->quantity < $product['quantity']) {
                    $availableQuantity = $inventory ? $inventory->quantity : 0;
                    $validationErrors["products.{$index}.quantity"] = "Insufficient requested quantity. Available: {$availableQuantity}.";
                    continue;
                }

                $productAttachments[$productItem->id] = [
                    'amount' => $product['amount'],
                    'unit_id' => $product['unit_id'],
                    'quantity' => $product['quantity'],
                    'selling_price' => $product['selling_price'],
                ];

                $newQuantity = max(0, $inventory->quantity - $product['quantity']);
                $inventoryUpdates[] = [
                    'product_id' => $productItem->id,
                    'new_quantity' => $newQuantity,
                    'unit_id' => $product['unit_id'],
                ];
            }

            if (!empty($validationErrors)) {
                throw ValidationException::withMessages($validationErrors);
            }

            // Attach products to the sale
            $sale->products()->attach($productAttachments);

            // Update inventory quantities
            foreach ($inventoryUpdates as $update) {
                Inventory::where([
                    'product_id' => $update['product_id'],
                    'unit_id' => $update['unit_id']
                ])->update(['quantity' => $update['new_quantity']]);
            }

            // Log the activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'Created a new sale',
                'description' => 'A new sale was created by ' . auth()->user()->username,
            ]);
        });

        return redirect()->route('sales')->with('message', 'Sale created successfully');
    }

    public function update(SaleRequest $saleRequest, Sale $sale)
    {
        $validated = $saleRequest->validated();

        if ($validated['status_id'] == 1) {
            $validated['due_date_id'] = '';
        }

        DB::transaction(function () use ($validated, $sale) {
            $sale->update([
                'sale_date' => $validated['sale_date'],
                'status_id' => $validated['status_id'],
                'customer_id' => $validated['customer_id'],
                'due_date_id' => $validated['due_date_id'],
                'description' => $validated['description'],
                'total_amount' => $validated['total_amount'],
                'transaction_id' => $validated['transaction_id'],
                'transaction_number' => $validated['transaction_number'],
            ]);

            $productAttachments = [];
            $inventoryUpdates = [];
            $validationErrors = [];

            foreach ($validated['products'] as $index => $product) {
                $product_id = Product::where([
                    'category_id' => $product['category_id'],
                    'subcategory_id' => $product['subcategory_id']
                ])->value('id');

                if (!$product_id) {
                    $validationErrors["products.{$index}.category_id"] = "Product not found for category ID {$product['category_id']} and subcategory ID {$product['subcategory_id']}.";
                    continue;
                }

                $inventory = Inventory::where(['product_id' => $product_id, 'unit_id' => $product['unit_id']])->first();

                if (!$inventory || $inventory->quantity < $product['quantity']) {
                    $requiredQuantity = $product['quantity'];
                    $availableQuantity = $inventory ? $inventory->quantity : 0;
                    $validationErrors["products.{$index}.quantity"] = "Selected product in index {$index} has insufficient stock. Required: {$requiredQuantity}, Available: {$availableQuantity}.";
                    continue;
                }

                $productAttachments[$product_id] = [
                    'amount' => $product['amount'],
                    'unit_id' => $product['unit_id'],
                    'quantity' => $product['quantity'],
                    'selling_price' => $product['selling_price'],
                ];

                $newQuantity = max(0, $inventory->quantity - $product['quantity']);
                $inventoryUpdates[] = [
                    'product_id' => $product_id,
                    'new_quantity' => $newQuantity,
                    'unit_id' => $product['unit_id'],
                ];
            }

            if (!empty($validationErrors)) {
                throw ValidationException::withMessages($validationErrors);
            }

            // Attach products to the sale
            $sale->products()->sync($productAttachments);

            // Update inventory quantities
            foreach ($inventoryUpdates as $update) {
                Inventory::where(['product_id' => $update['product_id'], 'unit_id' => $update['unit_id']])
                    ->update(['quantity' => $update['new_quantity']]);
            }

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'Updated a sale',
                'description' => 'A sale was updated by ' . auth()->user()->username,
            ]);
        });

        return redirect()->route('sales')->with('message', 'Sale updated successfully');
    }

    public function destroy(Sale $sale)
    {
        $sale->delete();

        ActivityLog::create([
            'user_id' => auth()->user()->id,
            'action' => 'Deleted a sale',
            'description' => 'A sale was deleted by ' . auth()->user()->username,
        ]);

        return redirect()->route('sales')->with('message', 'Purchase In deleted successfully');
    }
}
