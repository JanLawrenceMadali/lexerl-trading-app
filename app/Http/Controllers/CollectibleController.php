<?php

namespace App\Http\Controllers;

use App\Exports\CollectiblesExport;
use App\Models\ActivityLog;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class CollectibleController extends Controller
{
    public function index()
    {
        $sales = DB::table('product_sale')
            ->join('sales', 'product_sale.sale_id', '=', 'sales.id')
            ->join('statuses', 'sales.status_id', '=', 'statuses.id')
            ->join('customers', 'sales.customer_id', '=', 'customers.id')
            ->join('transactions', 'sales.transaction_id', '=', 'transactions.id')
            ->join('due_dates', 'sales.due_date_id', '=', 'due_dates.id')
            ->select(
                'sales.id',
                'sales.sale_date',
                'sales.transaction_number',
                'sales.total_amount',
                'sales.description',
                'statuses.name as payment_method',
                'statuses.id as status_id',
                'customers.name as customer_name',
                'customers.email as customer_email',
                'transactions.type as transaction_type',
                'due_dates.days',
            )
            ->where('sales.status_id', 2)
            ->orderBy('sales.id', 'desc')
            ->get();

        // return $sales;
        return inertia('Transactions/Collectibles/Index', [
            'sales' => $sales
        ]);
    }

    public function update(Request $request)
    {
        $validatedData = $request->validate([
            'selectedIds' => 'required|array',
            'selectedIds.*.id' => 'required|integer|exists:sales,id',
        ]);

        try {
            DB::transaction(function () use ($validatedData) {
                Sale::whereIn('id', array_column($validatedData['selectedIds'], 'id'))
                    ->update(['status_id' => 1, 'due_date_id' => null]);

                $this->logs('Sales have been marked as paid');
            });
        } catch (\Exception $e) {
            report($e);
        }
    }

    public function export(Request $request)
    {
        sleep(1);

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $export = new CollectiblesExport($startDate, $endDate);

        $date = now()->format('Ymd');
        $fileName = "collectibles_{$date}.xlsx";

        $this->logs('Collectibles Exported');

        return Excel::download($export, $fileName);
    }

    private function logs(string $action)
    {
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'description' => $action . ' by ' . Auth::user()->username,
        ]);
    }
}
