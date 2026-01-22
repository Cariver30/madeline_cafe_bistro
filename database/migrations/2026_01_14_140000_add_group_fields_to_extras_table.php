<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('extras')) {
            Schema::table('extras', function (Blueprint $table) {
                if (!Schema::hasColumn('extras', 'group_name')) {
                    $table->string('group_name')->nullable()->after('name');
                }
                if (!Schema::hasColumn('extras', 'kind')) {
                    $table->string('kind')->default('modifier')->after('group_name');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('extras')) {
            Schema::table('extras', function (Blueprint $table) {
                if (Schema::hasColumn('extras', 'group_name')) {
                    $table->dropColumn('group_name');
                }
                if (Schema::hasColumn('extras', 'kind')) {
                    $table->dropColumn('kind');
                }
            });
        }
    }
};
