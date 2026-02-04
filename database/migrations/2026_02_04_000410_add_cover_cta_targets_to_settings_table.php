<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (! Schema::hasColumn('settings', 'cover_cta_targets')) {
                $table->text('cover_cta_targets')->nullable()->after('cover_cta_order');
            }
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (Schema::hasColumn('settings', 'cover_cta_targets')) {
                $table->dropColumn('cover_cta_targets');
            }
        });
    }
};
