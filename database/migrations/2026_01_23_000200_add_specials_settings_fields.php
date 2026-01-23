<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (! Schema::hasColumn('settings', 'background_image_specials')) {
                $table->text('background_image_specials')->nullable()->after('background_image_cantina');
            }
            if (! Schema::hasColumn('settings', 'text_color_specials')) {
                $table->text('text_color_specials')->nullable()->after('text_color_cantina');
            }
            if (! Schema::hasColumn('settings', 'card_opacity_specials')) {
                $table->decimal('card_opacity_specials', 3, 2)->nullable()->after('card_opacity_cantina');
            }
            if (! Schema::hasColumn('settings', 'font_family_specials')) {
                $table->text('font_family_specials')->nullable()->after('font_family_cantina');
            }
            if (! Schema::hasColumn('settings', 'button_color_specials')) {
                $table->text('button_color_specials')->nullable()->after('button_color_cantina');
            }
            if (! Schema::hasColumn('settings', 'category_name_bg_color_specials')) {
                $table->text('category_name_bg_color_specials')->nullable()->after('category_name_bg_color_cantina');
            }
            if (! Schema::hasColumn('settings', 'category_name_text_color_specials')) {
                $table->text('category_name_text_color_specials')->nullable()->after('category_name_text_color_cantina');
            }
            if (! Schema::hasColumn('settings', 'category_name_font_size_specials')) {
                $table->integer('category_name_font_size_specials')->nullable()->after('category_name_font_size_cantina');
            }
            if (! Schema::hasColumn('settings', 'card_bg_color_specials')) {
                $table->text('card_bg_color_specials')->nullable()->after('card_bg_color_cantina');
            }
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            foreach ([
                'background_image_specials',
                'text_color_specials',
                'card_opacity_specials',
                'font_family_specials',
                'button_color_specials',
                'category_name_bg_color_specials',
                'category_name_text_color_specials',
                'category_name_font_size_specials',
                'card_bg_color_specials',
            ] as $column) {
                if (Schema::hasColumn('settings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
