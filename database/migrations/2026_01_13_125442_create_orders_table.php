<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('orders')) {
            Schema::create('orders', function (Blueprint $table) {
                $table->id();
                $table->foreignId('table_session_id')->constrained('table_sessions')->cascadeOnDelete();
                $table->foreignId('server_id')->constrained('users')->cascadeOnDelete();
                $table->enum('status', ['pending', 'confirmed', 'cancelled'])->default('pending');
                $table->timestamp('confirmed_at')->nullable();
                $table->timestamp('cancelled_at')->nullable();
                $table->timestamps();

                $table->index(['status', 'server_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
