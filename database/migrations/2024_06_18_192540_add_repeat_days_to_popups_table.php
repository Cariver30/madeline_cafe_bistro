<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRepeatDaysToPopupsTable extends Migration
{
    public function up()
    {
        Schema::table('popups', function (Blueprint $table) {
            $table->string('repeat_days')->nullable(); // Almacenar días de la semana como una cadena de texto
        });
    }

    public function down()
    {
        Schema::table('popups', function (Blueprint $table) {
            $table->dropColumn('repeat_days');
        });
    }
}
