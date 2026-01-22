<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('table_sessions', 'service_channel')) {
            Schema::table('table_sessions', function (Blueprint $table) {
                $table->string('service_channel')->default('table')->after('open_order_id');
                $table->index('service_channel');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('table_sessions', 'service_channel')) {
            Schema::table('table_sessions', function (Blueprint $table) {
                $table->dropIndex(['service_channel']);
                $table->dropColumn('service_channel');
            });
        }
    }
};
