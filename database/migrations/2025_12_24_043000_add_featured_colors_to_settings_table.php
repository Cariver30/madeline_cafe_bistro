<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $columns = [
                'featured_card_bg_color',
                'featured_card_text_color',
                'featured_tab_bg_color',
                'featured_tab_text_color',
            ];

            foreach ($columns as $column) {
                if (!Schema::hasColumn('settings', $column)) {
                    $table->string($column)->nullable()->after('cover_cta_reservations_text_color');
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $columns = [
                'featured_card_bg_color',
                'featured_card_text_color',
                'featured_tab_bg_color',
                'featured_tab_text_color',
            ];

            $columns = array_filter($columns, fn ($column) => Schema::hasColumn('settings', $column));

            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
