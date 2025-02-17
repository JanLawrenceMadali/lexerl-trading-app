<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Exports\ActivityLogReport;
use App\Exports\CurrentInventoryReport;
use App\Services\ActivityLoggerService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Number;
use Maatwebsite\Excel\Facades\Excel;

use function Symfony\Component\Clock\now;

class ReportController extends Controller
{
    protected $activityLogs;
    private $actor;

    public function __construct(ActivityLoggerService $activityLoggerService)
    {
        $this->activityLogs = $activityLoggerService;
        $this->actor = Auth::user()->username;
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
                    'quantity' => Number::format($item->total_quantity, 2) . ' left',
                ];
            });

        return inertia('Reports/Inventory/Index', [
            'inventories' => $inventories
        ]);
    }

    public function activity_logs_export()
    {
        sleep(1);
        $date = now()->format('Ymd');
        $fileName = "activity_logs_{$date}.xlsx";

        $this->activityLogs->logActivityLogsExport(
            ActivityLog::ACTION_EXPORTED,
            "{$this->actor} exported activity logs report",
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
            ActivityLog::ACTION_EXPORTED,
            "{$this->actor} exported current inventory report",
            ['old' => null, 'new' => null,]
        );

        return Excel::download(new CurrentInventoryReport, $fileName);
    }
}
