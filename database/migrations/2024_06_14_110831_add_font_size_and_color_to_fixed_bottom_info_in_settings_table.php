<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFontSizeAndColorToFixedBottomInfoInSettingsTable extends Migration
{
    public function up()
    {
        Schema::table('settings', function (Blueprint $table) {
            if (!Schema::hasColumn('settings', 'fixed_bottom_font_size')) {
                $table->integer('fixed_bottom_font_size')->default(14);
            }
            if (!Schema::hasColumn('settings', 'fixed_bottom_font_color')) {
                $table->string('fixed_bottom_font_color')->default('#000000');
            }
        });
    }

    public function down()
    {
        Schema::table('settings', function (Blueprint $table) {
            foreach (['fixed_bottom_font_size', 'fixed_bottom_font_color'] as $column) {
                if (Schema::hasColumn('settings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
}
