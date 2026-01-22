<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cocktail_dish', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cocktail_id')->constrained()->onDelete('cascade');
            $table->foreignId('dish_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cocktail_dish');
    }
};
