<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'notes',
        'amount',
        'is_paid',
        'quantity',
        'sales_date',
        'selling_price',
        'customer_id',
        'due_date_id',
        'category_id',
        'subcategory_id',
        'transaction_id',
        'unit_measure_id',
        'transaction_number',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function subcategory()
    {
        return $this->belongsTo(Subcategory::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function unit_measure()
    {
        return $this->belongsTo(UnitMeasure::class);
    }

    public function due_date()
    {
        return $this->belongsTo(DueDate::class);
    }
}
