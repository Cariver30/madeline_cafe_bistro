<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('order_item_extras')) {
            Schema::table('order_item_extras', function (Blueprint $table) {
                if (!Schema::hasColumn('order_item_extras', 'group_name')) {
                    $table->string('group_name')->nullable()->after('name');
                }
                if (!Schema::hasColumn('order_item_extras', 'kind')) {
                    $table->string('kind')->nullable()->after('group_name');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('order_item_extras')) {
            Schema::table('order_item_extras', function (Blueprint $table) {
                if (Schema::hasColumn('order_item_extras', 'group_name')) {
                    $table->dropColumn('group_name');
                }
                if (Schema::hasColumn('order_item_extras', 'kind')) {
                    $table->dropColumn('kind');
                }
            });
        }
    }
};
