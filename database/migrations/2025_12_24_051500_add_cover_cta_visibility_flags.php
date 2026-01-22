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
                'show_cta_menu',
                'show_cta_cafe',
                'show_cta_cocktails',
                'show_cta_events',
                'show_cta_reservations',
                'show_cta_vip',
            ];

            foreach ($columns as $column) {
                if (!Schema::hasColumn('settings', $column)) {
                    $table->boolean($column)->default(true);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $columns = [
                'show_cta_menu',
                'show_cta_cafe',
                'show_cta_cocktails',
                'show_cta_events',
                'show_cta_reservations',
                'show_cta_vip',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('settings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
