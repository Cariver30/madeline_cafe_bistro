<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStyleSettingsToSettingsTable extends Migration
{
    public function up()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->string('font_family')->nullable();
            $table->string('text_color')->nullable();
            $table->decimal('opacity', 3, 2)->nullable();
        });
    }

    public function down()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('font_family');
            $table->dropColumn('text_color');
            $table->dropColumn('opacity');
        });
    }
}
