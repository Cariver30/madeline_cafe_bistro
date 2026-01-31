<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (! Schema::hasColumn('settings', 'clover_merchant_id')) {
                $table->text('clover_merchant_id')->nullable();
            }
            if (! Schema::hasColumn('settings', 'clover_access_token')) {
                $table->text('clover_access_token')->nullable();
            }
            if (! Schema::hasColumn('settings', 'clover_env')) {
                $table->text('clover_env')->nullable();
            }
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
            if (Schema::hasColumn('settings', 'clover_merchant_id')) {
                $table->dropColumn('clover_merchant_id');
            }
            if (Schema::hasColumn('settings', 'clover_access_token')) {
                $table->dropColumn('clover_access_token');
            }
            if (Schema::hasColumn('settings', 'clover_env')) {
                $table->dropColumn('clover_env');
            }
            if (Schema::hasColumn('settings', 'clover_device_host')) {
                $table->dropColumn('clover_device_host');
            }
            if (Schema::hasColumn('settings', 'clover_device_token')) {
                $table->dropColumn('clover_device_token');
            }
        });
    }
};
