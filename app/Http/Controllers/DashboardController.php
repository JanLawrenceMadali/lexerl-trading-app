<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Inventory;
use App\Models\Sale;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $sale = Sale::get();
        $total_sale = $sale->sum('total_amount');
        $total_purchase = Inventory::sum('amount');
        $activity_logs = ActivityLog::with('users')->latest()->get();
        $total_collectible = $sale->where('status_id', 2)->sum('total_amount');

        $monthlySales = $sale
            ->groupBy(function ($sale) {
                return Carbon::parse($sale->sale_date)->format('F');
            })
            ->map(function ($sales) {
                return $sales->sum('total_amount');
            });

        $chartData = [
            'labels' => $monthlySales->keys()->toArray(),
            'datasets' => [
                [
                    'label' => 'Total Amount',
                    'backgroundColor' => '#42A5F5',
                    'borderColor' => '#1E88E5',
                    'data' => $monthlySales->values()->toArray(),
                ],
            ],
        ];

        return inertia('Dashboard', [
            'total_sale' => intval($total_sale),
            'total_purchase' => intval($total_purchase),
            'total_collectible' => intval($total_collectible),
            'activity_logs' => $activity_logs,
            'monthly_sales' => $monthlySales,
            'chartData' => $chartData
        ]);
    }
}
