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

                $this->logs('created', $validated['name']);
            });

            return redirect()->route('suppliers')->with('success', 'Supplier created successfully');
        } catch (\Throwable $e) {
            report($e);
            return redirect()->route('suppliers')->with('error', 'Something went wrong');
        }
    }

    public function update(SupplierRequest $supplierRequest, Supplier $supplier)
    {
        $validated = $supplierRequest->validated();

        try {
            DB::transaction(function () use ($supplier, $validated) {
                $supplier->update($validated);

                $this->logs('updated', $supplier->name);
            });

            return redirect()->route('suppliers')->with('success', 'Supplier updated successfully');
        } catch (\Throwable $e) {
            report($e);
            return redirect()->route('suppliers')->with('error', 'Something went wrong');
        }
    }

    public function destroy(Supplier $supplier)
    {
        try {
            DB::transaction(function () use ($supplier) {
                $supplier->delete();

                $this->logs('deleted', $supplier->name);
            });

            return redirect()->route('suppliers')->with('success', 'Supplier deleted successfully');
        } catch (\Throwable $e) {
            report($e);
            return redirect()->route('suppliers')->with('error', 'Something went wrong');
        }
    }

    private function logs(string $action, string $description)
    {
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'description' => Auth::user()->username . ' ' . $action . ' a supplier ' . $description
        ]);
    }
}
