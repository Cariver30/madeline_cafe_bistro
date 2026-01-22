<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddViewSettingsColumnsToSettingsTable extends Migration
{
    public function up()
    {
        Schema::table('settings', function (Blueprint $table) {
            if (!Schema::hasColumn('settings', 'text_color_cover')) {
                $table->string('text_color_cover')->nullable();
            }
            if (!Schema::hasColumn('settings', 'text_color_menu')) {
                $table->string('text_color_menu')->nullable();
            }
            if (!Schema::hasColumn('settings', 'card_opacity_cover')) {
                $table->decimal('card_opacity_cover', 3, 2)->nullable();
            }
            if (!Schema::hasColumn('settings', 'card_opacity_menu')) {
                $table->decimal('card_opacity_menu', 3, 2)->nullable();
            }
            if (!Schema::hasColumn('settings', 'font_family_cover')) {
                $table->string('font_family_cover')->nullable();
            }
            if (!Schema::hasColumn('settings', 'font_family_menu')) {
                $table->string('font_family_menu')->nullable();
            }
            if (!Schema::hasColumn('settings', 'button_color_cover')) {
                $table->string('button_color_cover')->nullable();
            }
            if (!Schema::hasColumn('settings', 'button_color_menu')) {
                $table->string('button_color_menu')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('settings', function (Blueprint $table) {
            foreach ([
                'text_color_cover',
                'text_color_menu',
                'card_opacity_cover',
                'card_opacity_menu',
                'font_family_cover',
                'font_family_menu',
                'button_color_cover',
                'button_color_menu',
            ] as $column) {
                if (Schema::hasColumn('settings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
}
