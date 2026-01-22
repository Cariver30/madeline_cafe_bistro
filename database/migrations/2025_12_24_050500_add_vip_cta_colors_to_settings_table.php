<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (!Schema::hasColumn('settings', 'cover_cta_vip_bg_color')) {
                $table->text('cover_cta_vip_bg_color')->nullable();
            }
            if (!Schema::hasColumn('settings', 'cover_cta_vip_text_color')) {
                $table->text('cover_cta_vip_text_color')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (Schema::hasColumn('settings', 'cover_cta_vip_bg_color')) {
                $table->dropColumn('cover_cta_vip_bg_color');
            }
            if (Schema::hasColumn('settings', 'cover_cta_vip_text_color')) {
                $table->dropColumn('cover_cta_vip_text_color');
            }
        });
    }
};
