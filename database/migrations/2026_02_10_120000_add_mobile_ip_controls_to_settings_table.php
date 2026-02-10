<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (! Schema::hasColumn('settings', 'mobile_ip_restriction_enabled')) {
                $table->boolean('mobile_ip_restriction_enabled')->default(false);
            }
            if (! Schema::hasColumn('settings', 'mobile_ip_allowlist')) {
                $table->text('mobile_ip_allowlist')->nullable();
            }
            if (! Schema::hasColumn('settings', 'mobile_ip_bypass_emails')) {
                $table->text('mobile_ip_bypass_emails')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (Schema::hasColumn('settings', 'mobile_ip_restriction_enabled')) {
                $table->dropColumn('mobile_ip_restriction_enabled');
            }
            if (Schema::hasColumn('settings', 'mobile_ip_allowlist')) {
                $table->dropColumn('mobile_ip_allowlist');
            }
            if (Schema::hasColumn('settings', 'mobile_ip_bypass_emails')) {
                $table->dropColumn('mobile_ip_bypass_emails');
            }
        });
    }
};
