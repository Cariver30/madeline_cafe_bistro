<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'stripe_payment_intent_id')) {
                $table->string('stripe_payment_intent_id')->nullable()->after('paid_total');
            }
            if (!Schema::hasColumn('orders', 'stripe_charge_id')) {
                $table->string('stripe_charge_id')->nullable()->after('stripe_payment_intent_id');
            }
            if (!Schema::hasColumn('orders', 'payment_status')) {
                $table->string('payment_status')->nullable()->after('stripe_charge_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'payment_status')) {
                $table->dropColumn('payment_status');
            }
            if (Schema::hasColumn('orders', 'stripe_charge_id')) {
                $table->dropColumn('stripe_charge_id');
            }
            if (Schema::hasColumn('orders', 'stripe_payment_intent_id')) {
                $table->dropColumn('stripe_payment_intent_id');
            }
        });
    }
};
