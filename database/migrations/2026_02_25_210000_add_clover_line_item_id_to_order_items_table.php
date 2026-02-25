<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('order_items', 'clover_line_item_id')) {
            $afterColumn = Schema::hasColumn('order_items', 'void_reason')
                ? 'void_reason'
                : 'notes';

            Schema::table('order_items', function (Blueprint $table) use ($afterColumn) {
                $table->string('clover_line_item_id', 64)
                    ->nullable()
                    ->after($afterColumn);

                $table->index('clover_line_item_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('order_items', 'clover_line_item_id')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->dropIndex(['clover_line_item_id']);
                $table->dropColumn('clover_line_item_id');
            });
        }
    }
};
