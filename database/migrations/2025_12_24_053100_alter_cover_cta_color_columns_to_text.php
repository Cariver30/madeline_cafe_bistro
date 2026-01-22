<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

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

        foreach ($columns as $column) {
            if (Schema::hasColumn('settings', $column)) {
                DB::statement("ALTER TABLE `settings` MODIFY COLUMN `{$column}` TEXT NULL");
            }
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

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

        foreach ($columns as $column) {
            if (Schema::hasColumn('settings', $column)) {
                DB::statement("ALTER TABLE `settings` MODIFY COLUMN `{$column}` VARCHAR(255) NULL");
            }
        }
    }
};
