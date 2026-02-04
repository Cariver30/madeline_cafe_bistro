<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('table_sessions') || Schema::hasColumn('table_sessions', 'waiting_list_entry_id')) {
            return;
        }

        Schema::table('table_sessions', function (Blueprint $table) {
            $table->foreignId('waiting_list_entry_id')
                ->nullable()
                ->after('dining_table_id')
                ->constrained('waiting_list_entries')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('table_sessions') || !Schema::hasColumn('table_sessions', 'waiting_list_entry_id')) {
            return;
        }

        Schema::table('table_sessions', function (Blueprint $table) {
            $table->dropForeign(['waiting_list_entry_id']);
            $table->dropColumn('waiting_list_entry_id');
        });
    }
};
