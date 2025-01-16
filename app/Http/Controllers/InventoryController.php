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
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class InventoryController extends Controller
{
    protected $activityLog;
    private $actor;

    public function __construct(ActivityLoggerService $activityLoggerService)
    {
        $this->activityLog = $activityLoggerService;
        $this->actor = Auth::user()->username;
    }

    public function index()
    {
        $units = Unit::all();
        $categories = Category::all();
        $subcategories = Subcategory::all();
        $transactions = Transaction::orderBy('type')->get();
        $suppliers = Supplier::orderBy('name')->get();
        $inventories = Purchases::with('units', 'suppliers', 'categories', 'subcategories', 'transactions')
            ->orderBy('purchases.id', 'desc')
            ->get()
            ->map(function ($inventory) {
                $purchase_date = Carbon::parse($inventory->purchase_date)->format('M j, Y');
                return [
                    'id' => $inventory->id,
                    'quantity' => $inventory->quantity,
                    'purchase_date' => $purchase_date,
                    'amount' => $inventory->amount,
                    'transaction_number' => $inventory->transaction_number,
                    'landed_cost' => $inventory->landed_cost,
                    'description' => $inventory->description,
                    'abbreviation' => $inventory->units->abbreviation,
                    'unit_id' => $inventory->units->id,
                    'supplier_id' => $inventory->suppliers->id,
                    'supplier_name' => $inventory->suppliers->name,
                    'supplier_email' => $inventory->suppliers->email,
                    'supplier_address1' => $inventory->suppliers->address1,
                    'supplier_address2' => $inventory->suppliers->address2,
                    'supplier_contact_person' => $inventory->suppliers->contact_person,
                    'supplier_contact_number' => $inventory->suppliers->contact_number,
                    'category_id' => $inventory->categories->id,
                    'category_name' => $inventory->categories->name,
                    'subcategory_id' => $inventory->subcategories->id,
                    'subcategory_name' => $inventory->subcategories->name,
                    'transaction_type' => $inventory->transactions->type,
                    'transaction_id' => $inventory->transactions->id,
                ];
            });

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

                $purchase_date = Carbon::parse($validated['purchase_date'])->format('Y-m-d');

                $data = [
                    'amount' => $validated['amount'],
                    'purchase_date' => $purchase_date,
                    'unit_id' => $validated['unit_id'],
                    'quantity' => $validated['quantity'],
                    'description' => $validated['description'],
                    'landed_cost' => $validated['landed_cost'],
                    'supplier_id' => $validated['supplier_id'],
                    'category_id' => $validated['category_id'],
                    'subcategory_id' => $validated['subcategory_id'],
                    'transaction_id' => $validated['transaction_id'],
                    'transaction_number' => $validated['transaction_number'],
                ];
                $inventory = Inventory::create($data);
                Purchases::create($data);

                $this->activityLog->logInventoryAction(
                    ActivityLog::ACTION_CREATED,
                    "{$this->actor} created a new purchase: #{$inventory->transaction_number}",
                    ['new' => $inventory->toArray()]
                );
            });
            return redirect()->back()->with('success', 'Transaction created successfully!');
        } catch (\Exception $e) {
            report($e);
            return redirect()->back()->with('error', $e->getMessage() ?? 'Failed to create a transaction');
        }
    }

    public function update(InventoryRequest $inventoryRequest, Inventory $inventory)
    {
        $validated = $inventoryRequest->validated();

        try {
            DB::transaction(function () use ($validated, $inventory) {
                $oldData = $inventory->toArray();

                $purchase_date = Carbon::parse($validated['purchase_date'])->format('Y-m-d');

                $data = [
                    'amount' => $validated['amount'],
                    'unit_id' => $validated['unit_id'],
                    'quantity' => $validated['quantity'],
                    'description' => $validated['description'],
                    'landed_cost' => $validated['landed_cost'],
                    'supplier_id' => $validated['supplier_id'],
                    'category_id' => $validated['category_id'],
                    'purchase_date' => $purchase_date,
                    'subcategory_id' => $validated['subcategory_id'],
                    'transaction_id' => $validated['transaction_id'],
                    'transaction_number' => $validated['transaction_number'],
                ];

                $inventory->update($data);
                Purchases::where('id', $inventory->id)->update($data);


                $this->activityLog->logInventoryAction(
                    ActivityLog::ACTION_UPDATED,
                    "{$this->actor} updated a purchase: #{$inventory->transaction_number}",
                    ['old' => $oldData, 'new' => $inventory->toArray()]
                );
            });
            return redirect()->back()->with('success', 'Transaction updated successfully!');
        } catch (\Exception $e) {
            report($e);
            return redirect()->back()->with('error', $e->getMessage() ?? 'Failed to update transaction');
        }
    }

    public function destroy(Inventory $inventory)
    {
        try {
            DB::transaction(function () use ($inventory) {
                $inventory->delete();
                Purchases::where('id', $inventory->id)->delete();

                $this->activityLog->logInventoryAction(
                    ActivityLog::ACTION_DELETED,
                    "{$this->actor} deleted a purchase: #{$inventory->transaction_number}",
                    ['old' => $inventory->toArray()]
                );
            });

            return redirect()->back()->with('success', 'Transaction deleted successfully!');
        } catch (\Exception $e) {
            report($e);
            return redirect()->back()->with('error', $e->getMessage() ?? 'Failed to delete transaction');
        }
    }

    public function export(Request $request)
    {
        sleep(1);

        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $export = new PurchaseInExport($startDate, $endDate);

        $date = now()->format('Ymd');
        $fileName = "purchase_in_report_{$date}.xlsx";

        $this->activityLog->logPurchaseExport(
            ActivityLog::ACTION_EXPORTED,
            "{$this->actor} exported purchase in report",
            ['old' => null, 'new' => null,]
        );

        return Excel::download($export, $fileName);
    }
}
