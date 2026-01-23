<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('special_items', function (Blueprint $table) {
            if (!Schema::hasColumn('special_items', 'offer_type')) {
                $table->string('offer_type', 32)->nullable()->after('ends_at');
            }
            if (!Schema::hasColumn('special_items', 'offer_value')) {
                $table->decimal('offer_value', 10, 2)->nullable()->after('offer_type');
            }
            if (!Schema::hasColumn('special_items', 'offer_text')) {
                $table->string('offer_text', 255)->nullable()->after('offer_value');
            }
        });
    }

    public function down(): void
    {
        Schema::table('special_items', function (Blueprint $table) {
            if (Schema::hasColumn('special_items', 'offer_text')) {
                $table->dropColumn('offer_text');
            }
            if (Schema::hasColumn('special_items', 'offer_value')) {
                $table->dropColumn('offer_value');
            }
            if (Schema::hasColumn('special_items', 'offer_type')) {
                $table->dropColumn('offer_type');
            }
        });
    }
};
