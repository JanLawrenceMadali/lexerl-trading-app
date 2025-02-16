<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Inventory;
use App\Models\Sale;
use App\Models\Subcategory;
use App\Models\Unit;
use Carbon\Carbon;

class SaleService
{
    public function getInventories()
    {
        return Inventory::with('units', 'suppliers', 'transactions', 'categories', 'subcategories')
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($inventory) {
                return [
                    'id' => $inventory->id,
                    'quantity' => $inventory->quantity,
                    'purchase_date' => $inventory->purchase_date,
                    'amount' => $inventory->amount,
                    'transaction_number' => $inventory->transaction_number,
                    'landed_cost' => $inventory->landed_cost,
                    'description' => $inventory->description,
                    'unit_id' => $inventory->unit_id,
                    'abbreviation' => $inventory->units->abbreviation,
                    'category_id' => $inventory->categories->id,
                    'category_name' => $inventory->categories->name,
                    'subcategory_id' => $inventory->subcategories->id,
                    'subcategory_name' => $inventory->subcategories->name,
                    'supplier_id' => $inventory->suppliers->id,
                    'supplier_name' => $inventory->suppliers->name,
                    'supplier_email' => $inventory->suppliers->email,
                    'transaction_id' => $inventory->transaction_id,
                    'transaction_type' => $inventory->transactions->type
                ];
            });
    }

    public function getSales()
    {
        return Sale::with([
            'statuses',
            'customers',
            'transactions',
            'inventory_sale' => function ($query) {
                $query->select([
                    'inventory_sale.*'
                ]);
            },
            'inventory_sale.units:id,abbreviation',
            'inventory_sale.categories:id,name',
            'inventory_sale.subcategories:id,name'
        ])
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($sale) {
                return [
                    'id' => $sale->id,
                    'sale_date' => Carbon::parse($sale->sale_date)->format('M j, Y'),
                    'transaction_id' => $sale->transaction_id,
                    'transaction_type' => $sale->transactions?->type,
                    'transaction_number' => $sale->transaction_number,
                    'total_amount' => $sale->total_amount,
                    'description' => $sale->description,
                    'status_id' => $sale->status_id,
                    'status' => $sale->statuses?->name,
                    'customer_id' => $sale->customer_id,
                    'customer_name' => $sale->customers?->name,
                    'customer_email' => $sale->customers?->email,
                    'customer_address1' => $sale->customers?->address1,
                    'customer_address2' => $sale->customers?->address2,
                    'customer_contact_person' => $sale->customers?->contact_person,
                    'customer_contact_number' => $sale->customers?->contact_number,
                    'due_date_id' => $sale->due_date_id,
                    'inventory_sale' => collect($sale->inventory_sale)
                        ->groupBy(function ($item) {
                            return implode('-', [
                                $item->inventory_id,
                                $item->unit_id,
                                $item->category_id,
                                $item->subcategory_id,
                                $item->selling_price
                            ]);
                        })
                        ->map(function ($group) {
                            $first = $group->first();
                            return [
                                'inventory_id' => $first->inventory_id,
                                'sale_id' => $first->sale_id,
                                'unit_id' => $first->unit_id,
                                'abbreviation' => $first->units?->abbreviation,
                                'category_id' => $first->category_id,
                                'category_name' => $first->categories?->name,
                                'subcategory_id' => $first->subcategory_id,
                                'subcategory_name' => $first->subcategories?->name,
                                'amount' => $group->sum('amount'),
                                'quantity' => $group->sum('quantity'),
                                'selling_price' => $first->selling_price,
                            ];
                        })
                        ->values(),
                    'created_at' => $sale->created_at,
                ];
            });
    }

    public function prepareSaleData(array $data): array
    {
        if (isset($data['status_id']) && $data['status_id'] == 1) {
            $data['due_date_id'] = null;
        }

        $data['sale_date'] = Carbon::parse($data['sale_date'])->format('Y-m-d');

        return collect($data)->only([
            'sale_date',
            'status_id',
            'customer_id',
            'due_date_id',
            'description',
            'total_amount',
            'transaction_id',
            'transaction_number'
        ])->toArray();
    }
}
