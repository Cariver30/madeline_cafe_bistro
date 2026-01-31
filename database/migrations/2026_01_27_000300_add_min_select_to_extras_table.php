<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('extras') && ! Schema::hasColumn('extras', 'min_select')) {
            Schema::table('extras', function (Blueprint $table) {
                $table->unsignedInteger('min_select')->nullable()->after('max_select');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('extras') && Schema::hasColumn('extras', 'min_select')) {
            Schema::table('extras', function (Blueprint $table) {
                $table->dropColumn('min_select');
            });
        }
    }
};
