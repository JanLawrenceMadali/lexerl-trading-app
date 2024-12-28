<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Subcategory;
use App\Models\Unit;
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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Category::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Subcategory::class)->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('product_sale', function (Blueprint $table) {
            $table->id();
            $table->decimal('quantity', 10, 2)->default(0);
            $table->decimal('amount', 10, 2)->default(0);
            $table->decimal('selling_price', 10, 2)->default(0);
            $table->foreignIdFor(Unit::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Product::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Sale::class)->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
        Schema::dropIfExists('product_sale');
    }
};
