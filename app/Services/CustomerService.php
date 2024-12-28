<?php

namespace App\Services;

use App\Models\Customer;
use Illuminate\Support\Facades\DB;

class CustomerService
{
    public function createCustomer($data)
    {
        return DB::transaction(function () use ($data) {

            $customer = Customer::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'address1' => $data['address1'],
                'address2' => $data['address2'],
                'contact_person' => $data['contact_person'],
                'contact_number' => $data['contact_number']
            ]);

            return $customer;
        });
    }

    public function updateCustomer(Customer $customer, array $data)
    {
        return DB::transaction(function () use ($customer, $data) {
            $customer->update([
                'name' => $data['name'],
                'email' => $data['email'],
                'address1' => $data['address1'],
                'address2' => $data['address2'],
                'contact_person' => $data['contact_person'],
                'contact_number' => $data['contact_number']
            ]);

            return $customer;
        });
    }

    public function deleteCustomer(Customer $customer)
    {
        return DB::transaction(function () use ($customer) {

            $customer->delete();

            return true;
        });
    }
}
