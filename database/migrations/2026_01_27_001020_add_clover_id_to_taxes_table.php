<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('taxes', function (Blueprint $table) {
            if (! Schema::hasColumn('taxes', 'clover_id')) {
                $table->string('clover_id')->nullable()->unique()->after('id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('taxes', function (Blueprint $table) {
            if (Schema::hasColumn('taxes', 'clover_id')) {
                $table->dropUnique(['clover_id']);
                $table->dropColumn('clover_id');
            }
        });
    }
};
