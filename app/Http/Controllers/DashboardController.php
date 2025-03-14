<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Inventory;
use App\Models\Purchase;
use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Support\Number;

class DashboardController extends Controller
{
    public function index()
    {
        $sale = Sale::get();
        $inventory = Inventory::where('quantity', '>', 0)->get();

        $inventory->map(function ($inventory) {
            $inventory->total_amount = $inventory->landed_cost * $inventory->quantity;
            return $inventory;
        });

        $total_sale = $sale->sum('total_amount');
        $total_purchase = Purchase::sum('amount');
        $total_inventory = $inventory->sum('total_amount');
        $activity_logs = ActivityLog::with('users')->latest()->get();
        $total_collectible = $sale->where('status_id', 2)->sum('total_amount');
        $total_gross_profit = bcsub(bcadd($total_sale, $total_inventory, 10), $total_purchase, 10);

        $monthlySales = $sale
            ->groupBy(function ($sale) {
                return Carbon::parse($sale->sale_date)->format('F Y');
            })
            ->map(function ($sales) {
                return $sales->sum('total_amount');
            })
            ->sortBy(function ($amount, $month) {
                return Carbon::parse('01 ' . $month)->timestamp;
            });

        $chartData = [
            'labels' => $monthlySales->keys()->toArray(),
            'datasets' => [
                [
                    'label' => 'Total Amount',
                    'backgroundColor' => '#191970',
                    'borderColor' => '#172554',
                    'data' => $monthlySales->values()->toArray(),
                ],
            ],
        ];

        return inertia('Dashboard/Index', [
            'total_sale' => Number::format($total_sale, 2),
            'total_purchase' => Number::format($total_purchase, 2),
            'total_inventory' => Number::format($total_inventory, 2),
            'total_collectible' => Number::format($total_collectible, 2),
            'total_gross_profit' => Number::format($total_gross_profit, 2),
            'activity_logs' => $activity_logs,
            'monthly_sales' => $monthlySales,
            'chartData' => $chartData
        ]);
    }
}
