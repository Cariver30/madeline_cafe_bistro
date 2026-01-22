<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (!Schema::hasColumn('settings', 'text_color_cover_secondary')) {
                $table->string('text_color_cover_secondary')->nullable()->after('text_color_cover');
            }
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (Schema::hasColumn('settings', 'text_color_cover_secondary')) {
                $table->dropColumn('text_color_cover_secondary');
            }
        });
    }
};
