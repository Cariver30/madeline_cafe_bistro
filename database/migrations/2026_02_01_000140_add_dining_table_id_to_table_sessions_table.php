<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('table_sessions') || Schema::hasColumn('table_sessions', 'dining_table_id')) {
            return;
        }

        Schema::table('table_sessions', function (Blueprint $table) {
            $table->foreignId('dining_table_id')
                ->nullable()
                ->after('server_id')
                ->constrained('dining_tables')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('table_sessions') || !Schema::hasColumn('table_sessions', 'dining_table_id')) {
            return;
        }

        Schema::table('table_sessions', function (Blueprint $table) {
            $table->dropForeign(['dining_table_id']);
            $table->dropColumn('dining_table_id');
        });
    }
};
