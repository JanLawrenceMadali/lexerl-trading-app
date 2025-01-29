<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Inventory;
use App\Models\Purchases;
use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Support\Number;

class DashboardController extends Controller
{
    public function index()
    {
        $sale = Sale::get();
        $total_sale = $sale->sum('total_amount');
        $total_purchase = Purchases::sum('amount');
        $total_inventory = Inventory::where('quantity', '>', 0)->sum('amount');
        $activity_logs = ActivityLog::with('users')->latest()->get();
        $total_collectible = $sale->where('status_id', 2)->sum('total_amount');
        $total_gross_profit = $total_sale + $total_inventory - $total_purchase;

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
            'total_sale' => Number::format($total_sale),
            'total_purchase' => Number::format($total_purchase),
            'total_inventory' => Number::format($total_inventory),
            'total_collectible' => Number::format($total_collectible),
            'total_gross_profit' => Number::format($total_gross_profit),
            'activity_logs' => $activity_logs,
            'monthly_sales' => $monthlySales,
            'chartData' => $chartData
        ]);
    }
}
