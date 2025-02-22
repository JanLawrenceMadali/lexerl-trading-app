<?php

namespace App\Http\Controllers;

use App\Exports\SalesDetailedExport;
use App\Exports\SalesSummaryExport;
use App\Http\Requests\SaleRequest;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Customer;
use App\Models\DueDate;
use App\Models\Inventory;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Subcategory;
use App\Models\Transaction;
use App\Models\Unit;
use App\Services\ActivityLoggerService;
use App\Services\SaleService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

use function Pest\Laravel\get;

class SalesController extends Controller
{
    protected $activityLog;
    protected $saleService;
    private $actor;
    private const MAX_TRANSACTION_RETRIES = 3;
    private const INVENTORY_LOCK_TIMEOUT = 5;

    public function __construct(
        SaleService $saleService,
        ActivityLoggerService $activityLoggerService
    ) {
        $this->saleService = $saleService;
        $this->activityLog = $activityLoggerService;
        $this->actor = Auth::user()->username;
    }

    public function index()
    {
        $units = Unit::orderBy('name')->get();
        $dues = DueDate::get();
        $categories = Category::orderBy('name')->get();
        $subcategories = Subcategory::orderBy('name')->get();
        $customers = Customer::orderBy('name')->get();
        $transactions = Transaction::where('id', '!=', 1)->orderBy('type')->get();
        $inventories = $this->saleService->getInventories();
        $sales = $this->saleService->getSales();

        return inertia('Transactions/Sales/Index', [
            'dues' => $dues,
            'units' => $units,
            'sales' => $sales,
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
            return $this->executeWithRetry(function () use ($validated) {
                $sale = null;
                $inventoryAttachments = [];
                try {
                    // Prepare and validate all data first
                    $saleData = $this->saleService->prepareSaleData($validated);
                    $inventoryAttachments = $this->processProductInventory($validated['products'], 'create');

                    // Create sale
                    $sale = Sale::create($saleData);

                    // Attach products
                    $sale->inventory_sale()->attach($inventoryAttachments);

                    $this->activityLog->logSaleAction(
                        ActivityLog::ACTION_CREATED,
                        "{$this->actor} created sale: #{$sale->transaction_number}",
                        ['new' => $sale->toArray()]
                    );

                    return redirect()->back()->with('success', 'Transaction added successfully!');
                } catch (\Throwable $e) {
                    // If we created the sale but failed to attach products, clean up
                    if ($sale !== null) {
                        $sale->delete();
                    }
                    throw $e;
                }
            });
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Throwable $e) {
            report($e);
            return redirect()->back()->with('error', $e->getMessage() ?? 'Failed to add transaction');
        }
    }

    public function update(SaleRequest $saleRequest, Sale $sale)
    {
        $validated = $saleRequest->validated();

        try {
            DB::transaction(function () use ($validated, $sale) {

                $oldData = $sale->toArray();

                $products = [];

                foreach ($validated['products'] as $product) {
                    $products[] = [
                        'amount' => $product['amount'],
                        'unit_id' => $product['unit_id'],
                        'quantity' => $product['quantity'],
                        'category_id' => $product['category_id'],
                        'selling_price' => $product['selling_price'],
                        'subcategory_id' => $product['subcategory_id'],
                    ];
                }

                $commonData = [
                    'sale_date' => Carbon::parse($validated['sale_date'])->format('Y-m-d'),
                    'status_id' => $validated['status_id'],
                    'due_date_id' => $validated['due_date_id'],
                    'description' => $validated['description'],
                    'customer_id' => $validated['customer_id'],
                    'total_amount' => $validated['total_amount'],
                    'transaction_id' => $validated['transaction_id'],
                    'transaction_number' => $validated['transaction_number'],
                    'products' => $products,
                ];

                $sale->update($this->saleService->prepareSaleData($commonData));

                $inventoryAttachments = $this->processProductInventory($products, 'update', $sale, $validated['productDeleted']);

                $sale->inventory_sale()->sync($inventoryAttachments);

                $this->activityLog->logSaleAction(
                    ActivityLog::ACTION_UPDATED,
                    "{$this->actor} updated a sale: #{$sale->transaction_number}",
                    ['old' => $oldData, 'new' => $sale->toArray()]
                );
            });

            return redirect()->back()->with('success', 'Transaction updated successfully!');
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Throwable $e) {
            report($e);
            return redirect()->back()->with('error', $e->getMessage() ?? 'Failed to update transaction');
        }
    }

    private function processProductInventory(array $products, string $action, ?Sale $existingSale = null, array $productDeleted = null): array
    {
        $productAttachments = [];
        $validationErrors = [];
        $processedInventoryIds = [];

        // **Handle product deletion first (only for updates)**
        if ($action === 'update' && !empty($productDeleted)) {
            foreach ($productDeleted as $deletedProduct) {
                $returnInventory = Inventory::where([
                    'category_id' => $deletedProduct['category_id'],
                    'subcategory_id' => $deletedProduct['subcategory_id'],
                    'unit_id' => $deletedProduct['unit_id']
                ])->first();

                if ($returnInventory) {
                    $returnInventory->increment('quantity', $deletedProduct['quantity']);
                }
            }
        }

        // **Lock necessary inventory records upfront to reduce deadlocks**
        $inventoryIds = Inventory::whereIn('category_id', array_column($products, 'category_id'))
            ->whereIn('subcategory_id', array_column($products, 'subcategory_id'))
            ->whereIn('unit_id', array_column($products, 'unit_id'))
            ->pluck('id');

        $lockedInventories = Inventory::with('inventory_sale')->whereIn('id', $inventoryIds)->get();

        foreach ($products as $index => $product) {
            $inventories = $lockedInventories->where('category_id', $product['category_id'])
                ->where('subcategory_id', $product['subcategory_id'])
                ->where('unit_id', $product['unit_id']);

            $totalQuantityInput = round($product['quantity'], 2);
            $currentTotalQuantity = $inventories->sum('quantity');

            if ($inventories->isEmpty()) {
                $validationErrors["products.{$index}.category_id"] = "Item not found.";
                continue;
            }

            // **Validate available quantity**
            if ($action === 'create' && $currentTotalQuantity < $totalQuantityInput) {
                $validationErrors["products.{$index}.quantity"] = "Insufficient stock. Only {$currentTotalQuantity} units available.";
                continue;
            }

            // **Check for duplicates**
            foreach ($products as $key => $existingItem) {
                if (
                    $key !== $index &&
                    $existingItem['category_id'] === $product['category_id'] &&
                    $existingItem['subcategory_id'] === $product['subcategory_id'] &&
                    $existingItem['unit_id'] === $product['unit_id']
                ) {

                    $category = Category::find($product['category_id'])->name;
                    $subcategory = Subcategory::find($product['subcategory_id'])->name;
                    $unit = Unit::find($product['unit_id'])->abbreviation;

                    $validationErrors["products.{$index}.duplicate"] =
                        "{$category}, {$subcategory} and, {$unit} are already exists in the list of item. Please choose a different item.";
                    break;
                }
            }

            // **Process inventory adjustments**
            foreach ($inventories as $inventory) {
                $processedInventoryIds[] = $inventory->id;
                $unitAmount = $product['amount'] / $product['quantity'];

                if ($action === 'create') {
                    if ($inventory->quantity == 0) continue;

                    $quantityFromThis = min($totalQuantityInput, $inventory->quantity);
                    $amountForThis = round($quantityFromThis * $unitAmount, 2);

                    // Deduct from inventory
                    $inventory->decrement('quantity', $quantityFromThis);

                    $productAttachments[$inventory->id] = [
                        'amount' => $amountForThis,
                        'purchase_id' => $inventory->id,
                        'quantity' => $quantityFromThis,
                        'unit_id' => $product['unit_id'],
                        'category_id' => $product['category_id'],
                        'selling_price' => $product['selling_price'],
                        'subcategory_id' => $product['subcategory_id'],
                        'inventory_id' => $inventory->id === $inventory->id,
                    ];
                    $totalQuantityInput -= $quantityFromThis;
                } elseif ($action === 'update') {
                    // To Do: Implement update logic
                }
            }
        }

        // **Round very small inventory values to zero**
        if (!empty($processedInventoryIds)) {
            $inventoriesToCheck = Inventory::whereIn('id', array_unique($processedInventoryIds))
                ->lockForUpdate()
                ->get();

            foreach ($inventoriesToCheck as $inventoryArray) {
                if (round($inventoryArray->quantity, 2) <= 0.01) {
                    $inventoryArray->update(['quantity' => 0]);
                }
            }
        }

        if (!empty($validationErrors)) {
            throw ValidationException::withMessages($validationErrors);
        }

        return $productAttachments;
    }

    private function executeWithRetry(callable $operation)
    {
        $attempts = 0;
        do {
            try {
                return DB::transaction($operation, self::INVENTORY_LOCK_TIMEOUT);
            } catch (\Throwable $e) {
                $attempts++;
                if ($attempts >= self::MAX_TRANSACTION_RETRIES || !$this->isRetryableException($e)) {
                    throw $e;
                }
                sleep(1); // Basic exponential backoff
            }
        } while (true);
    }

    private function isRetryableException(\Throwable $e): bool
    {
        return $e instanceof \PDOException &&
            in_array($e->getCode(), [1213, 1205]); // MySQL deadlock codes
    }

    public function destroy(Sale $sale)
    {
        try {
            DB::transaction(function () use ($sale) {

                foreach ($sale->inventory_sale as $product) {
                    $inventory = Inventory::where([
                        'id' => $product->pivot->purchase_id
                    ])->lockForUpdate()->firstOrFail();

                    if ($inventory) {
                        // Validate inventory quantity doesn't exceed any maximum limits
                        $newQuantity = $inventory->quantity + $product->pivot->quantity;
                        $inventory->quantity = $newQuantity;
                        $inventory->save();
                    } else {
                        throw ValidationException::withMessages([
                            'inventory' => ['Inventory record not found for one or more products']
                        ]);
                    }
                }

                $sale->delete();

                $this->activityLog->logSaleAction(
                    ActivityLog::ACTION_CANCELLED,
                    "{$this->actor} cancelled a sale: #{$sale->transaction_number}",
                    ['old' => $sale->toArray()]
                );
            });

            return redirect()->back()->with('success', 'Transaction deleted successfully!');
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->getMessages())->withInput();
        } catch (\Throwable $e) {
            report($e);
            return redirect()->back()->with('error', $e->getMessage() ?? 'Failed to delete transaction');
        }
    }

    public function summary_export(Request $request)
    {
        sleep(1);

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $export = new SalesSummaryExport($startDate, $endDate);

        $date = now()->format('Ymd');
        $fileName = "sales_summary_report_{$date}.xlsx";

        $this->activityLog->logSaleExport(
            ActivityLog::ACTION_EXPORTED,
            "{$this->actor} exported sales summary report",
            ['old' => null, 'new' => null,]
        );

        return Excel::download($export, $fileName);
    }

    public function detailed_export(Request $request)
    {
        sleep(1);

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $export = new SalesDetailedExport($startDate, $endDate);

        $date = now()->format('Ymd');
        $fileName = "sales_detailed_report_{$date}.xlsx";

        $this->activityLog->logSaleExport(
            ActivityLog::ACTION_EXPORTED,
            "{$this->actor} exported sales detailed report",
            ['old' => null, 'new' => null,]
        );

        return Excel::download($export, $fileName);
    }
}
