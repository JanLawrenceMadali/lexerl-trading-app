<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\Purchases;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class InventoryService
{
    public function getFormattedInventories()
    {
        return Purchases::with(['units', 'suppliers', 'categories', 'subcategories', 'transactions'])
            ->orderBy('purchases.id', 'desc')
            ->get()
            ->map(function ($inventory) {
                return $this->formatInventoryData($inventory);
            });
    }

    private function formatInventoryData($inventory)
    {
        return [
            'id' => $inventory->id,
            'quantity' => $inventory->quantity,
            'purchase_date' => Carbon::parse($inventory->purchase_date)->format('M j, Y'),
            'amount' => $inventory->amount,
            'transaction_number' => $inventory->transaction_number,
            'landed_cost' => $inventory->landed_cost,
            'description' => $inventory->description,
            'abbreviation' => $inventory->units->abbreviation,
            'unit_id' => $inventory->units->id,
            'supplier_id' => $inventory->suppliers->id,
            'supplier_name' => $inventory->suppliers->name,
            'supplier_email' => $inventory->suppliers->email,
            'supplier_address1' => $inventory->suppliers->address1,
            'supplier_address2' => $inventory->suppliers->address2,
            'supplier_contact_person' => $inventory->suppliers->contact_person,
            'supplier_contact_number' => $inventory->suppliers->contact_number,
            'category_id' => $inventory->categories->id,
            'category_name' => $inventory->categories->name,
            'subcategory_id' => $inventory->subcategories->id,
            'subcategory_name' => $inventory->subcategories->name,
            'transaction_type' => $inventory->transactions->type,
            'transaction_id' => $inventory->transactions->id,
        ];
    }

    public function prepareInventoryData(array $validated, bool $includeQuantity = true): array
    {
        $data = [
            'amount' => $validated['amount'],
            'purchase_date' => Carbon::parse($validated['purchase_date'])->format('Y-m-d'),
            'unit_id' => $validated['unit_id'],
            'description' => $validated['description'],
            'landed_cost' => $validated['landed_cost'],
            'supplier_id' => $validated['supplier_id'],
            'category_id' => $validated['category_id'],
            'subcategory_id' => $validated['subcategory_id'],
            'transaction_id' => $validated['transaction_id'],
            'transaction_number' => $validated['transaction_number'],
        ];

        if ($includeQuantity) {
            $data['quantity'] = $validated['quantity'];
        }

        return $data;
    }

    public function validateQuantityChange(Inventory $inventory)
    {
        if ($inventory->quantity <= 0) {
            throw ValidationException::withMessages([
                'quantity' => 'Product is out of stock.'
            ]);
        }
    }
}
