<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('loyalty_redemptions') && ! Schema::hasColumn('loyalty_redemptions', 'expiration_notified_days')) {
            Schema::table('loyalty_redemptions', function (Blueprint $table) {
                $table->json('expiration_notified_days')->nullable()->after('redeemed_at');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('loyalty_redemptions') && Schema::hasColumn('loyalty_redemptions', 'expiration_notified_days')) {
            Schema::table('loyalty_redemptions', function (Blueprint $table) {
                $table->dropColumn('expiration_notified_days');
            });
        }
    }
};
