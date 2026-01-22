<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dish_recommendations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('dish_id');
            $table->unsignedBigInteger('recommended_dish_id');
            $table->timestamps();

            $table->foreign('dish_id')
                ->references('id')
                ->on('dishes')
                ->cascadeOnDelete();

            $table->foreign('recommended_dish_id')
                ->references('id')
                ->on('dishes')
                ->cascadeOnDelete();

            $table->unique(['dish_id', 'recommended_dish_id'], 'dish_recommendations_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dish_recommendations');
    }
};
