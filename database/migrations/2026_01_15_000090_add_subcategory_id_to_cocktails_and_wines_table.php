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
        Schema::table('cocktails', function (Blueprint $table) {
            $table->foreignId('subcategory_id')
                ->nullable()
                ->constrained('cocktail_subcategories')
                ->nullOnDelete()
                ->after('category_id');
        });

        Schema::table('wines', function (Blueprint $table) {
            $table->foreignId('subcategory_id')
                ->nullable()
                ->constrained('wine_subcategories')
                ->nullOnDelete()
                ->after('category_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cocktails', function (Blueprint $table) {
            $table->dropForeign(['subcategory_id']);
            $table->dropColumn('subcategory_id');
        });

        Schema::table('wines', function (Blueprint $table) {
            $table->dropForeign(['subcategory_id']);
            $table->dropColumn('subcategory_id');
        });
    }
};
