<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Exports\ActivityLogReport;
use App\Exports\CurrentInventoryReport;
use App\Services\ActivityLoggerService;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

use function Symfony\Component\Clock\now;

class ReportController extends Controller
{
    protected $activityLogs;

    public function __construct(ActivityLoggerService $activityLoggerService)
    {
        $this->activityLogs = $activityLoggerService;
    }

    public function activity_logs()
    {
        $activity_logs = ActivityLog::with('users')->latest()->get();
        return inertia('Reports/ActivityLogs/Index', [
            'activity_logs' => $activity_logs
        ]);
    }

    public function current_inventory()
    {
        $inventories = DB::table('inventories')
            ->join('units', 'inventories.unit_id', '=', 'units.id')
            ->join('categories', 'inventories.category_id', '=', 'categories.id')
            ->join('subcategories', 'inventories.subcategory_id', '=', 'subcategories.id')
            ->select(
                'units.abbreviation as unit',
                'categories.name as category_name',
                'subcategories.name as subcategory_name',
                DB::raw('SUM(inventories.quantity) as total_quantity'),
                DB::raw('MAX(inventories.created_at) as latest_created_at')
            )
            ->groupBy('units.abbreviation', 'categories.name', 'subcategories.name')
            ->having('total_quantity', '>', 0)
            ->orderBy('latest_created_at', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'category' => $item->category_name,
                    'subcategory' => $item->subcategory_name,
                    'unit' => $item->unit,
                    'quantity' => $item->total_quantity . ' left',
                ];
            });

        return inertia('Reports/Inventory/Index', [
            'inventories' => $inventories
        ]);
    }

    public function sale_logs()
    {
        $sales = DB::table('product_sale')
            ->join('products', 'product_sale.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->join('subcategories', 'products.subcategory_id', '=', 'subcategories.id')
            ->join('units', 'product_sale.unit_id', '=', 'units.id')
            ->select(
                'categories.name as category_name',
                'subcategories.name as subcategory_name',
                'product_sale.quantity',
                'units.abbreviation as unit'
            )
            ->latest('product_sale.created_at')
            ->get();

        return inertia('Reports/Sales/Index', [
            'sales' => $sales
        ]);
    }

    public function activity_logs_export()
    {
        sleep(1);
        $date = now()->format('Ymd');
        $fileName = "activity_logs_{$date}.xlsx";

        $this->activityLogs->logActivityLogsExport(
            $fileName,
            ActivityLog::MODULE_ACTIVITY_LOGS,
            ['old' => null, 'new' => null,]
        );

        return Excel::download(new ActivityLogReport, $fileName);
    }

    public function current_inventory_export()
    {
        sleep(1);
        $date = now()->format('Ymd');
        $fileName = "current_inventory_report_{$date}.xlsx";

        $this->activityLogs->logCurrentInventoryExport(
            $fileName,
            ActivityLog::MODULE_CURRENT_INVENTORY,
            ['old' => null, 'new' => null,]
        );

        return Excel::download(new CurrentInventoryReport, $fileName);
    }
}
