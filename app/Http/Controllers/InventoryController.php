<?php

namespace App\Http\Controllers;

use App\Exports\PurchaseInExport;
use App\Http\Requests\InventoryRequest;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Inventory;
use App\Models\Purchases;
use App\Models\Subcategory;
use App\Models\Supplier;
use App\Models\Transaction;
use App\Models\Unit;
use App\Services\ActivityLoggerService;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class InventoryController extends Controller
{
    protected $activityLog;
    protected $inventoryService;
    private $actor;

    public function __construct(ActivityLoggerService $activityLoggerService, InventoryService $inventoryService)
    {
        $this->activityLog = $activityLoggerService;
        $this->actor = Auth::user()->username;
        $this->inventoryService = $inventoryService;
    }

    public function index()
    {
        $units = Unit::all();
        $categories = Category::all();
        $subcategories = Subcategory::all();
        $transactions = Transaction::orderBy('type')->get();
        $suppliers = Supplier::orderBy('name')->get();

        $inventories = $this->inventoryService->getFormattedInventories();

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
        try {
            return DB::transaction(function () use ($inventoryRequest) {
                $data = $this->inventoryService->prepareInventoryData($inventoryRequest->validated());

                $inventory = Inventory::create($data);
                Purchases::create($data);

                $this->logInventoryActivity(
                    ActivityLog::ACTION_CREATED,
                    "created a new purchase",
                    $inventory,
                    null
                );

                return redirect()->back()->with('success', 'Transaction created successfully!');
            });
        } catch (\Exception $e) {
            report($e);
            return $this->handleError($e, 'Failed to create a transaction');
        }
    }

    public function update(InventoryRequest $inventoryRequest, Inventory $inventory)
    {
        try {
            return DB::transaction(function () use ($inventoryRequest, $inventory) {
                $oldData = $inventory->toArray();
                $validated = $inventoryRequest->validated();

                // Check if quantity is being modified
                $quantityChanged = isset($validated['quantity']) &&
                    $validated['quantity'] != $inventory->quantity;

                // Prepare update data
                $updateData = $this->inventoryService->prepareInventoryData($validated, false);

                // Validate quantity change if applicable
                if ($quantityChanged) {
                    $this->inventoryService->validateQuantityChange($inventory);
                    $updateData['quantity'] = $validated['quantity'];
                }

                // Perform updates
                $inventory->update($updateData);
                Purchases::where('id', $inventory->id)->update($updateData);

                $this->logInventoryActivity(
                    ActivityLog::ACTION_UPDATED,
                    "updated a purchase",
                    $inventory,
                    $oldData
                );

                return redirect()->back()->with('success', 'Transaction updated successfully!');
            });
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return $this->handleError($e, 'Failed to update transaction');
        }
    }

    public function destroy(Inventory $inventory)
    {
        try {
            return DB::transaction(function () use ($inventory) {
                $oldData = $inventory->toArray();

                $inventory->delete();
                Purchases::where('id', $inventory->id)->delete();

                $this->logInventoryActivity(
                    ActivityLog::ACTION_DELETED,
                    "deleted a purchase",
                    $inventory,
                    $oldData
                );

                return redirect()->back()->with('success', 'Transaction deleted successfully!');
            });
        } catch (\Exception $e) {
            return $this->handleError($e, 'Failed to delete transaction');
        }
    }

    public function export(Request $request)
    {
        sleep(1);

        try {
            $startDate = $request->query('start_date');
            $endDate = $request->query('end_date');
            $export = new PurchaseInExport($startDate, $endDate);

            $fileName = "purchase_in_report_" . now()->format('Ymd') . ".xlsx";

            $this->activityLog->logPurchaseExport(
                ActivityLog::ACTION_EXPORTED,
                "{$this->actor} exported purchase in report",
                ['old' => null, 'new' => null]
            );

            return Excel::download($export, $fileName);
        } catch (\Exception $e) {
            return $this->handleError($e, 'Failed to export data');
        }
    }

    private function logInventoryActivity(string $action, string $message, Inventory $inventory, ?array $oldData): void
    {
        $this->activityLog->logInventoryAction(
            $action,
            "{$this->actor} {$message}: #{$inventory->transaction_number}",
            [
                'old' => $oldData,
                'new' => $inventory->toArray()
            ]
        );
    }

    private function handleError(\Exception $e, string $defaultMessage): \Illuminate\Http\RedirectResponse
    {
        report($e);
        return redirect()->back()->with('error', $e->getMessage() ?? $defaultMessage);
    }
}
