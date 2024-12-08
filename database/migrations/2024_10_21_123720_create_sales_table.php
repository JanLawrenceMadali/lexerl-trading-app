<?php

use App\Models\Customer;
use App\Models\DueDate;
use App\Models\Status;
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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->date('sale_date');
            $table->string('transaction_number');
            $table->decimal('total_amount', 10, 2);
            $table->string('description')->nullable();
            $table->foreignIdFor(Status::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Customer::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Transaction::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(DueDate::class)->nullable()->constrained()->cascadeOnDelete();
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
