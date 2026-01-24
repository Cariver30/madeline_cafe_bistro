<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('loyalty_rewards') && ! Schema::hasColumn('loyalty_rewards', 'expiration_days')) {
            Schema::table('loyalty_rewards', function (Blueprint $table) {
                $table->unsignedInteger('expiration_days')->nullable()->after('points_required');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('loyalty_rewards') && Schema::hasColumn('loyalty_rewards', 'expiration_days')) {
            Schema::table('loyalty_rewards', function (Blueprint $table) {
                $table->dropColumn('expiration_days');
            });
        }
    }
};
