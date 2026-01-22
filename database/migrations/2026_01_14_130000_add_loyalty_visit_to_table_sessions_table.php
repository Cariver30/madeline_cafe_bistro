<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('table_sessions') && !Schema::hasColumn('table_sessions', 'loyalty_visit_id')) {
            Schema::table('table_sessions', function (Blueprint $table) {
                $table->foreignId('loyalty_visit_id')
                    ->nullable()
                    ->constrained('loyalty_visits')
                    ->nullOnDelete()
                    ->after('guest_phone');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('table_sessions') && Schema::hasColumn('table_sessions', 'loyalty_visit_id')) {
            Schema::table('table_sessions', function (Blueprint $table) {
                $table->dropConstrainedForeignId('loyalty_visit_id');
            });
        }
    }
};
