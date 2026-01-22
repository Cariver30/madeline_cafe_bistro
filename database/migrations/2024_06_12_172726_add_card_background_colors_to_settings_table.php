<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCardBackgroundColorsToSettingsTable extends Migration
{
    public function up()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->string('card_bg_color_menu')->nullable();
            $table->string('card_bg_color_cocktails')->nullable();
            $table->string('card_bg_color_wines')->nullable();
        });
    }

    public function down()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('card_bg_color_menu');
            $table->dropColumn('card_bg_color_cocktails');
            $table->dropColumn('card_bg_color_wines');
        });
    }
}

