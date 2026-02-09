<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (!Schema::hasColumn('settings', 'seo_title')) {
                // Use TEXT to avoid InnoDB row-size limits on wide settings table.
                $table->text('seo_title')->nullable();
            }
            if (!Schema::hasColumn('settings', 'seo_description')) {
                $table->text('seo_description')->nullable();
            }
            if (!Schema::hasColumn('settings', 'seo_image')) {
                // Use TEXT to avoid InnoDB row-size limits on wide settings table.
                $table->text('seo_image')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (Schema::hasColumn('settings', 'seo_image')) {
                $table->dropColumn('seo_image');
            }
            if (Schema::hasColumn('settings', 'seo_description')) {
                $table->dropColumn('seo_description');
            }
            if (Schema::hasColumn('settings', 'seo_title')) {
                $table->dropColumn('seo_title');
            }
        });
    }
};
