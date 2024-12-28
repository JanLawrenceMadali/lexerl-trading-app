<?php

use App\Models\Role;
use App\Models\User;
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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Role::class)->constrained()->cascadeOnDelete();

            $table->string('action');
            $table->string('module');
            $table->text('description');

            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('route')->nullable();

            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();

            // Indexes
            $table->index(['user_id', 'created_at']);
            $table->index(['action', 'module']);
            $table->index('role_id');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
