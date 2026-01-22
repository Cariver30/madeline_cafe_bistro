<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $map = [
                'menu',
                'cafe',
                'cocktails',
                'events',
                'reservations',
            ];

            foreach ($map as $key) {
                $bgColumn = "cover_cta_{$key}_bg_color";
                $textColumn = "cover_cta_{$key}_text_color";

                if (!Schema::hasColumn('settings', $bgColumn)) {
                    $table->text($bgColumn)->nullable()->after('cover_highlight_3_description');
                }
                if (!Schema::hasColumn('settings', $textColumn)) {
                    $table->text($textColumn)->nullable()->after($bgColumn);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $columns = [
                'cover_cta_menu_bg_color',
                'cover_cta_menu_text_color',
                'cover_cta_cafe_bg_color',
                'cover_cta_cafe_text_color',
                'cover_cta_cocktails_bg_color',
                'cover_cta_cocktails_text_color',
                'cover_cta_events_bg_color',
                'cover_cta_events_text_color',
                'cover_cta_reservations_bg_color',
                'cover_cta_reservations_text_color',
            ];

            $columns = array_filter($columns, fn ($column) => Schema::hasColumn('settings', $column));

            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
