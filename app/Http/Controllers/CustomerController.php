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
    public function store(CustomerRequest $customerRequest)
    {
        $validated = $customerRequest->validated();

        try {
            DB::transaction(function () use ($validated) {
                Customer::create($validated);

                $this->logs('Customer Created');
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
