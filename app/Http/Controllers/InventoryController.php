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
        $inventories = DB::table('purchases')
            ->join('units', 'purchases.unit_id', '=', 'units.id')
            ->join('suppliers', 'purchases.supplier_id', '=', 'suppliers.id')
            ->join('transactions', 'purchases.transaction_id', '=', 'transactions.id')
            ->join('categories', 'purchases.category_id', '=', 'categories.id')
            ->join('subcategories', 'purchases.subcategory_id', '=', 'subcategories.id')
            ->select(
                'purchases.id',
                'purchases.quantity',
                'purchases.purchase_date',
                'purchases.amount',
                'purchases.transaction_number',
                'purchases.landed_cost',
                'purchases.description',
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
            ->orderBy('purchases.id', 'desc')
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
                $data = [
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
                ];
                $inventory = Inventory::create($data);
                Purchases::create($data);

                $this->activityLog->logInventoryAction(
                    ActivityLog::ACTION_CREATED,
                    "{$this->actor} created a new purchase: #{$inventory->transaction_number}",
                    ['new' => $inventory->toArray()]
                );
            });
            return redirect()->back()->with('success', 'Transaction successfully added!');
        } catch (\Exception $e) {
            report($e);
            return redirect()->back()->with('error', $e->getMessage() ?? 'Failed to create purchase');
        }
    }

    public function update(InventoryRequest $inventoryRequest, Inventory $inventory)
    {
        $validated = $inventoryRequest->validated();

        try {
            DB::transaction(function () use ($validated, $inventory) {
                $oldData = $inventory->toArray();

                $inventory->update($validated);
                Purchases::where('id', $inventory->id)->update($validated);


                $this->activityLog->logInventoryAction(
                    ActivityLog::ACTION_UPDATED,
                    "{$this->actor} updated a purchase: #{$inventory->transaction_number}",
                    ['old' => $oldData, 'new' => $inventory->toArray()]
                );
            });
            return redirect()->back()->with('success', 'Transaction successfully updated!');
        } catch (\Exception $e) {
            report($e);
            return redirect()->back()->with('error', 'Something went wrong');
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

            return redirect()->back()->with('success', 'Transaction successfully removed!');
        } catch (\Exception $e) {
            report($e);
            return redirect()->back()->with('error', 'Something went wrong');
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
            "{$this->actor} exported a purchase in report",
            ['old' => null, 'new' => null,]
        );

        return Excel::download($export, $fileName);
    }
}
