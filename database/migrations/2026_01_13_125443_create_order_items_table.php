<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('order_items')) {
            Schema::create('order_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
                $table->morphs('itemable');
                $table->string('name');
                $table->unsignedInteger('quantity')->default(1);
                $table->decimal('unit_price', 10, 2)->default(0);
                $table->text('notes')->nullable();
                $table->string('category_scope', 20)->nullable();
                $table->unsignedBigInteger('category_id')->nullable();
                $table->string('category_name')->nullable();
                $table->unsignedInteger('category_order')->default(0);
                $table->timestamps();

                $table->index(['category_scope', 'category_order']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
