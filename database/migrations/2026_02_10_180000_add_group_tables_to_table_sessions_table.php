<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('table_sessions') && ! Schema::hasColumn('table_sessions', 'group_name')) {
            Schema::table('table_sessions', function (Blueprint $table) {
                $table->string('group_name')->nullable()->after('table_label');
            });
        }

        if (! Schema::hasTable('table_session_tables')) {
            Schema::create('table_session_tables', function (Blueprint $table) {
                $table->id();
                $table->foreignId('table_session_id')->constrained('table_sessions')->cascadeOnDelete();
                $table->foreignId('dining_table_id')->constrained('dining_tables')->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['table_session_id', 'dining_table_id']);
                $table->index('dining_table_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('table_session_tables')) {
            Schema::dropIfExists('table_session_tables');
        }

        if (Schema::hasTable('table_sessions') && Schema::hasColumn('table_sessions', 'group_name')) {
            Schema::table('table_sessions', function (Blueprint $table) {
                $table->dropColumn('group_name');
            });
        }
    }
};
