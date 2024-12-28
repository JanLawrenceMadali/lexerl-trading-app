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
use App\Services\ActivityLoggerService;
use App\Services\SaleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

class SalesController extends Controller
{
    protected $activityLog;
    protected $saleService;

    public function __construct(
        SaleService $saleService,
        ActivityLoggerService $activityLoggerService
    ) {
        $this->saleService = $saleService;
        $this->activityLog = $activityLoggerService;
    }

    public function index()
    {
        $units = Unit::orderBy('name')->get();
        $dues = DueDate::get();
        $products = Product::get();
        $categories = Category::orderBy('name')->get();
        $subcategories = Subcategory::orderBy('name')->get();
        $customers = Customer::orderBy('name')->get();
        $transactions = Transaction::where('id', '!=', 1)->orderBy('type')->get();
        $inventories = Inventory::with('units', 'suppliers', 'transactions', 'categories', 'subcategories')
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($inventory) {
                return [
                    'id' => $inventory->id,
                    'quantity' => $inventory->quantity,
                    'purchase_date' => $inventory->purchase_date,
                    'amount' => $inventory->amount,
                    'transaction_number' => $inventory->transaction_number,
                    'landed_cost' => $inventory->landed_cost,
                    'description' => $inventory->description,
                    'unit_id' => $inventory->unit_id,
                    'abbreviation' => $inventory->units->abbreviation,
                    'category_id' => $inventory->categories->id,
                    'category_name' => $inventory->categories->name,
                    'subcategory_id' => $inventory->subcategories->id,
                    'subcategory_name' => $inventory->subcategories->name,
                    'supplier_id' => $inventory->suppliers->id,
                    'supplier_name' => $inventory->suppliers->name,
                    'supplier_email' => $inventory->suppliers->email,
                    'transaction_id' => $inventory->transaction_id,
                    'transaction_type' => $inventory->transactions->type
                ];
            });

        $sales = Sale::has('products')
            ->with('products.categories', 'products.subcategories', 'statuses', 'customers', 'transactions')
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($sale) {
                return [
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
            });

        return inertia('Transactions/Sales/Index', [
            'dues' => $dues,
            'units' => $units,
            'sales' => $sales,
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
        try {
            DB::transaction(function () use ($saleRequest) {
                $validated = $saleRequest->validated();

                $sale = Sale::create($this->saleService->prepareSaleData($validated));

                $productAttachments = $this->processProductInventory($validated['products'], 'create');

                $sale->products()->attach($productAttachments);

                $this->activityLog->logSaleAction(
                    $sale,
                    ActivityLog::ACTION_CREATED,
                    ['new' => $sale->toArray()]
                );

                return $sale;
            });

            return redirect()->back()->with('success', 'Sale created successfully');
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Throwable $e) {
            report($e);
            return redirect()->back()->with('error', $e->getMessage() ?? 'An unexpected error occurred. Please try again.');
        }
    }

    public function update(SaleRequest $saleRequest, Sale $sale)
    {
        try {
            DB::transaction(function () use ($saleRequest, $sale) {
                $validated = $saleRequest->validated();

                $oldData = $sale->toArray();

                $sale->update($this->saleService->prepareSaleData($validated));

                $productAttachments = $this->processProductInventory($validated['products'], 'update', $sale);

                $sale->products()->sync($productAttachments);

                $this->activityLog->logSaleAction(
                    $sale,
                    ActivityLog::ACTION_UPDATED,
                    ['old' => $oldData, 'new' => $sale->toArray()]
                );
            });

            return redirect()->back()->with('success', 'Sale updated successfully');
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Throwable $e) {
            report($e);
            return redirect()->back()->with('error', $e->getMessage() ?? 'An error occurred while updating the sale.');
        }
    }

    private function processProductInventory(array $products, string $action, ?Sale $existingSale = null): array
    {
        $productAttachments = [];
        $validationErrors = [];

        foreach ($products as $index => $product) {
            $product['quantity'] = round($product['quantity'], 2);

            $product_id = Product::where([
                'category_id' => $product['category_id'],
                'subcategory_id' => $product['subcategory_id']
            ])->value('id');

            if (!$product_id) {
                $validationErrors["products.{$index}.category_id"] =
                    "Product not found for category ID {$product['category_id']} and subcategory ID {$product['subcategory_id']}.";
                continue;
            }

            $inventory = Inventory::where([
                'category_id' => $product['category_id'],
                'subcategory_id' => $product['subcategory_id'],
                'unit_id' => $product['unit_id']
            ])->lockForUpdate()->first();

            if ($action === 'create') {
                $this->validateInventoryForCreate($inventory, $product, $products, $index, $validationErrors);
                $this->deductInventory($inventory, $product['quantity']);
            } elseif ($action === 'update' && $existingSale) {
                $this->validateInventoryForUpdate($inventory, $product, $products, $index, $existingSale, $product_id, $validationErrors);
            }

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

        return $productAttachments;
    }

    private function validateInventoryForCreate(?Inventory $inventory, array $product, array $products, int $index, array &$validationErrors)
    {
        if (!$inventory) {
            $validationErrors["products.{$index}.quantity"] = "Inventory not found.";
            return false;
        }

        if ($inventory->quantity < $product['quantity']) {
            $validationErrors["products.{$index}.quantity"] = "Requested quantity is insufficient. Only {$inventory->quantity} available.";
            return false;
        }

        foreach ($products as $key => $existingItem) {
            if ($key === $index) {
                continue;
            }

            if (
                $existingItem['category_id'] === $product['category_id'] &&
                $existingItem['subcategory_id'] === $product['subcategory_id'] &&
                $existingItem['unit_id'] === $product['unit_id']
            ) {
                $validationErrors["duplicate"] = "category, subcategory and unit already selected. Please pick another item.";
                return false;
            }
        }

        return true;
    }

    private function validateInventoryForUpdate(
        ?Inventory $inventory,
        array $product,
        array $products,
        int $index,
        Sale $existingSale,
        int $product_id,
        array &$validationErrors
    ): bool {
        $existingSaleProduct = $existingSale->products()->wherePivot('product_id', $product_id)->first();
        $oldSaleQuantity = $existingSaleProduct ? $existingSaleProduct->pivot->quantity : 0;
        $newSaleQuantity = $product['quantity'];

        $quantityDifference = round($oldSaleQuantity - $newSaleQuantity, 2);

        if (!$inventory || $inventory->quantity + $quantityDifference < 0) {
            $validationErrors["products.{$index}.quantity"] = "Requested quantity is insufficient. Only {$inventory->quantity} available.";
            return false;
        }

        $newQuantity = $inventory->quantity + $quantityDifference;
        $inventory->update(['quantity' => $newQuantity <= 0.01 ? 0 : $newQuantity]);

        foreach ($products as $key => $existingItem) {
            if ($key === $index) {
                continue;
            }

            if (
                $existingItem['category_id'] === $product['category_id'] &&
                $existingItem['subcategory_id'] === $product['subcategory_id'] &&
                $existingItem['unit_id'] === $product['unit_id']
            ) {
                $validationErrors["duplicate"] = "category, subcategory and unit already selected. Please pick another item.";
                return false;
            }
        }

        return true;
    }

    private function deductInventory(Inventory $inventory, float $quantityToDeduct)
    {
        $inventory->quantity = round(max(0, $inventory->quantity - $quantityToDeduct), 2);

        if ($inventory->quantity <= 0.01) {
            $inventory->quantity = 0;
        }

        $inventory->save();
    }

    public function destroy(Sale $sale)
    {
        try {
            DB::transaction(function () use ($sale) {

                foreach ($sale->products as $product) {
                    $inventory = Inventory::where([
                        'category_id' => $product->categories->id,
                        'subcategory_id' => $product->subcategories->id,
                        'unit_id' => $product->pivot->unit_id
                    ])->lockForUpdate()->first();

                    if ($inventory) {
                        $inventory->quantity += $product->pivot->quantity;
                        $inventory->save();
                    }
                }

                $sale->delete();

                $this->activityLog->logSaleAction(
                    $sale,
                    ActivityLog::ACTION_CANCELLED,
                    ['old' => $sale->toArray()]
                );
            });
            return redirect()->back()->with('success', 'Sale deleted successfully');
        } catch (\Throwable $e) {
            report($e);
            return redirect()->back()->with('error', $e->getMessage() ?? 'An error occurred while deleting the sale.');
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

        $this->activityLog->logSaleExport(
            $fileName,
            ActivityLog::ACTION_EXPORTED,
            ['old' => null, 'new' => null,]
        );

        return Excel::download($export, $fileName);
    }
}
