<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('order_batches')) {
            Schema::table('order_batches', function (Blueprint $table) {
                if (! Schema::hasColumn('order_batches', 'clover_order_id')) {
                    $table->string('clover_order_id')->nullable()->after('cancelled_at');
                }
                if (! Schema::hasColumn('order_batches', 'clover_print_event_id')) {
                    $table->string('clover_print_event_id')->nullable()->after('clover_order_id');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('order_batches')) {
            Schema::table('order_batches', function (Blueprint $table) {
                if (Schema::hasColumn('order_batches', 'clover_print_event_id')) {
                    $table->dropColumn('clover_print_event_id');
                }
                if (Schema::hasColumn('order_batches', 'clover_order_id')) {
                    $table->dropColumn('clover_order_id');
                }
            });
        }
    }
};
