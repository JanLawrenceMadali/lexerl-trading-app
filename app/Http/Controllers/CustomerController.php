<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\CustomerRequest;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CustomerController extends Controller
{
    public function index()
    {
        $customers = Customer::latest()->get();

        return inertia('Settings/Customers/Index', [
            'customers' => $customers
        ]);
    }

    public function store(CustomerRequest $customerRequest)
    {
        $validated = $customerRequest->validated();

        try {
            DB::transaction(function () use ($validated) {
                Customer::create($validated);

                $this->logs('Customer ' . $validated['name'] . ' was created');
            });

            return redirect()->route('customers')->with('success', 'Customer created successfully');
        } catch (\Throwable $e) {
            report($e);
            return redirect()->route('customers')->with('error', 'Something went wrong');
        }
    }

    public function update(CustomerRequest $customerRequest, Customer $customer)
    {
        $validated = $customerRequest->validated();

        try {
            DB::transaction(function () use ($validated, $customer) {
                $customer->update($validated);

                $this->logs('Customer ' . $validated['name'] . ' was updated');
            });

            return redirect()->route('customers')->with('success', 'Customer updated successfully');
        } catch (\Throwable $e) {
            report($e);
            return redirect()->route('customers')->with('error', 'Something went wrong');
        }
    }

    public function destroy(Customer $customer)
    {
        try {
            DB::transaction(function () use ($customer) {
                $customer->delete();

                $this->logs('Customer ' . $customer->name . ' was deleted');
            });

            return redirect()->route('customers')->with('success', 'Customer deleted successfully');
        } catch (\Throwable $e) {
            report($e);
            return redirect()->route('customers')->with('error', 'Something went wrong');
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
