<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddImageToCocktailsTable extends Migration
{
    public function up()
    {
        Schema::table('cocktails', function (Blueprint $table) {
            // Verifica si la columna no existe antes de agregarla
            if (!Schema::hasColumn('cocktails', 'image')) {
                $table->string('image')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('cocktails', function (Blueprint $table) {
            // Verifica si la columna existe antes de eliminarla
            if (Schema::hasColumn('cocktails', 'image')) {
                $table->dropColumn('image');
            }
        });
    }
}
