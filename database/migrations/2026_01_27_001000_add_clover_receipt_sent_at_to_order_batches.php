<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_batches', function (Blueprint $table) {
            if (! Schema::hasColumn('order_batches', 'clover_receipt_sent_at')) {
                $table->timestamp('clover_receipt_sent_at')->nullable()->after('metered_closed_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('order_batches', function (Blueprint $table) {
            if (Schema::hasColumn('order_batches', 'clover_receipt_sent_at')) {
                $table->dropColumn('clover_receipt_sent_at');
            }
        });
    }
};
