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
        Schema::create('cocktail_subcategories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cocktail_category_id')->constrained('cocktail_categories')->onDelete('cascade');
            $table->string('name');
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();
        });

        Schema::create('wine_subcategories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wine_category_id')->constrained('wine_categories')->onDelete('cascade');
            $table->string('name');
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wine_subcategories');
        Schema::dropIfExists('cocktail_subcategories');
    }
};
