<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (! Schema::hasColumn('settings', 'background_image_cantina')) {
                $table->text('background_image_cantina')->nullable()->after('background_image_wines');
            }
            if (! Schema::hasColumn('settings', 'text_color_cantina')) {
                $table->text('text_color_cantina')->nullable()->after('text_color_wines');
            }
            if (! Schema::hasColumn('settings', 'card_opacity_cantina')) {
                $table->decimal('card_opacity_cantina', 3, 2)->nullable()->after('card_opacity_wines');
            }
            if (! Schema::hasColumn('settings', 'font_family_cantina')) {
                $table->text('font_family_cantina')->nullable()->after('font_family_wines');
            }
            if (! Schema::hasColumn('settings', 'button_color_cantina')) {
                $table->text('button_color_cantina')->nullable()->after('button_color_wines');
            }
            if (! Schema::hasColumn('settings', 'category_name_bg_color_cantina')) {
                $table->text('category_name_bg_color_cantina')->nullable()->after('category_name_font_size_wines');
            }
            if (! Schema::hasColumn('settings', 'category_name_text_color_cantina')) {
                $table->text('category_name_text_color_cantina')->nullable()->after('category_name_bg_color_cantina');
            }
            if (! Schema::hasColumn('settings', 'category_name_font_size_cantina')) {
                $table->integer('category_name_font_size_cantina')->nullable()->after('category_name_text_color_cantina');
            }
            if (! Schema::hasColumn('settings', 'card_bg_color_cantina')) {
                $table->text('card_bg_color_cantina')->nullable()->after('card_bg_color_wines');
            }
            if (! Schema::hasColumn('settings', 'button_label_cantina')) {
                $table->text('button_label_cantina')->nullable()->after('button_label_wines');
            }
            if (! Schema::hasColumn('settings', 'tab_label_cantina')) {
                $table->text('tab_label_cantina')->nullable()->after('tab_label_wines');
            }
            if (! Schema::hasColumn('settings', 'show_tab_cantina')) {
                $table->boolean('show_tab_cantina')->default(true)->after('show_tab_wines');
            }
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $columns = [
                'background_image_cantina',
                'text_color_cantina',
                'card_opacity_cantina',
                'font_family_cantina',
                'button_color_cantina',
                'category_name_bg_color_cantina',
                'category_name_text_color_cantina',
                'category_name_font_size_cantina',
                'card_bg_color_cantina',
                'button_label_cantina',
                'tab_label_cantina',
                'show_tab_cantina',
            ];

            $columns = array_filter($columns, fn ($column) => Schema::hasColumn('settings', $column));
            if (count($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
