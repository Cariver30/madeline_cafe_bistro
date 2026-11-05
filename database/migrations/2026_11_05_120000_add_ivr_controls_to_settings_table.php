<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TOGGLES = [
        'ivr_enabled',
        'ivr_option_hours',
        'ivr_option_menu',
        'ivr_option_location',
        'ivr_option_order',
        'ivr_option_transfer',
    ];

    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            foreach (self::TOGGLES as $column) {
                if (! Schema::hasColumn('settings', $column)) {
                    $table->boolean($column)->default(true);
                }
            }
            if (! Schema::hasColumn('settings', 'ivr_transfer_number')) {
                $table->text('ivr_transfer_number')->nullable();
            }
            if (! Schema::hasColumn('settings', 'ivr_hours_es')) {
                $table->text('ivr_hours_es')->nullable();
            }
            if (! Schema::hasColumn('settings', 'ivr_hours_en')) {
                $table->text('ivr_hours_en')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            foreach ([...self::TOGGLES, 'ivr_transfer_number', 'ivr_hours_es', 'ivr_hours_en'] as $column) {
                if (Schema::hasColumn('settings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
