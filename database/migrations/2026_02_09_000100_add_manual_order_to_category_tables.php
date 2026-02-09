<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $tables = [
            'categories',
            'cocktail_categories',
            'wine_categories',
            'cantina_categories',
        ];

        foreach ($tables as $tableName) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (! Schema::hasColumn($tableName, 'manual_order')) {
                    $table->boolean('manual_order')->default(false)->after('order');
                }
            });
        }
    }

    public function down(): void
    {
        $tables = [
            'categories',
            'cocktail_categories',
            'wine_categories',
            'cantina_categories',
        ];

        foreach ($tables as $tableName) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (Schema::hasColumn($tableName, 'manual_order')) {
                    $table->dropColumn('manual_order');
                }
            });
        }
    }
};
