<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('waiting_list_entries')) {
            return;
        }

        Schema::create('waiting_list_entries', function (Blueprint $table) {
            $table->id();
            $table->string('guest_name');
            $table->string('guest_phone');
            $table->string('guest_email')->nullable();
            $table->unsignedSmallInteger('party_size');
            $table->text('notes')->nullable();
            $table->enum('status', ['waiting', 'notified', 'seated', 'cancelled', 'no_show'])->default('waiting');
            $table->unsignedSmallInteger('quoted_minutes')->nullable();
            $table->timestamp('quoted_at')->nullable();
            $table->timestamp('notified_at')->nullable();
            $table->timestamp('seated_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('no_show_at')->nullable();
            $table->string('cancel_token')->nullable()->unique();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('guest_phone');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('waiting_list_entries');
    }
};
