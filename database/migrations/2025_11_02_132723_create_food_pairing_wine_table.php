<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('food_pairing_wine', function (Blueprint $table) {
        $table->id();
        $table->foreignId('wine_id')->constrained()->cascadeOnDelete();
        $table->foreignId('food_pairing_id')->constrained()->cascadeOnDelete();
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('food_pairing_wine');
    }
};
