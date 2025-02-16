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

        foreach ($products as $index => $product) {
            // Get all matching inventory records
            $inventory = Inventory::with('inventory_sale');

            $inventories = $inventory->where([
                'category_id' => $product['category_id'],
                'subcategory_id' => $product['subcategory_id'],
                'unit_id' => $product['unit_id']
            ])->lockForUpdate()
                ->get();

            $totalQuantityInput = round($product['quantity'], 2);
            $currentTotalQuantity = $inventories->sum('quantity');

            if ($inventories->isEmpty()) {
                $validationErrors["products.{$index}.category_id"] = "Item not found.";
                continue;
            }

            // Validate total available quantity
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
                        "{$category} {$subcategory} ({$unit}) already exists. Please choose a different item.";
                    break;
                }
            }

            // Handle product deletion during update
            if ($action === 'update' && $productDeleted) {
                foreach ($productDeleted as $deletedProduct) {
                    $returnInventory = Inventory::where([
                        'category_id' => $deletedProduct['category_id'],
                        'subcategory_id' => $deletedProduct['subcategory_id'],
                        'unit_id' => $deletedProduct['unit_id']
                    ])->lockForUpdate()->first();

                    if ($returnInventory) {
                        $returnInventory->increment('quantity', $deletedProduct['quantity']);
                    }
                }
            }

            // Process each inventory record
            foreach ($inventories as $inventory) {
                if ($totalQuantityInput <= 0) {
                    break;
                }

                $quantityFromThis = min($totalQuantityInput, $inventory->quantity);

                // Calculate proportional amount
                $unitAmount = $product['amount'] / $product['quantity'];
                $amountForThis = round($quantityFromThis * $unitAmount, 2);

                if ($action === 'create') {
                    // Deduct from inventory
                    $inventory->quantity -= $quantityFromThis;
                    $inventory->save();
                } elseif ($action === 'update' && $existingSale) {
                    // To Do
                    foreach ($existingSale->inventory_sale as $saleItem) {
                        $inventory = $inventory->where([
                            'id' => $saleItem->pivot->secondary_id,
                            'category_id' => $saleItem->pivot->category_id,
                            'subcategory_id' => $saleItem->pivot->subcategory_id,
                            'unit_id' => $saleItem->pivot->unit_id
                        ])->get();
                        dd($inventory);
                    }
                }

                $processedInventoryIds[] = $inventory->id;

                if (!empty($processedInventoryIds)) {
                    $inventoriesToCheck = Inventory::whereIn('id', array_unique($processedInventoryIds))
                        ->lockForUpdate()
                        ->get();
                    foreach ($inventoriesToCheck as $inventory) {
                        $roundedQuantity = round($inventory->quantity, 2);
                        if ($roundedQuantity <= 0.01) {
                            $inventory->update(['quantity' => 0]);
                        }
                    }
                }

                // Record the attachment
                $productAttachments[$inventory->id] = [
                    'amount' => $amountForThis,
                    'quantity' => $quantityFromThis,
                    'secondary_id' => $inventory->id,
                    'unit_id' => $product['unit_id'],
                    'category_id' => $product['category_id'],
                    'selling_price' => $product['selling_price'],
                    'subcategory_id' => $product['subcategory_id'],
                    'inventory_id' => $inventory->id === $inventory->id,
                ];

                $totalQuantityInput -= $quantityFromThis;
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

    private function validateInventoryForCreate(
        ?Inventory $inventory,
        array $product,
        array $products,
        int $index,
        array &$validationErrors
    ): void {
        // Collect all validation errors before returning
        if (!$inventory) {
            $validationErrors["products.{$index}.quantity"] = "Inventory not found.";
        }

        $currentInventory = $inventory->where([
            'category_id' => $product['category_id'],
            'subcategory_id' => $product['subcategory_id'],
            'unit_id' => $product['unit_id']
        ])->get();

        $totalQuantity = $currentInventory->sum('quantity');

        if ($totalQuantity < 0) {
            $validationErrors["products.{$index}.quantity"] = "Requested quantity is insufficient. Only {$totalQuantity} available.";
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
                // Get the category, subcategory, and unit names
                $category = Category::find($product['category_id'])->name;
                $subcategory = Subcategory::find($product['subcategory_id'])->name;
                $unit = Unit::find($product['unit_id'])->abbreviation;

                $validationErrors["products.{$index}.duplicate"] = "{$category} {$subcategory} ({$unit}) already exists. Please choose a different item.";
                break; // Break after finding first duplicate
            }
        }
    }

    private function validateInventoryForUpdate(?Inventory $inventory, array $product, array $products, int $index, Sale $existingSale, array &$validationErrors, array $productDeleted): bool
    {
        // Ensure inventory exists
        if (!$inventory) {
            $validationErrors["products.{$index}.inventory"] = "Inventory not found.";
            return false;
        }

        // Fetch the existing sale product and its quantity
        $existingSaleProduct = $existingSale->inventory_sale()->wherePivot('inventory_id', $inventory->id)->first();
        $oldSaleQuantity = $existingSaleProduct ? $existingSaleProduct->pivot->sum('quantity') : 0;

        // Calculate new sale quantities and differences
        $newSaleQuantity = $product['quantity'];
        $quantityDifference = round($oldSaleQuantity - $newSaleQuantity, 2);

        // Fetch all matching inventory items, ordered by priority (e.g., by ID)
        $matchingInventoryItems = $inventory->where([
            'category_id' => $product['category_id'],
            'subcategory_id' => $product['subcategory_id'],
            'unit_id' => $product['unit_id']
        ])->get();

        // Calculate the total quantity of matching items
        $totalQuantity = $matchingInventoryItems->sum('quantity');
        $newTotalQuantity = $totalQuantity + $quantityDifference;

        // Check if the inventory quantity is sufficient
        if ($newTotalQuantity < 0) {
            $validationErrors["products.{$index}.quantity"] = "Requested quantity is insufficient. Only {$totalQuantity} available.";
            return false;
        }

        if ($productDeleted) {
            foreach ($productDeleted as $index => $deletedProduct) {
                $returnDeletedProduct = Inventory::where([
                    'category_id' => $deletedProduct['category_id'],
                    'subcategory_id' => $deletedProduct['subcategory_id'],
                    'unit_id' => $deletedProduct['unit_id']
                ])->get();

                foreach ($returnDeletedProduct as $item) {
                    $item->increment('quantity', $deletedProduct['quantity']);
                }
            }
        }

        // Handle edge case where new total quantity is very small
        if ($newTotalQuantity <= 0.01) {
            foreach ($matchingInventoryItems as $item) {
                $item->update(['quantity' => 0]);
            }
            return true; // Early exit since all quantities are set to zero
        }

        // Consume inventory items based on priority
        $remainingDifference = abs($quantityDifference);

        foreach ($existingSale->inventory_sale as $saleItem) {
            $inventory = $inventory->with('inventory_sale')
                ->where([
                    'id' => $saleItem->pivot->secondary_id,
                    'category_id' => $saleItem->pivot->category_id,
                    'subcategory_id' => $saleItem->pivot->subcategory_id,
                    'unit_id' => $saleItem->pivot->unit_id
                ])->get();

            if ($quantityDifference < 0) {
                $consumable = min($remainingDifference, $item->quantity);
                $inventory->decrement('quantity', $consumable);
                $remainingDifference -= $consumable;
            } else {
                $inventory->increment('quantity', $remainingDifference);
                $remainingDifference = 0; // Fully replenished
                break;
            }
        }

        foreach ($products as $key => $existingItem) {
            if ($key === $index) {
                continue;
            }

            // Check for duplicates in the product list
            if (
                $existingItem['category_id'] === $product['category_id'] &&
                $existingItem['subcategory_id'] === $product['subcategory_id'] &&
                $existingItem['unit_id'] === $product['unit_id']
            ) {
                // Get the category, subcategory, and unit names
                $category = Category::find($product['category_id'])->name;
                $subcategory = Subcategory::find($product['subcategory_id'])->name;
                $unit = Unit::find($product['unit_id'])->abbreviation;

                $validationErrors["products.{$index}.duplicate"] = "{$category} {$subcategory} ({$unit}) already exists. Please choose a different item.";
                break;
            }
        }

        // Validation successful
        return true;
    }

    private function deductInventory(Inventory $inventory, array $product)
    {
        // Find all matching inventory items, ordered by creation date (oldest first)
        $currentInventory = $inventory->where([
            'category_id' => $product['category_id'],
            'subcategory_id' => $product['subcategory_id'],
            'unit_id' => $product['unit_id']
        ])
            ->orderBy('created_at', 'asc')
            ->get();

        if ($currentInventory->isEmpty()) {
            return false;
        }

        $totalQuantity = $currentInventory->sum('quantity');

        // If trying to deduct more than available, return false
        if ($totalQuantity < $product['quantity']) {
            return false;
        }

        $remainingToDeduct = $product['quantity'];

        // Process each inventory item in FIFO order
        foreach ($currentInventory as $item) {
            if ($remainingToDeduct <= 0) {
                break;
            }

            $deductFromCurrent = min($item->quantity, $remainingToDeduct);
            $newQuantity = round(max(0, $item->quantity - $deductFromCurrent), 2);

            if ($newQuantity <= 0.01) {
                $item->update(['quantity' => 0]);
            } else {
                $item->update(['quantity' => $newQuantity]);
            }

            $remainingToDeduct -= $deductFromCurrent;
        }
        return true;
    }

    public function destroy(Sale $sale)
    {
        try {
            DB::transaction(function () use ($sale) {

                foreach ($sale->inventory_sale as $product) {
                    $inventory = Inventory::where([
                        'id' => $product->pivot->secondary_id,
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
