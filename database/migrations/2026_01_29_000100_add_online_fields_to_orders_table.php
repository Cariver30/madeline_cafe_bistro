<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('orders')) {
            return;
        }

        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            if (Schema::hasColumn('orders', 'table_session_id')) {
                DB::statement('ALTER TABLE orders MODIFY table_session_id BIGINT UNSIGNED NULL');
            }
            if (Schema::hasColumn('orders', 'server_id')) {
                DB::statement('ALTER TABLE orders MODIFY server_id BIGINT UNSIGNED NULL');
            }
        }

        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'channel')) {
                $table->string('channel', 20)->default('table')->after('server_id');
            }
            if (! Schema::hasColumn('orders', 'public_token')) {
                $table->string('public_token', 64)->nullable()->unique()->after('channel');
            }
            if (! Schema::hasColumn('orders', 'customer_name')) {
                $table->string('customer_name', 150)->nullable()->after('public_token');
            }
            if (! Schema::hasColumn('orders', 'customer_email')) {
                $table->string('customer_email', 150)->nullable()->after('customer_name');
            }
            if (! Schema::hasColumn('orders', 'customer_phone')) {
                $table->string('customer_phone', 50)->nullable()->after('customer_email');
            }
            if (! Schema::hasColumn('orders', 'pickup_at')) {
                $table->timestamp('pickup_at')->nullable()->after('customer_phone');
            }
            if (! Schema::hasColumn('orders', 'notes')) {
                $table->text('notes')->nullable()->after('pickup_at');
            }
            if (! Schema::hasColumn('orders', 'checkout_id')) {
                $table->string('checkout_id', 100)->nullable()->after('notes');
            }
            if (! Schema::hasColumn('orders', 'checkout_url')) {
                $table->text('checkout_url')->nullable()->after('checkout_id');
            }
            if (! Schema::hasColumn('orders', 'checkout_status')) {
                $table->string('checkout_status', 50)->nullable()->after('checkout_url');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('orders')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'checkout_status')) {
                $table->dropColumn('checkout_status');
            }
            if (Schema::hasColumn('orders', 'checkout_url')) {
                $table->dropColumn('checkout_url');
            }
            if (Schema::hasColumn('orders', 'checkout_id')) {
                $table->dropColumn('checkout_id');
            }
            if (Schema::hasColumn('orders', 'notes')) {
                $table->dropColumn('notes');
            }
            if (Schema::hasColumn('orders', 'pickup_at')) {
                $table->dropColumn('pickup_at');
            }
            if (Schema::hasColumn('orders', 'customer_phone')) {
                $table->dropColumn('customer_phone');
            }
            if (Schema::hasColumn('orders', 'customer_email')) {
                $table->dropColumn('customer_email');
            }
            if (Schema::hasColumn('orders', 'customer_name')) {
                $table->dropColumn('customer_name');
            }
            if (Schema::hasColumn('orders', 'public_token')) {
                $table->dropUnique(['public_token']);
                $table->dropColumn('public_token');
            }
            if (Schema::hasColumn('orders', 'channel')) {
                $table->dropColumn('channel');
            }
        });

        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            if (Schema::hasColumn('orders', 'table_session_id')) {
                DB::statement('ALTER TABLE orders MODIFY table_session_id BIGINT UNSIGNED NOT NULL');
            }
            if (Schema::hasColumn('orders', 'server_id')) {
                DB::statement('ALTER TABLE orders MODIFY server_id BIGINT UNSIGNED NOT NULL');
            }
        }
    }
};
