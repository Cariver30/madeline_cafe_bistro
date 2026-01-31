<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            if (! Schema::hasColumn('categories', 'clover_id')) {
                $table->string('clover_id')->nullable()->index();
            }
        });

        Schema::table('cocktail_categories', function (Blueprint $table) {
            if (! Schema::hasColumn('cocktail_categories', 'clover_id')) {
                $table->string('clover_id')->nullable()->index();
            }
        });

        Schema::table('wine_categories', function (Blueprint $table) {
            if (! Schema::hasColumn('wine_categories', 'clover_id')) {
                $table->string('clover_id')->nullable()->index();
            }
        });

        Schema::table('cantina_categories', function (Blueprint $table) {
            if (! Schema::hasColumn('cantina_categories', 'clover_id')) {
                $table->string('clover_id')->nullable()->index();
            }
        });

        Schema::table('dishes', function (Blueprint $table) {
            if (! Schema::hasColumn('dishes', 'clover_id')) {
                $table->string('clover_id')->nullable()->index();
            }
        });

        Schema::table('cocktails', function (Blueprint $table) {
            if (! Schema::hasColumn('cocktails', 'clover_id')) {
                $table->string('clover_id')->nullable()->index();
            }
        });

        Schema::table('wines', function (Blueprint $table) {
            if (! Schema::hasColumn('wines', 'clover_id')) {
                $table->string('clover_id')->nullable()->index();
            }
        });

        Schema::table('cantina_items', function (Blueprint $table) {
            if (! Schema::hasColumn('cantina_items', 'clover_id')) {
                $table->string('clover_id')->nullable()->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            if (Schema::hasColumn('categories', 'clover_id')) {
                $table->dropColumn('clover_id');
            }
        });

        Schema::table('cocktail_categories', function (Blueprint $table) {
            if (Schema::hasColumn('cocktail_categories', 'clover_id')) {
                $table->dropColumn('clover_id');
            }
        });

        Schema::table('wine_categories', function (Blueprint $table) {
            if (Schema::hasColumn('wine_categories', 'clover_id')) {
                $table->dropColumn('clover_id');
            }
        });

        Schema::table('cantina_categories', function (Blueprint $table) {
            if (Schema::hasColumn('cantina_categories', 'clover_id')) {
                $table->dropColumn('clover_id');
            }
        });

        Schema::table('dishes', function (Blueprint $table) {
            if (Schema::hasColumn('dishes', 'clover_id')) {
                $table->dropColumn('clover_id');
            }
        });

        Schema::table('cocktails', function (Blueprint $table) {
            if (Schema::hasColumn('cocktails', 'clover_id')) {
                $table->dropColumn('clover_id');
            }
        });

        Schema::table('wines', function (Blueprint $table) {
            if (Schema::hasColumn('wines', 'clover_id')) {
                $table->dropColumn('clover_id');
            }
        });

        Schema::table('cantina_items', function (Blueprint $table) {
            if (Schema::hasColumn('cantina_items', 'clover_id')) {
                $table->dropColumn('clover_id');
            }
        });
    }
};
