<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('order_items', 'order_batch_id')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->foreignId('order_batch_id')
                    ->nullable()
                    ->after('order_id')
                    ->constrained('order_batches')
                    ->nullOnDelete();
                $table->index('order_batch_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('order_items', 'order_batch_id')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->dropForeign(['order_batch_id']);
                $table->dropIndex(['order_batch_id']);
                $table->dropColumn('order_batch_id');
            });
        }
    }
};
