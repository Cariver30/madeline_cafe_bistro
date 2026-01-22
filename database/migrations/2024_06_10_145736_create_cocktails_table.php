<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCocktailsTable extends Migration
{
    public function up()
    {
        Schema::create('cocktails', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 8, 2);
            $table->unsignedBigInteger('category_id');
            $table->foreign('category_id')->references('id')->on('cocktail_categories')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('cocktails');
    }
}
