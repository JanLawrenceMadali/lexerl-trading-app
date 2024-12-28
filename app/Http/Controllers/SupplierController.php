<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\SupplierRequest;
use App\Services\ActivityLoggerService;
use App\Services\SupplierService;
use Illuminate\Support\Facades\Auth;

class SupplierController extends Controller
{
    protected $supplierService;
    protected $activityLog;

    public function __construct(
        SupplierService $supplierService,
        ActivityLoggerService $activityLoggerService
    ) {
        $this->supplierService = $supplierService;
        $this->activityLog = $activityLoggerService;
    }

    public function index()
    {
        $suppliers = Supplier::latest()->get();

        return inertia('Settings/Suppliers/Index', [
            'suppliers' => $suppliers
        ]);
    }

    public function store(SupplierRequest $supplierRequest)
    {
        $validated = $supplierRequest->validated();

        try {
            $supplier = $this->supplierService->createSupplier($validated);

            $this->activityLog->logSupplierAction(
                $supplier,
                ActivityLog::ACTION_CREATED,
                ['new' => $supplier->toArray()]
            );

            return redirect()->back()->with('success', 'Supplier created successfully');
        } catch (\Throwable $e) {
            report($e);
            return redirect()->back()->with('error', $e->getMessage() ?? 'Failed to create supplier');
        }
    }

    public function update(SupplierRequest $supplierRequest, Supplier $supplier)
    {
        $validated = $supplierRequest->validated();

        try {
            $oldData = $supplier->toArray();

            $this->supplierService->updateSupplier($supplier, $validated);

            $this->activityLog->logSupplierAction(
                $supplier,
                ActivityLog::ACTION_UPDATED,
                ['old' => $oldData, 'new' => $supplier->toArray()]
            );

            return redirect()->back()->with('success', 'Supplier updated successfully');
        } catch (\Throwable $e) {
            report($e);
            return redirect()->back()->with('error', $e->getMessage() ?? 'Failed to update supplier');
        }
    }

    public function destroy(Supplier $supplier)
    {
        try {
            $this->supplierService->deleteSupplier($supplier);

            $this->activityLog->logSupplierAction(
                $supplier,
                ActivityLog::ACTION_DELETED,
                ['old' => $supplier->toArray()]
            );

            return redirect()->back()->with('success', 'Supplier deleted successfully');
        } catch (\Throwable $e) {
            report($e);
            return redirect()->back()->with('error', 'Something went wrong');
        }
    }
}
