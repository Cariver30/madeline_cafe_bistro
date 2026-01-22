<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddViewSettingsToSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('settings', function (Blueprint $table) {
            if (!Schema::hasColumn('settings', 'background_image_cover')) {
                $table->string('background_image_cover')->nullable();
            }
            if (!Schema::hasColumn('settings', 'background_image_menu')) {
                $table->string('background_image_menu')->nullable();
            }
            if (!Schema::hasColumn('settings', 'background_image_cocktails')) {
                $table->string('background_image_cocktails')->nullable();
            }
            if (!Schema::hasColumn('settings', 'background_image_wines')) {
                $table->string('background_image_wines')->nullable();
            }
            if (!Schema::hasColumn('settings', 'text_color_cover')) {
                $table->string('text_color_cover')->nullable();
            }
            if (!Schema::hasColumn('settings', 'text_color_menu')) {
                $table->string('text_color_menu')->nullable();
            }
            if (!Schema::hasColumn('settings', 'text_color_cocktails')) {
                $table->string('text_color_cocktails')->nullable();
            }
            if (!Schema::hasColumn('settings', 'text_color_wines')) {
                $table->string('text_color_wines')->nullable();
            }
            if (!Schema::hasColumn('settings', 'card_opacity_cover')) {
                $table->decimal('card_opacity_cover', 3, 2)->nullable();
            }
            if (!Schema::hasColumn('settings', 'card_opacity_menu')) {
                $table->decimal('card_opacity_menu', 3, 2)->nullable();
            }
            if (!Schema::hasColumn('settings', 'card_opacity_cocktails')) {
                $table->decimal('card_opacity_cocktails', 3, 2)->nullable();
            }
            if (!Schema::hasColumn('settings', 'card_opacity_wines')) {
                $table->decimal('card_opacity_wines', 3, 2)->nullable();
            }
            if (!Schema::hasColumn('settings', 'font_family_cover')) {
                $table->string('font_family_cover')->nullable();
            }
            if (!Schema::hasColumn('settings', 'font_family_menu')) {
                $table->string('font_family_menu')->nullable();
            }
            if (!Schema::hasColumn('settings', 'font_family_cocktails')) {
                $table->string('font_family_cocktails')->nullable();
            }
            if (!Schema::hasColumn('settings', 'font_family_wines')) {
                $table->string('font_family_wines')->nullable();
            }
            if (!Schema::hasColumn('settings', 'button_color_cover')) {
                $table->string('button_color_cover')->nullable();
            }
            if (!Schema::hasColumn('settings', 'button_color_menu')) {
                $table->string('button_color_menu')->nullable();
            }
            if (!Schema::hasColumn('settings', 'button_color_cocktails')) {
                $table->string('button_color_cocktails')->nullable();
            }
            if (!Schema::hasColumn('settings', 'button_color_wines')) {
                $table->string('button_color_wines')->nullable();
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
            $table->dropColumn('background_image_cover');
            $table->dropColumn('background_image_menu');
            $table->dropColumn('background_image_cocktails');
            $table->dropColumn('background_image_wines');
            $table->dropColumn('text_color_cover');
            $table->dropColumn('text_color_menu');
            $table->dropColumn('text_color_cocktails');
            $table->dropColumn('text_color_wines');
            $table->dropColumn('card_opacity_cover');
            $table->dropColumn('card_opacity_menu');
            $table->dropColumn('card_opacity_cocktails');
            $table->dropColumn('card_opacity_wines');
            $table->dropColumn('font_family_cover');
            $table->dropColumn('font_family_menu');
            $table->dropColumn('font_family_cocktails');
            $table->dropColumn('font_family_wines');
            $table->dropColumn('button_color_cover');
            $table->dropColumn('button_color_menu');
            $table->dropColumn('button_color_cocktails');
            $table->dropColumn('button_color_wines');
        });
    }
}
