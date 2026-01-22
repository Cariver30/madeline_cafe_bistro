<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('table_sessions') && !Schema::hasColumn('table_sessions', 'order_mode')) {
            Schema::table('table_sessions', function (Blueprint $table) {
                $table->string('order_mode')->default('table')->after('guest_phone');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('table_sessions') && Schema::hasColumn('table_sessions', 'order_mode')) {
            Schema::table('table_sessions', function (Blueprint $table) {
                $table->dropColumn('order_mode');
            });
        }
    }
};
