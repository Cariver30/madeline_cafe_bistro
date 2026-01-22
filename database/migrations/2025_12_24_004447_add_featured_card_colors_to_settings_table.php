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
            if (! Schema::hasColumn('settings', 'featured_card_bg_color')) {
                $table->text('featured_card_bg_color')->nullable();
            }
            if (! Schema::hasColumn('settings', 'featured_card_text_color')) {
                $table->text('featured_card_text_color')->nullable();
            }
            if (! Schema::hasColumn('settings', 'featured_tab_bg_color')) {
                $table->text('featured_tab_bg_color')->nullable();
            }
            if (! Schema::hasColumn('settings', 'featured_tab_text_color')) {
                $table->text('featured_tab_text_color')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            foreach (['featured_card_bg_color', 'featured_card_text_color', 'featured_tab_bg_color', 'featured_tab_text_color'] as $column) {
                if (Schema::hasColumn('settings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
