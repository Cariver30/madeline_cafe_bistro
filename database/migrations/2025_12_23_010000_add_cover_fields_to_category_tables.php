<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->boolean('show_on_cover')->default(false)->after('order');
            $table->string('cover_title')->nullable()->after('show_on_cover');
            $table->string('cover_subtitle')->nullable()->after('cover_title');
        });

        Schema::table('cocktail_categories', function (Blueprint $table) {
            $table->boolean('show_on_cover')->default(false)->after('order');
            $table->string('cover_title')->nullable()->after('show_on_cover');
            $table->string('cover_subtitle')->nullable()->after('cover_title');
        });

        Schema::table('wine_categories', function (Blueprint $table) {
            $table->boolean('show_on_cover')->default(false)->after('order');
            $table->string('cover_title')->nullable()->after('show_on_cover');
            $table->string('cover_subtitle')->nullable()->after('cover_title');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn(['show_on_cover', 'cover_title', 'cover_subtitle']);
        });

        Schema::table('cocktail_categories', function (Blueprint $table) {
            $table->dropColumn(['show_on_cover', 'cover_title', 'cover_subtitle']);
        });

        Schema::table('wine_categories', function (Blueprint $table) {
            $table->dropColumn(['show_on_cover', 'cover_title', 'cover_subtitle']);
        });
    }
};
