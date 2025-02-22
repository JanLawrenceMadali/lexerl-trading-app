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
        'sale_date',
        'status_id',
        'description',
        'due_date_id',
        'customer_id',
        'total_amount',
        'transaction_id',
        'transaction_number',
    ];

    public function inventory_sale(): BelongsToMany
    {
        return $this->belongsToMany(Inventory::class, 'inventory_sale')
            ->withPivot('quantity', 'amount', 'selling_price', 'unit_id', 'category_id', 'subcategory_id', 'purchase_id')
            ->withTimestamps();
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
