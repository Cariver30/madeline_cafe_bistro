<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('table_sessions')) {
            Schema::create('table_sessions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('server_id')->constrained('users')->cascadeOnDelete();
                $table->string('table_label');
                $table->unsignedSmallInteger('party_size');
                $table->string('guest_name');
                $table->string('guest_email');
                $table->string('guest_phone');
                $table->string('qr_token')->unique();
                $table->enum('status', ['active', 'closed', 'expired'])->default('active');
                $table->timestamp('expires_at')->nullable();
                $table->timestamp('closed_at')->nullable();
                $table->timestamps();

                $table->index(['status', 'expires_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('table_sessions');
    }
};
