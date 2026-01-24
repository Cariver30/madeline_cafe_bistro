<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('loyalty_rewards') && ! Schema::hasColumn('loyalty_rewards', 'expires_at')) {
            Schema::table('loyalty_rewards', function (Blueprint $table) {
                $table->date('expires_at')->nullable()->after('points_required');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('loyalty_rewards') && Schema::hasColumn('loyalty_rewards', 'expires_at')) {
            Schema::table('loyalty_rewards', function (Blueprint $table) {
                $table->dropColumn('expires_at');
            });
        }
    }
};
