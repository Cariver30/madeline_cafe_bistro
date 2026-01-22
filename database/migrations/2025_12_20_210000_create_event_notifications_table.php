<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('email');
            $table->boolean('confirmed')->default(false);
            $table->string('confirmation_token')->nullable();
            $table->timestamps();

            $table->unique(['event_id', 'email']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_notifications');
    }
};
