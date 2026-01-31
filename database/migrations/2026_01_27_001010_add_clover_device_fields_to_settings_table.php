<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (! Schema::hasColumn('settings', 'clover_device_host')) {
                $table->text('clover_device_host')->nullable();
            }
            if (! Schema::hasColumn('settings', 'clover_device_token')) {
                $table->text('clover_device_token')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (Schema::hasColumn('settings', 'clover_device_token')) {
                $table->dropColumn('clover_device_token');
            }
            if (Schema::hasColumn('settings', 'clover_device_host')) {
                $table->dropColumn('clover_device_host');
            }
        });
    }
};
