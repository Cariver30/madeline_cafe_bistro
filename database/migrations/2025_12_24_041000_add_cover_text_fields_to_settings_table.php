<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (!Schema::hasColumn('settings', 'cover_hero_kicker')) {
                $table->text('cover_hero_kicker')->nullable()->after('text_color_cover_secondary');
            }
            if (!Schema::hasColumn('settings', 'cover_hero_title')) {
                $table->text('cover_hero_title')->nullable()->after('cover_hero_kicker');
            }
            if (!Schema::hasColumn('settings', 'cover_hero_paragraph')) {
                $table->text('cover_hero_paragraph')->nullable()->after('cover_hero_title');
            }
            if (!Schema::hasColumn('settings', 'cover_location_text')) {
                $table->text('cover_location_text')->nullable()->after('cover_hero_paragraph');
            }

            for ($i = 1; $i <= 3; $i++) {
                $labelColumn = "cover_highlight_{$i}_label";
                $titleColumn = "cover_highlight_{$i}_title";
                $descriptionColumn = "cover_highlight_{$i}_description";

                if (!Schema::hasColumn('settings', $labelColumn)) {
                    $table->text($labelColumn)->nullable()->after('cover_location_text');
                }
                if (!Schema::hasColumn('settings', $titleColumn)) {
                    $table->text($titleColumn)->nullable()->after($labelColumn);
                }
                if (!Schema::hasColumn('settings', $descriptionColumn)) {
                    $table->text($descriptionColumn)->nullable()->after($titleColumn);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $columns = [
                'cover_hero_kicker',
                'cover_hero_title',
                'cover_hero_paragraph',
                'cover_location_text',
                'cover_highlight_1_label',
                'cover_highlight_1_title',
                'cover_highlight_1_description',
                'cover_highlight_2_label',
                'cover_highlight_2_title',
                'cover_highlight_2_description',
                'cover_highlight_3_label',
                'cover_highlight_3_title',
                'cover_highlight_3_description',
            ];

            $columns = array_filter($columns, fn ($column) => Schema::hasColumn('settings', $column));

            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
