<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCategoryStylesToSettingsTableNew extends Migration
{
    public function up()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->string('category_name_bg_color_menu')->nullable();
            $table->string('category_name_text_color_menu')->nullable();
            $table->integer('category_name_font_size_menu')->nullable();
            $table->string('category_name_bg_color_cocktails')->nullable();
            $table->string('category_name_text_color_cocktails')->nullable();
            $table->integer('category_name_font_size_cocktails')->nullable();
            $table->string('category_name_bg_color_wines')->nullable();
            $table->string('category_name_text_color_wines')->nullable();
            $table->integer('category_name_font_size_wines')->nullable();
        });
    }

    public function down()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'category_name_bg_color_menu',
                'category_name_text_color_menu',
                'category_name_font_size_menu',
                'category_name_bg_color_cocktails',
                'category_name_text_color_cocktails',
                'category_name_font_size_cocktails',
                'category_name_bg_color_wines',
                'category_name_text_color_wines',
                'category_name_font_size_wines',
            ]);
        });
    }
}
