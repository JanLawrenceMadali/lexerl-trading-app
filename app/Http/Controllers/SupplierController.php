<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\SupplierRequest;
use Illuminate\Support\Facades\Auth;

class SupplierController extends Controller
{
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
            DB::transaction(function () use ($validated) {
                Supplier::create($validated);

                $this->logs('Supplier Created');
            });
        } catch (\Throwable $e) {
            report($e);
        }
    }

    public function update(SupplierRequest $supplierRequest, Supplier $supplier)
    {
        $validated = $supplierRequest->validated();

        try {
            DB::transaction(function () use ($supplier, $validated) {
                $supplier->update($validated);

                $this->logs('Supplier Updated');
            });
        } catch (\Throwable $e) {
            report($e);
        }
    }

    public function destroy(Supplier $supplier)
    {
        try {
            DB::transaction(function () use ($supplier) {
                $supplier->delete();

                $this->logs('Supplier Deleted');
            });
        } catch (\Throwable $e) {
            report($e);
        }
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
