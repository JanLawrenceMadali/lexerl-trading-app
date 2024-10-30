<?php

namespace App\Http\Controllers;

use App\Http\Requests\SupplierRequest;
use App\Models\Supplier;
use Illuminate\Http\Request;

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

        Supplier::create($validated);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'Created a supplier',
            'description' => 'A supplier was created by ' . auth()->user()->username,
        ]);
    }
}
