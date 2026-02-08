<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('dishes') && ! Schema::hasColumn('dishes', 'manual_hidden')) {
            Schema::table('dishes', function (Blueprint $table) {
                $table->boolean('manual_hidden')->default(false)->after('visible');
            });
        }

        if (Schema::hasTable('cocktails') && ! Schema::hasColumn('cocktails', 'manual_hidden')) {
            Schema::table('cocktails', function (Blueprint $table) {
                $table->boolean('manual_hidden')->default(false)->after('visible');
            });
        }

        if (Schema::hasTable('wines') && ! Schema::hasColumn('wines', 'manual_hidden')) {
            Schema::table('wines', function (Blueprint $table) {
                $table->boolean('manual_hidden')->default(false)->after('visible');
            });
        }

        if (Schema::hasTable('cantina_items') && ! Schema::hasColumn('cantina_items', 'manual_hidden')) {
            Schema::table('cantina_items', function (Blueprint $table) {
                $table->boolean('manual_hidden')->default(false)->after('visible');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('dishes') && Schema::hasColumn('dishes', 'manual_hidden')) {
            Schema::table('dishes', function (Blueprint $table) {
                $table->dropColumn('manual_hidden');
            });
        }

        if (Schema::hasTable('cocktails') && Schema::hasColumn('cocktails', 'manual_hidden')) {
            Schema::table('cocktails', function (Blueprint $table) {
                $table->dropColumn('manual_hidden');
            });
        }

        if (Schema::hasTable('wines') && Schema::hasColumn('wines', 'manual_hidden')) {
            Schema::table('wines', function (Blueprint $table) {
                $table->dropColumn('manual_hidden');
            });
        }

        if (Schema::hasTable('cantina_items') && Schema::hasColumn('cantina_items', 'manual_hidden')) {
            Schema::table('cantina_items', function (Blueprint $table) {
                $table->dropColumn('manual_hidden');
            });
        }
    }
};
