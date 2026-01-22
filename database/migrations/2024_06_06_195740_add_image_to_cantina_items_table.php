<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddImageToCantinaItemsTable extends Migration
{
    public function up()
    {
        Schema::table('cantina_items', function (Blueprint $table) {
            $table->string('image')->nullable()->after('price');
        });
    }

    public function down()
    {
        Schema::table('cantina_items', function (Blueprint $table) {
            $table->dropColumn('image');
        });
    }
}

