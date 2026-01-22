<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWinesTable extends Migration
{
    public function up()
    {
        Schema::create('wines', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 8, 2);
            $table->unsignedBigInteger('category_id');
            $table->string('image')->nullable();
            $table->timestamps();

            $table->foreign('category_id')->references('id')->on('wine_categories')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('wines');
    }
}
