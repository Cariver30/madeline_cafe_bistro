<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('table_sessions', function (Blueprint $table) {
            if (!Schema::hasColumn('table_sessions', 'waiting_list_entry_id')) {
                $table->foreignId('waiting_list_entry_id')
                    ->nullable()
                    ->constrained('waiting_list_entries')
                    ->nullOnDelete()
                    ->after('dining_table_id');
            }

            if (!Schema::hasColumn('table_sessions', 'seated_at')) {
                $table->timestamp('seated_at')->nullable()->after('status');
            }

            if (!Schema::hasColumn('table_sessions', 'first_order_at')) {
                $table->timestamp('first_order_at')->nullable()->after('seated_at');
            }

            if (!Schema::hasColumn('table_sessions', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('first_order_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('table_sessions', function (Blueprint $table) {
            if (Schema::hasColumn('table_sessions', 'paid_at')) {
                $table->dropColumn('paid_at');
            }
            if (Schema::hasColumn('table_sessions', 'first_order_at')) {
                $table->dropColumn('first_order_at');
            }
            if (Schema::hasColumn('table_sessions', 'seated_at')) {
                $table->dropColumn('seated_at');
            }
            if (Schema::hasColumn('table_sessions', 'waiting_list_entry_id')) {
                $table->dropConstrainedForeignId('waiting_list_entry_id');
            }
        });
    }
};
