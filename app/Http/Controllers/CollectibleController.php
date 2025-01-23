<?php

namespace App\Http\Controllers;

use App\Exports\CollectiblesExport;
use App\Models\ActivityLog;
use App\Models\Sale;
use App\Services\ActivityLoggerService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;

class CollectibleController extends Controller
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
        $sales = Sale::query()
            ->whereHas('products')
            ->with(['dues', 'statuses', 'customers', 'transactions'])
            ->where('status_id', 2)
            ->get()
            ->map(function ($sale) {
                $duesDays = (int) Str::beforeLast($sale->dues->days, ' days');
                $dueDate = Carbon::parse($sale->sale_date)
                    ->startOfDay()
                    ->addDays($duesDays);
                $daysLeft = now()->startOfDay()->diffInDays($dueDate, false);
                $sale_date = Carbon::parse($sale->sale_date)->format('M j, Y');
                return [
                    'id' => $sale->id,
                    'sale_date' => $sale_date,
                    'transaction_number' => $sale->transaction_number,
                    'total_amount' => $sale->total_amount,
                    'description' => $sale->description,
                    'payment_method' => $sale->statuses->name,
                    'customer_name' => $sale->customers->name,
                    'customer_email' => $sale->customers->email,
                    'transaction_type' => $sale->transactions->type,
                    'due_date' => $sale->dues->days,
                    'daysLeft' => $daysLeft,
                    'created_at' => $sale->created_at,
                ];
            })
            ->sortBy('daysLeft')
            ->values();

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
                foreach ($validatedData['selectedIds'] as $selectedId) {
                    $sale = Sale::find($selectedId['id']);
                    $sale->status_id = 1;
                    $sale->save();

                    $this->activityLog->logCollectibleAction(
                        ActivityLog::ACTION_PAID,
                        "{$this->actor} marked #{$sale->transaction_number} as paid",
                        ['new' => $sale->toArray()]
                    );
                }
            });
            return redirect()->back()->with('success', 'Collectible(s) marked as paid successfully!');
        } catch (\Exception $e) {
            report($e);
            return redirect()->back()->with('error', $e->getMessage() ?? 'Failed to mark as paid a collectible');
        }
    }

    public function export(Request $request)
    {
        sleep(1);

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $export = new CollectiblesExport($startDate, $endDate);

        $date = now()->format('Ymd');
        $fileName = "collectibles_report_{$date}.xlsx";

        $this->activityLog->logCollectibleExport(
            ActivityLog::ACTION_EXPORTED,
            "{$this->actor} exported collectibles report",
            ['old' => null, 'new' => null]
        );

        return Excel::download($export, $fileName);
    }
}
