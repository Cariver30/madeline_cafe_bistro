<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('extras', 'group_required')) {
            Schema::table('extras', function (Blueprint $table) {
                $table->boolean('group_required')->default(false)->after('group_name');
            });
        }

        if (!Schema::hasColumn('extras', 'max_select')) {
            Schema::table('extras', function (Blueprint $table) {
                $table->unsignedTinyInteger('max_select')->nullable()->after('group_required');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('extras', 'max_select')) {
            Schema::table('extras', function (Blueprint $table) {
                $table->dropColumn('max_select');
            });
        }

        if (Schema::hasColumn('extras', 'group_required')) {
            Schema::table('extras', function (Blueprint $table) {
                $table->dropColumn('group_required');
            });
        }
    }
};
