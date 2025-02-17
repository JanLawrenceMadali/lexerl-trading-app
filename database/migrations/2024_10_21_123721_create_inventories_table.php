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
            $table->decimal('quantity', 10, 2)->default(0);
            $table->date('purchase_date');
            $table->decimal('amount', 10, 2)->default(0);
            $table->string('transaction_number');
            $table->decimal('landed_cost', 10, 2)->default(0);
            $table->string('description')->nullable();
            $table->foreignIdFor(Unit::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Supplier::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Category::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Subcategory::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Transaction::class)->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->decimal('quantity', 10, 2)->default(0);
            $table->date('purchase_date');
            $table->decimal('amount', 10, 2)->default(0);
            $table->string('transaction_number');
            $table->decimal('landed_cost', 10, 2)->default(0);
            $table->string('description')->nullable();
            $table->foreignIdFor(Unit::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Supplier::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Category::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Subcategory::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Transaction::class)->constrained()->cascadeOnDelete();
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
