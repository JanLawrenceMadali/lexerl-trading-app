<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'amount',
        'quantity',
        'sale_date',
        'status_id',
        'description',
        'total_amount',
        'due_date_id',
        'supplier_id',
        'category_id',
        'customer_id',
        'selling_price',
        'subcategory_id',
        'transaction_id',
        'unit_measure_id',
        'transaction_number',
    ];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_sale')->withPivot('quantity', 'amount', 'selling_price', 'unit_id', 'product_id');
    }

    public function statuses(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'status_id');
    }

    public function customers(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function transactions(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'transaction_id');
    }

    public function dues(): BelongsTo
    {
        return $this->belongsTo(DueDate::class, 'due_date_id');
    }
}
