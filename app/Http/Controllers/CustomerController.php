<?php

namespace App\Http\Controllers;

use App\Http\Requests\CustomerRequest;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function store(CustomerRequest $customerRequest)
    {
        $validated = $customerRequest->validated();

        Customer::create($validated);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'Created a customer',
            'description' => 'A customer was created by ' . auth()->user()->username,
        ]);
    }
}
