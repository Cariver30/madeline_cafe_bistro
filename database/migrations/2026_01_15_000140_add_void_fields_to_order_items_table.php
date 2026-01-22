<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('order_items', 'voided_at')) {
                $table->timestamp('voided_at')->nullable()->after('notes');
            }
            if (!Schema::hasColumn('order_items', 'voided_by')) {
                $table->foreignId('voided_by')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete()
                    ->after('voided_at');
            }
            if (!Schema::hasColumn('order_items', 'void_reason')) {
                $table->string('void_reason', 255)->nullable()->after('voided_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            if (Schema::hasColumn('order_items', 'void_reason')) {
                $table->dropColumn('void_reason');
            }
            if (Schema::hasColumn('order_items', 'voided_by')) {
                $table->dropConstrainedForeignId('voided_by');
            }
            if (Schema::hasColumn('order_items', 'voided_at')) {
                $table->dropColumn('voided_at');
            }
        });
    }
};
