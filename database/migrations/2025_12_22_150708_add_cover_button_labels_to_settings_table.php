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
            $columns = [
                'button_label_menu' => 'Menú',
                'button_label_cocktails' => 'Cócteles',
                'button_label_wines' => 'Cafe',
                'button_label_events' => 'Eventos especiales',
                'button_label_vip' => 'Lista VIP',
                'button_label_reservations' => 'Reservas',
            ];

            foreach ($columns as $column => $default) {
                if (!Schema::hasColumn('settings', $column)) {
                    $table->string($column)->nullable()->default($default);
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            foreach ([
                'button_label_menu',
                'button_label_cocktails',
                'button_label_wines',
                'button_label_events',
                'button_label_vip',
                'button_label_reservations',
            ] as $column) {
                if (Schema::hasColumn('settings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
