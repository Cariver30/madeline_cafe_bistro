<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (! Schema::hasColumn('settings', 'online_enabled')) {
                $table->boolean('online_enabled')->default(true)->after('business_hours');
            }
            if (! Schema::hasColumn('settings', 'online_pause_message')) {
                $table->string('online_pause_message')->nullable()->after('online_enabled');
            }
            if (! Schema::hasColumn('settings', 'online_schedule')) {
                $table->json('online_schedule')->nullable()->after('online_pause_message');
            }
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (Schema::hasColumn('settings', 'online_schedule')) {
                $table->dropColumn('online_schedule');
            }
            if (Schema::hasColumn('settings', 'online_pause_message')) {
                $table->dropColumn('online_pause_message');
            }
            if (Schema::hasColumn('settings', 'online_enabled')) {
                $table->dropColumn('online_enabled');
            }
        });
    }
};
