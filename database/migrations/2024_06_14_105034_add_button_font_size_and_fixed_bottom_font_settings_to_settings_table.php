<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddButtonFontSizeAndFixedBottomFontSettingsToSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('settings', function (Blueprint $table) {
            if (!Schema::hasColumn('settings', 'button_font_size_cover')) {
                $table->integer('button_font_size_cover')->nullable()->default(18);
            }
            if (!Schema::hasColumn('settings', 'fixed_bottom_font_size')) {
                $table->integer('fixed_bottom_font_size')->nullable()->default(14);
            }
            if (!Schema::hasColumn('settings', 'fixed_bottom_font_color')) {
                $table->string('fixed_bottom_font_color')->nullable()->default('#ffffff');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('button_font_size_cover');
            $table->dropColumn('fixed_bottom_font_size');
            $table->dropColumn('fixed_bottom_font_color');
        });
    }
}
