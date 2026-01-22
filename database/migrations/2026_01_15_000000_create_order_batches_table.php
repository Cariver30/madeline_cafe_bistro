<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('order_batches')) {
            Schema::create('order_batches', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
                $table->enum('source', ['server', 'table', 'pos'])->default('server');
                $table->enum('status', ['pending', 'confirmed', 'cancelled'])->default('pending');
                $table->timestamp('confirmed_at')->nullable();
                $table->timestamp('cancelled_at')->nullable();
                $table->timestamps();

                $table->index(['status', 'order_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('order_batches');
    }
};
