<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('loyalty_visits', function (Blueprint $table) {
            if (!Schema::hasColumn('loyalty_visits', 'metered_at')) {
                $table->timestamp('metered_at')->nullable()->after('confirmed_at');
            }
            if (!Schema::hasColumn('loyalty_visits', 'metered_event_id')) {
                $table->string('metered_event_id')->nullable()->after('metered_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loyalty_visits', function (Blueprint $table) {
            if (Schema::hasColumn('loyalty_visits', 'metered_event_id')) {
                $table->dropColumn('metered_event_id');
            }
            if (Schema::hasColumn('loyalty_visits', 'metered_at')) {
                $table->dropColumn('metered_at');
            }
        });
    }
};
