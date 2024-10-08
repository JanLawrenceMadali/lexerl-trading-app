<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;
    protected $fillable = [
        'cost',
        'notes',
        'quantity',
        'category_id',
        'supplier_id',
        'subcategory_id',
        'transaction_id',
        'unit_measure_id',
        'transaction_number'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    
    public function subcategory()
    {
        return $this->belongsTo(Subcategory::class);
    }
    
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function unit_measure()
    {
        return $this->belongsTo(UnitMeasure::class);
    }
}
