<?php

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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->integer('quantity');
            $table->decimal('selling_price');
            $table->decimal('amount');
            $table->string('sales_date');
            $table->string('notes')->nullable();
            $table->boolean('is_paid')->default(false);
            $table->integer('transaction_number')->unique();
            $table->foreignId('due_date_id')->constrained();
            $table->foreignId('customer_id')->constrained();
            $table->foreignId('category_id')->constrained();
            $table->foreignId('subcategory_id')->constrained();
            $table->foreignId('transaction_id')->constrained();
            $table->foreignId('unit_measure_id')->constrained();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
