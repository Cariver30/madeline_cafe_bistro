<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (!Schema::hasColumn('settings', 'button_label_specials')) {
                // settings table is already very wide; use TEXT to avoid hitting InnoDB row-size limits.
                $table->text('button_label_specials')->nullable();
            }
            if (!Schema::hasColumn('settings', 'show_cta_specials')) {
                $table->boolean('show_cta_specials')->default(true);
            }
            if (!Schema::hasColumn('settings', 'cta_image_specials')) {
                $table->text('cta_image_specials')->nullable();
            }
            if (!Schema::hasColumn('settings', 'cover_cta_specials_bg_color')) {
                $table->text('cover_cta_specials_bg_color')->nullable();
            }
            if (!Schema::hasColumn('settings', 'cover_cta_specials_text_color')) {
                $table->text('cover_cta_specials_text_color')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (Schema::hasColumn('settings', 'button_label_specials')) {
                $table->dropColumn('button_label_specials');
            }
            if (Schema::hasColumn('settings', 'show_cta_specials')) {
                $table->dropColumn('show_cta_specials');
            }
            if (Schema::hasColumn('settings', 'cta_image_specials')) {
                $table->dropColumn('cta_image_specials');
            }
            if (Schema::hasColumn('settings', 'cover_cta_specials_bg_color')) {
                $table->dropColumn('cover_cta_specials_bg_color');
            }
            if (Schema::hasColumn('settings', 'cover_cta_specials_text_color')) {
                $table->dropColumn('cover_cta_specials_text_color');
            }
        });
    }
};
