<?php

namespace App\Http\Controllers;

use App\Exports\SalesExport;
use App\Http\Requests\SaleRequest;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Customer;
use App\Models\DueDate;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Subcategory;
use App\Models\Transaction;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

class SalesController extends Controller
{
    public function index()
    {
        $units = Unit::orderBy('name')->get();
        $dues = DueDate::get();
        $products = Product::get();
        $categories = Category::orderBy('name')->get();
        $subcategories = Subcategory::orderBy('name')->get();
        $customers = Customer::orderBy('name')->get();
        $transactions = Transaction::where('id', '!=', 1)->orderBy('type')->get();
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
                'units.id as unit_id',
                'units.abbreviation',
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
            'subcategories' => $subcategories
        ]);
    }

    public function store(SaleRequest $saleRequest)
    {
        $validated = $saleRequest->validated();

        try {
            DB::transaction(function () use ($validated) {
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

                $validationErrors = [];
                $productAttachments = [];

                foreach ($validated['products'] as $index => $product) {
                    $totalQuantity = Inventory::where([
                        'category_id' => $product['category_id'],
                        'subcategory_id' => $product['subcategory_id'],
                        'unit_id' => $product['unit_id']
                    ])->sum('quantity');

                    if ($totalQuantity < $product['quantity']) {
                        $validationErrors["products.{$index}.quantity"] = "Insufficient stock. Only {$totalQuantity} available.";
                        continue;
                    }

                    $remainingQuantityToDeduct = $product['quantity'];
                    $inventories = Inventory::where([
                        'category_id' => $product['category_id'],
                        'subcategory_id' => $product['subcategory_id'],
                        'unit_id' => $product['unit_id']
                    ])
                        ->where('quantity', '>', 0)
                        ->orderBy('created_at', 'asc')
                        ->get();

                    foreach ($inventories as $inventory) {
                        if ($remainingQuantityToDeduct <= 0) break;

                        $deductAmount = min($inventory->quantity, $remainingQuantityToDeduct);
                        $inventory->quantity -= $deductAmount;
                        $inventory->save();

                        $remainingQuantityToDeduct -= $deductAmount;
                    }

                    $product_id = Product::where([
                        'category_id' => $product['category_id'],
                        'subcategory_id' => $product['subcategory_id']
                    ])
                        ->select('id')
                        ->value('id');

                    $productAttachments[$product_id] = [
                        'amount' => $product['amount'],
                        'unit_id' => $product['unit_id'],
                        'quantity' => $product['quantity'],
                        'selling_price' => $product['selling_price'],
                    ];
                }

                if (!empty($validationErrors)) {
                    throw ValidationException::withMessages($validationErrors);
                }

                $sale->products()->attach($productAttachments);

                $this->logs('created', $validated['transaction_number']);
            });
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Throwable $e) {
            report($e);
            return redirect()->back()->with('error', 'An unexpected error occurred. Please try again.');
        }
    }

    public function update(SaleRequest $saleRequest, Sale $sale)
    {
        $validated = $saleRequest->validated();
        if ($validated['status_id'] == 1) $validated['due_date_id'] = null;

        try {
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

                $validationErrors = [];
                $productAttachments = [];

                // Loop through each product to handle inventory and sale updates
                foreach ($validated['products'] as $index => $product) {
                    $product_id = Product::where([
                        'category_id' => $product['category_id'],
                        'subcategory_id' => $product['subcategory_id']
                    ])->value('id');

                    if (!$product_id) {
                        $validationErrors["products.{$index}.category_id"] =
                            "Product not found for category ID {$product['category_id']} and subcategory ID {$product['subcategory_id']}.";
                        continue;
                    }

                    $inventory = Inventory::where(['category_id' => $product['category_id'], 'subcategory_id' => $product['subcategory_id'], 'unit_id' => $product['unit_id']])->first();

                    $existingSaleProduct = $sale->products()->wherePivot('product_id', $product_id)->first();
                    $oldSaleQuantity = $existingSaleProduct ? $existingSaleProduct->pivot->quantity : 0;

                    $newSaleQuantity = $product['quantity'];
                    $quantityDifference = $oldSaleQuantity - $newSaleQuantity;

                    // Validate inventory for this product
                    if (!$inventory || $inventory->quantity + $quantityDifference < 0) {
                        $validationErrors["products.{$index}.quantity"] =
                            "Insufficient stock. Only {$inventory->quantity} available.";
                        continue;
                    }

                    $inventory->increment('quantity', $quantityDifference);

                    $productAttachments[$product_id] = [
                        'amount' => $product['amount'],
                        'unit_id' => $product['unit_id'],
                        'quantity' => $newSaleQuantity,
                        'selling_price' => $product['selling_price'],
                    ];
                }

                if (!empty($validationErrors)) {
                    throw ValidationException::withMessages($validationErrors);
                }

                $sale->products()->sync($productAttachments);

                $this->logs('updated', $sale->transaction_number);
            });
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Throwable $e) {
            report($e);
            return redirect()->back()->with('error', 'An error occurred while updating the sale.');
        }
    }

    public function destroy(Sale $sale)
    {
        try {
            DB::transaction(function () use ($sale) {
                $sale->delete();

                $this->logs('deleted', $sale->transaction_numer);
            });
        } catch (\Throwable $e) {
            report($e);
        }
    }

    public function export(Request $request)
    {
        sleep(1);

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $export = new SalesExport($startDate, $endDate);

        $date = now()->format('Ymd');
        $fileName = "sales_report_{$date}.xlsx";

        $this->logs('exported', $fileName);

        return Excel::download($export, $fileName);
    }

    private function logs(string $action, string $description)
    {
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'description' => Auth::user()->username . ' ' . $action . ' a sale ' . $description
        ]);
    }
}
