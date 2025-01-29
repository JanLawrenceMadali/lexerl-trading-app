<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\ActivityLog;
use App\Http\Requests\CustomerRequest;
use App\Services\ActivityLoggerService;
use App\Services\CustomerService;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{
    protected $customerService;
    protected $activityLog;
    private $actor;

    public function __construct(
        CustomerService $customerService,
        ActivityLoggerService $activityLoggerService
    ) {
        $this->customerService = $customerService;
        $this->activityLog = $activityLoggerService;
        $this->actor = Auth::user()->username;
    }

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
            $customer = $this->customerService->createCustomer($validated);

            $this->activityLog->logCustomerAction(
                ActivityLog::ACTION_CREATED,
                "{$this->actor} created a new customer: {$customer->name}",
                ['new' => $customer->toArray()]
            );

            return redirect()->back()->with('success', 'Customer created successfully!');
        } catch (\Throwable $e) {
            report($e);
            return redirect()->back()->with('error', $e->getMessage() ?? 'Failed to create a customer');
        }
    }

    public function update(CustomerRequest $customerRequest, Customer $customer)
    {
        $validated = $customerRequest->validated();

        try {
            $oldData = $customer->toArray();

            $this->customerService->updateCustomer($customer, $validated);

            $this->activityLog->logCustomerAction(
                ActivityLog::ACTION_UPDATED,
                "{$this->actor} updated a customer: {$customer->name}",
                ['old' => $oldData, 'new' => $customer->toArray()]
            );

            return redirect()->back()->with('success', 'Customer updated successfully!');
        } catch (\Throwable $e) {
            report($e);
            return redirect()->back()->with('error', $e->getMessage() ?? 'Failed to update customer');
        }
    }

    public function destroy(Customer $customer)
    {
        try {
            $this->customerService->deleteCustomer($customer);

            $this->activityLog->logCustomerAction(
                ActivityLog::ACTION_DELETED,
                "{$this->actor} deleted a customer: {$customer->name}",
                ['old' => $customer->toArray()]
            );

            return redirect()->back()->with('success', 'Customer deleted successfully!');
        } catch (\Throwable $e) {
            report($e);
            return redirect()->back()->with('error', $e->getMessage() ?? 'Failed to delete customer');
        }
    }
}
