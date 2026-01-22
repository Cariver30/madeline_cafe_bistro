<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->string('cover_gallery_image_1')->nullable()->after('button_label_reservations');
            $table->string('cover_gallery_image_2')->nullable()->after('cover_gallery_image_1');
            $table->string('cover_gallery_image_3')->nullable()->after('cover_gallery_image_2');
            $table->string('menu_hero_image')->nullable()->after('cover_gallery_image_3');
            $table->string('cocktail_hero_image')->nullable()->after('menu_hero_image');
            $table->string('coffee_hero_image')->nullable()->after('cocktail_hero_image');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'cover_gallery_image_1',
                'cover_gallery_image_2',
                'cover_gallery_image_3',
                'menu_hero_image',
                'cocktail_hero_image',
                'coffee_hero_image',
            ]);
        });
    }
};
