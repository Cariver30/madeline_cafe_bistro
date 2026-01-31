<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('extras', function (Blueprint $table) {
            if (! Schema::hasColumn('extras', 'clover_id')) {
                $table->string('clover_id')->nullable()->unique();
            }
            if (! Schema::hasColumn('extras', 'clover_group_id')) {
                $table->string('clover_group_id')->nullable()->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('extras', function (Blueprint $table) {
            if (Schema::hasColumn('extras', 'clover_id')) {
                $table->dropUnique(['clover_id']);
                $table->dropColumn('clover_id');
            }
            if (Schema::hasColumn('extras', 'clover_group_id')) {
                $table->dropColumn('clover_group_id');
            }
        });
    }
};
