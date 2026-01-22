<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (!Schema::hasColumn('settings', 'tab_label_menu')) {
                $table->string('tab_label_menu')->nullable()->default('Menú');
            }
            if (!Schema::hasColumn('settings', 'tab_label_cocktails')) {
                $table->string('tab_label_cocktails')->nullable()->default('Cócteles');
            }
            if (!Schema::hasColumn('settings', 'tab_label_wines')) {
                $table->string('tab_label_wines')->nullable()->default('Café & Brunch');
            }
            if (!Schema::hasColumn('settings', 'tab_label_events')) {
                $table->string('tab_label_events')->nullable()->default('Eventos');
            }
            if (!Schema::hasColumn('settings', 'tab_label_loyalty')) {
                $table->string('tab_label_loyalty')->nullable()->default('Fidelidad');
            }
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'tab_label_menu',
                'tab_label_cocktails',
                'tab_label_wines',
                'tab_label_events',
                'tab_label_loyalty',
            ]);
        });
    }
};
