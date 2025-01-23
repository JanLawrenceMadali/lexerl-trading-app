<?php

namespace App\Services;

use Carbon\Carbon;

class SaleService
{
    public function prepareSaleData(array $data): array
    {
        if (isset($data['status_id']) && $data['status_id'] == 1) {
            $data['due_date_id'] = null;
        }

        $sale_date = Carbon::parse($data['sale_date'])->format('Y-m-d');
        $data['sale_date'] = $sale_date;

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
