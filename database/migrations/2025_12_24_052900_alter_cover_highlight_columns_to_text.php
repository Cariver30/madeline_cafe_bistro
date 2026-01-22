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
            'cover_hero_kicker',
            'cover_hero_title',
            'cover_location_text',
            'cover_highlight_1_label',
            'cover_highlight_1_title',
            'cover_highlight_2_label',
            'cover_highlight_2_title',
            'cover_highlight_3_label',
            'cover_highlight_3_title',
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
            'cover_hero_kicker',
            'cover_hero_title',
            'cover_location_text',
            'cover_highlight_1_label',
            'cover_highlight_1_title',
            'cover_highlight_2_label',
            'cover_highlight_2_title',
            'cover_highlight_3_label',
            'cover_highlight_3_title',
        ];

        foreach ($columns as $column) {
            if (Schema::hasColumn('settings', $column)) {
                DB::statement("ALTER TABLE `settings` MODIFY COLUMN `{$column}` VARCHAR(255) NULL");
            }
        }
    }
};
