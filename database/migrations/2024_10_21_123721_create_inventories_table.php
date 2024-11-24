<?php

use App\Models\Category;
use App\Models\Unit;
use App\Models\Product;
use App\Models\Subcategory;
use App\Models\Supplier;
use App\Models\Transaction;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->integer('quantity');
            $table->date('purchase_date');
            $table->decimal('amount', 10, 2);
            $table->string('transaction_number');
            $table->decimal('landed_cost', 10, 2);
            $table->string('description')->nullable();
            $table->foreignIdFor(Unit::class)->constrained();
            $table->foreignIdFor(Supplier::class)->constrained();
            $table->foreignIdFor(Category::class)->constrained();
            $table->foreignIdFor(Subcategory::class)->constrained();
            $table->foreignIdFor(Transaction::class)->constrained();
            $table->timestamps();
        });

        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->integer('quantity');
            $table->date('purchase_date');
            $table->decimal('amount', 10, 2);
            $table->string('transaction_number');
            $table->decimal('landed_cost', 10, 2);
            $table->string('description')->nullable();
            $table->foreignIdFor(Unit::class)->constrained();
            $table->foreignIdFor(Supplier::class)->constrained();
            $table->foreignIdFor(Category::class)->constrained();
            $table->foreignIdFor(Subcategory::class)->constrained();
            $table->foreignIdFor(Transaction::class)->constrained();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventories');
        Schema::dropIfExists('purchase-in');
    }
};
