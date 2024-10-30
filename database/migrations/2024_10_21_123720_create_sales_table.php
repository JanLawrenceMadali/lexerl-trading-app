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
            $table->date('sale_date');
            $table->string('transaction_number');
            $table->decimal('total_amount', 10, 2);
            $table->string('description')->nullable();
            $table->foreignId('status_id')->constrained();
            $table->foreignId('customer_id')->constrained();
            $table->foreignId('transaction_id')->constrained();
            $table->foreignId('due_date_id')->nullable()->constrained();
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
