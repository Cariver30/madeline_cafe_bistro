<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('order_item_prep_labels')) {
            Schema::create('order_item_prep_labels', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_item_id')->constrained('order_items')->cascadeOnDelete();
                $table->foreignId('prep_label_id')->constrained('prep_labels')->cascadeOnDelete();
                $table->enum('status', ['pending', 'preparing', 'ready', 'delivered', 'cancelled'])->default('pending');
                $table->timestamp('prepared_at')->nullable();
                $table->timestamp('ready_at')->nullable();
                $table->timestamp('delivered_at')->nullable();
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->unique(['order_item_id', 'prep_label_id'], 'order_item_prep_labels_unique');
                $table->index(['prep_label_id', 'status']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('order_item_prep_labels');
    }
};
