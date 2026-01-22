<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->boolean('show_tab_menu')->default(true)->after('tab_label_loyalty');
            $table->boolean('show_tab_cocktails')->default(true)->after('show_tab_menu');
            $table->boolean('show_tab_wines')->default(true)->after('show_tab_cocktails');
            $table->boolean('show_tab_events')->default(true)->after('show_tab_wines');
            $table->boolean('show_tab_campaigns')->default(true)->after('show_tab_events');
            $table->boolean('show_tab_popups')->default(true)->after('show_tab_campaigns');
            $table->boolean('show_tab_loyalty')->default(true)->after('show_tab_popups');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'show_tab_menu',
                'show_tab_cocktails',
                'show_tab_wines',
                'show_tab_events',
                'show_tab_campaigns',
                'show_tab_popups',
                'show_tab_loyalty',
            ]);
        });
    }
};
