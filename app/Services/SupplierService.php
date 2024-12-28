<?php

namespace App\Services;

use App\Models\Supplier;
use Illuminate\Support\Facades\DB;

class SupplierService
{
    public function createSupplier(array $data)
    {
        return DB::transaction(function () use ($data) {

            $supplier = Supplier::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'address1' => $data['address1'],
                'address2' => $data['address2'],
                'contact_person' => $data['contact_person'],
                'contact_number' => $data['contact_number'],
            ]);

            return $supplier;
        });
    }

    public function updateSupplier(Supplier $supplier, array $data)
    {
        return DB::transaction(function () use ($supplier, $data) {

            $supplier->update([
                'name' => $data['name'],
                'email' => $data['email'],
                'address1' => $data['address1'],
                'address2' => $data['address2'],
                'contact_person' => $data['contact_person'],
                'contact_number' => $data['contact_number'],
            ]);

            return $supplier;
        });
    }

    public function deleteSupplier(Supplier $supplier)
    {
        return DB::transaction(function () use ($supplier) {

            $supplier->delete();

            return true;
        });
    }
}
