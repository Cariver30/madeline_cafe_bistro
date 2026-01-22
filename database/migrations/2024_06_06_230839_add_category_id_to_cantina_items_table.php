<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCategoryIdToCantinaItemsTable extends Migration
{
    public function up()
    {
        Schema::table('cantina_items', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id')->after('price');
            $table->foreign('category_id')->references('id')->on('cantina_categories')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('cantina_items', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');
        });
    }
}
