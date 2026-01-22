<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('table_sessions', 'open_order_id')) {
            Schema::table('table_sessions', function (Blueprint $table) {
                $table->foreignId('open_order_id')
                    ->nullable()
                    ->after('server_id')
                    ->constrained('orders')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('table_sessions', 'open_order_id')) {
            Schema::table('table_sessions', function (Blueprint $table) {
                $table->dropForeign(['open_order_id']);
                $table->dropColumn('open_order_id');
            });
        }
    }
};
