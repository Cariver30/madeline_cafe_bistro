<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('loyalty_redemptions')) {
            Schema::table('loyalty_redemptions', function (Blueprint $table) {
                if (! Schema::hasColumn('loyalty_redemptions', 'qr_token')) {
                    $table->uuid('qr_token')->nullable()->unique()->after('points_used');
                }
                if (! Schema::hasColumn('loyalty_redemptions', 'expires_at')) {
                    $table->date('expires_at')->nullable()->after('qr_token');
                }
                if (! Schema::hasColumn('loyalty_redemptions', 'redeemed_at')) {
                    $table->timestamp('redeemed_at')->nullable()->after('status');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('loyalty_redemptions')) {
            Schema::table('loyalty_redemptions', function (Blueprint $table) {
                if (Schema::hasColumn('loyalty_redemptions', 'redeemed_at')) {
                    $table->dropColumn('redeemed_at');
                }
                if (Schema::hasColumn('loyalty_redemptions', 'expires_at')) {
                    $table->dropColumn('expires_at');
                }
                if (Schema::hasColumn('loyalty_redemptions', 'qr_token')) {
                    $table->dropColumn('qr_token');
                }
            });
        }
    }
};
