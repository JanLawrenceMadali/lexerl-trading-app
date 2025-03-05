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

                $sale->inventory_sale()->syncWithoutDetaching($inventoryAttachments);

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
        $inventoryAttachments = [];
        $validationErrors = [];

        if ($action === 'update' && !empty($productDeleted)) {
            foreach ($productDeleted as $deletedProduct) {
                $returnInventories = Inventory::with('inventory_sale')->where([
                    'category_id' => $deletedProduct['category_id'],
                    'subcategory_id' => $deletedProduct['subcategory_id'],
                    'unit_id' => $deletedProduct['unit_id']
                ])->orderBy('id')->get();

                $quantityToReturn = $deletedProduct['quantity'];

                foreach ($returnInventories as $returnInventory) {
                    if ($quantityToReturn <= 0) break;

                    $restoreQty = min($quantityToReturn, $deletedProduct['quantity']);
                    $returnInventory->increment('quantity', $restoreQty);
                    $returnInventory->update(['amount' => $returnInventory->quantity * $returnInventory->landed_cost]);
                    $returnInventory->inventory_sale()->detach($existingSale->id);
                    $quantityToReturn -= $restoreQty;
                }
            }
        }

        $inventoryIds = Inventory::whereIn('category_id', array_column($products, 'category_id'))
            ->whereIn('subcategory_id', array_column($products, 'subcategory_id'))
            ->whereIn('unit_id', array_column($products, 'unit_id'))
            ->pluck('id');

        $lockedInventories = Inventory::with('inventory_sale')->whereIn('id', $inventoryIds)
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

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

            // Validate stock on create
            if ($action === 'create' && $currentTotalQuantity < $totalQuantityInput) {
                $validationErrors["products.{$index}.quantity"] = "Insufficient stock. Only {$currentTotalQuantity} units available.";
                continue;
            }

            // Check for duplicates in the products list
            foreach ($products as $key => $existingItem) {
                if ($key === $index) {
                    continue;
                }

                if (
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

            $availableInventories = $inventories->sortBy('id');

            if ($action === 'create') {
                $currentQuantity = $inventories->sum('quantity');
                $quantityDifference = $totalQuantityInput - $currentQuantity;
                $quantityToDeduct = abs($quantityDifference);

                foreach ($availableInventories as $inventory) {
                    if ($quantityToDeduct <= 0) break;

                    $deductQty = min($quantityToDeduct, $inventory->quantity);

                    $inventory->decrement('quantity', $deductQty);
                    if (round($inventory->quantity, 2) <= 0.01) {
                        $inventory->update([
                            'quantity' => 0,
                            'amount' => 0
                        ]);
                    } else {
                        $inventory->update(['amount' => $inventory->quantity * $inventory->landed_cost]);
                    }

                    $newQuantity = $deductQty - $inventory->quantity;
                    dd($deductQty, $inventory->quantity);
                    $inventoryAttachments[$inventory->id] = [
                        'quantity' => $newQuantity,
                        'amount' => $newQuantity * $inventory->landed_cost,
                        'unit_id' => $product['unit_id'],
                        'category_id' => $product['category_id'],
                        'selling_price' => $product['selling_price'],
                        'subcategory_id' => $product['subcategory_id'],
                    ];

                    $quantityToDeduct -= $deductQty;
                }

                if ($quantityToDeduct > 0) {
                    $validationErrors["products.{$index}.quantity"] = "Insufficient stock. Only {$currentTotalQuantity} units available.";
                    continue;
                }
            }

            if ($action === 'update' && $existingSale) {
                // Get existing inventory sales for this product
                $existingInventorySales = $existingSale->inventory_sale()
                    ->where([
                        'inventory_sale.unit_id' => $product['unit_id'],
                        'inventory_sale.category_id' => $product['category_id'],
                        'inventory_sale.subcategory_id' => $product['subcategory_id']
                    ])
                    ->get();


                $currentQuantity = $existingInventorySales->sum('pivot.quantity');
                $quantityDifference = $totalQuantityInput - $currentQuantity;

                if ($quantityDifference < 0) {
                    $quantityToReturn = abs($quantityDifference);
                    $returnInventorySales = $existingInventorySales->sortByDesc('pivot.inventory_id');

                    foreach ($returnInventorySales as $inventorySale) {
                        if ($quantityToReturn <= 0) break;

                        $originalInventory = $inventories->firstWhere('id', $inventorySale->pivot->inventory_id);
                        $deductAmount = min($quantityToReturn, $inventorySale->pivot->quantity);

                        $originalInventory->increment('quantity', $deductAmount);
                        if (round($originalInventory->quantity, 2) <= 0.01) {
                            $originalInventory->update([
                                'quantity' => 0,
                                'amount' => 0
                            ]);
                        } else {
                            $originalInventory->update(['amount' => $originalInventory->quantity * $originalInventory->landed_cost]);
                        }

                        $newQuantity = $inventorySale->pivot->quantity - $deductAmount;
                        $inventoryAttachments[$inventorySale->pivot->inventory_id] = [
                            'quantity' => $newQuantity,
                            'amount' => $newQuantity * $inventorySale->pivot->selling_price,
                            'unit_id' => $product['unit_id'],
                            'category_id' => $product['category_id'],
                            'selling_price' => $product['selling_price'],
                            'subcategory_id' => $product['subcategory_id'],
                        ];
                        $quantityToReturn -= $deductAmount;
                    }

                    while ($quantityToReturn > 0) {
                        $nextInventory = $inventories
                            ->where('unit_id', $product['unit_id'])
                            ->where('category_id', $product['category_id'])
                            ->where('subcategory_id', $product['subcategory_id'])
                            ->where('quantity', '>', 0)
                            ->first();

                        if (!$nextInventory) break; // No more matching inventory to deduct from

                        $deductAmount = min($quantityToReturn, $nextInventory->quantity);

                        $nextInventory->decrement('quantity', $deductAmount);
                        $nextInventory->update(['amount' => round($nextInventory->quantity * $nextInventory->landed_cost, 2)]);

                        $inventoryAttachments[$nextInventory->id] = [
                            'quantity' => $nextInventory->quantity,
                            'amount' => $nextInventory->quantity * $inventorySale->pivot->selling_price,
                            'unit_id' => $product['unit_id'],
                            'category_id' => $product['category_id'],
                            'selling_price' => $product['selling_price'],
                            'subcategory_id' => $product['subcategory_id'],
                        ];

                        $quantityToReturn -= $deductAmount;
                    }
                } elseif ($quantityDifference > 0) {
                    $quantityToDeduct = $quantityDifference;

                    $availableInventories = $inventories
                        ->where('unit_id', $product['unit_id'])
                        ->where('category_id', $product['category_id'])
                        ->where('subcategory_id', $product['subcategory_id']);

                    foreach ($availableInventories as $inventory) {
                        if ($quantityToDeduct <= 0) break;

                        $originalInventorySale = $existingInventorySales->firstWhere('pivot.inventory_id', $inventory->id);

                        if (!$originalInventorySale) {
                            continue;
                        }

                        $deduction = min($quantityToDeduct, $inventory->quantity);

                        $inventory->decrement('quantity', $deduction);
                        if (round($inventory->quantity, 2) <= 0.01) {
                            $inventory->update([
                                'quantity' => 0,
                                'amount' => 0
                            ]);
                        } else {
                            $inventory->update(['amount' => $inventory->quantity * $inventory->landed_cost]);
                        }

                        $newQuantity = $originalInventorySale ? $originalInventorySale->pivot->quantity + $deduction : $deduction;

                        $inventoryAttachments[$inventory->id] = [
                            'quantity' => $newQuantity,
                            'amount' => $newQuantity * $product['selling_price'],
                            'unit_id' => $product['unit_id'],
                            'category_id' => $product['category_id'],
                            'subcategory_id' => $product['subcategory_id'],
                            'selling_price' => $product['selling_price'],
                        ];

                        $quantityToDeduct -= $deduction;
                    }

                    while ($quantityToDeduct > 0) {
                        $nextInventory = Inventory::where('unit_id', $product['unit_id'])
                            ->where('category_id', $product['category_id'])
                            ->where('subcategory_id', $product['subcategory_id'])
                            ->where('quantity', '>', 0)
                            ->first();

                        if (!$nextInventory) {
                            $validationErrors["products.{$index}.quantity"] = "Insufficient inventory. Please restock and try again.";
                            break;
                        }

                        $deduction = min($quantityToDeduct, $nextInventory->quantity);

                        $nextInventory->decrement('quantity', $deduction);
                        $nextInventory->update(['amount' => $nextInventory->quantity * $nextInventory->landed_cost]);

                        $inventoryAttachments[$nextInventory->id] = [
                            'quantity' => $deduction,
                            'amount' => $deduction * $product['selling_price'],
                            'unit_id' => $product['unit_id'],
                            'category_id' => $product['category_id'],
                            'subcategory_id' => $product['subcategory_id'],
                            'selling_price' => $product['selling_price'],
                        ];

                        $quantityToDeduct -= $deduction;
                    }
                } else {
                    foreach ($existingInventorySales as $inventorySale) {
                        $inventoryAttachments[$inventorySale->pivot->inventory_id] = [
                            'amount' => $inventorySale->pivot->amount,
                            'quantity' => $inventorySale->pivot->quantity,
                            'unit_id' => $product['unit_id'],
                            'category_id' => $product['category_id'],
                            'selling_price' => $product['selling_price'],
                            'subcategory_id' => $product['subcategory_id'],
                        ];
                    }
                }
            }
        }

        if (!empty($validationErrors)) {
            throw ValidationException::withMessages($validationErrors);
        }

        return $inventoryAttachments;
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
                        'id' => $product->pivot->inventory_id
                    ])->lockForUpdate()->firstOrFail();

                    if ($inventory) {
                        // Validate inventory quantity doesn't exceed any maximum limits
                        $newQuantity = $inventory->quantity + $product->pivot->quantity;
                        $inventory->update(['quantity' => $newQuantity]);
                        $inventory->update(['amount' => $inventory->quantity * $inventory->landed_cost]);
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
