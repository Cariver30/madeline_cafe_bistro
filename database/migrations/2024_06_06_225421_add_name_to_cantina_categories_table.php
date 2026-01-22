<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNameToCantinaCategoriesTable extends Migration
{
    public function up()
    {
        Schema::table('cantina_categories', function (Blueprint $table) {
            // Comenta o elimina la siguiente línea
            // $table->string('name')->after('id');
        });
    }

    public function down()
    {
        Schema::table('cantina_categories', function (Blueprint $table) {
            $table->dropColumn('name');
        });
    }
}
