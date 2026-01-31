<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_batches', function (Blueprint $table) {
            if (! Schema::hasColumn('order_batches', 'metered_opened_at')) {
                $table->timestamp('metered_opened_at')->nullable()->after('confirmed_at');
            }
            if (! Schema::hasColumn('order_batches', 'metered_closed_at')) {
                $table->timestamp('metered_closed_at')->nullable()->after('metered_opened_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('order_batches', function (Blueprint $table) {
            if (Schema::hasColumn('order_batches', 'metered_closed_at')) {
                $table->dropColumn('metered_closed_at');
            }
            if (Schema::hasColumn('order_batches', 'metered_opened_at')) {
                $table->dropColumn('metered_opened_at');
            }
        });
    }
};
