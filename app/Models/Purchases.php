<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Purchases extends Model
{
    protected $fillable = [
        'amount',
        'unit_id',
        'quantity',
        'description',
        'supplier_id',
        'landed_cost',
        'category_id',
        'subcategory_id',
        'description',
        'purchase_date',
        'transaction_id',
        'transaction_number',
    ];

    public function transactions(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'transaction_id');
    }

    public function suppliers(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function categories(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function subcategories(): BelongsTo
    {
        return $this->belongsTo(Subcategory::class, 'subcategory_id');
    }

    public function units(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function products(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
