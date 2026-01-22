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
        if (!Schema::hasTable('loyalty_customers')) {
            Schema::create('loyalty_customers', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email')->unique();
                $table->string('phone')->nullable();
                $table->unsignedInteger('points')->default(0);
                $table->timestamp('last_visit_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('loyalty_rewards')) {
            Schema::create('loyalty_rewards', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->text('description')->nullable();
                $table->unsignedInteger('points_required');
                $table->boolean('active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('loyalty_visits')) {
            Schema::create('loyalty_visits', function (Blueprint $table) {
                $table->id();
                $table->foreignId('server_id')->constrained('users')->cascadeOnDelete();
                $table->string('expected_name');
                $table->string('expected_email');
                $table->string('expected_phone');
                $table->string('qr_token')->unique();
                $table->enum('status', ['pending', 'confirmed', 'expired'])->default('pending');
                $table->unsignedInteger('points_awarded')->default(0);
                $table->timestamp('confirmed_at')->nullable();
                $table->json('customer_snapshot')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('loyalty_redemptions')) {
            Schema::create('loyalty_redemptions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('loyalty_customer_id')->constrained()->cascadeOnDelete();
                $table->foreignId('loyalty_reward_id')->constrained()->cascadeOnDelete();
                $table->unsignedInteger('points_used');
                $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loyalty_redemptions');
        Schema::dropIfExists('loyalty_visits');
        Schema::dropIfExists('loyalty_rewards');
        Schema::dropIfExists('loyalty_customers');
    }
};
