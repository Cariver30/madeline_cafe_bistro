<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('order_batches')) {
            return;
        }

        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys=off');

            Schema::create('order_batches_new', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
                $table->enum('source', ['server', 'table', 'pos'])->default('server');
                $table->enum('status', ['pending', 'confirmed', 'cancelled'])->default('pending');
                $table->timestamp('confirmed_at')->nullable();
                $table->timestamp('cancelled_at')->nullable();
                $table->timestamps();

                $table->index(['status', 'order_id']);
            });

            DB::statement('
                INSERT INTO order_batches_new (id, order_id, source, status, confirmed_at, cancelled_at, created_at, updated_at)
                SELECT id, order_id, source, status, confirmed_at, cancelled_at, created_at, updated_at
                FROM order_batches
            ');

            Schema::drop('order_batches');
            DB::statement('ALTER TABLE order_batches_new RENAME TO order_batches');
            DB::statement('PRAGMA foreign_keys=on');

            return;
        }

        DB::statement(
            "ALTER TABLE order_batches MODIFY source ENUM('server','table','pos') NOT NULL DEFAULT 'server'"
        );
    }

    public function down(): void
    {
        if (!Schema::hasTable('order_batches')) {
            return;
        }

        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys=off');

            Schema::create('order_batches_new', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
                $table->enum('source', ['server', 'table'])->default('server');
                $table->enum('status', ['pending', 'confirmed', 'cancelled'])->default('pending');
                $table->timestamp('confirmed_at')->nullable();
                $table->timestamp('cancelled_at')->nullable();
                $table->timestamps();

                $table->index(['status', 'order_id']);
            });

            DB::statement('
                INSERT INTO order_batches_new (id, order_id, source, status, confirmed_at, cancelled_at, created_at, updated_at)
                SELECT id, order_id,
                    CASE WHEN source = "pos" THEN "server" ELSE source END,
                    status, confirmed_at, cancelled_at, created_at, updated_at
                FROM order_batches
            ');

            Schema::drop('order_batches');
            DB::statement('ALTER TABLE order_batches_new RENAME TO order_batches');
            DB::statement('PRAGMA foreign_keys=on');

            return;
        }

        DB::statement(
            "ALTER TABLE order_batches MODIFY source ENUM('server','table') NOT NULL DEFAULT 'server'"
        );
    }
};
