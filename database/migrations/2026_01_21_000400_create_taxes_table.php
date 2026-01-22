<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('taxes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('rate', 5, 2);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('category_tax', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tax_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['category_id', 'tax_id']);
        });

        Schema::create('dish_tax', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dish_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tax_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['dish_id', 'tax_id']);
        });

        Schema::create('cocktail_category_tax', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cocktail_category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tax_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['cocktail_category_id', 'tax_id']);
        });

        Schema::create('cocktail_tax', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cocktail_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tax_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['cocktail_id', 'tax_id']);
        });

        Schema::create('wine_category_tax', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wine_category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tax_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['wine_category_id', 'tax_id']);
        });

        Schema::create('wine_tax', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wine_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tax_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['wine_id', 'tax_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wine_tax');
        Schema::dropIfExists('wine_category_tax');
        Schema::dropIfExists('cocktail_tax');
        Schema::dropIfExists('cocktail_category_tax');
        Schema::dropIfExists('dish_tax');
        Schema::dropIfExists('category_tax');
        Schema::dropIfExists('taxes');
    }
};
