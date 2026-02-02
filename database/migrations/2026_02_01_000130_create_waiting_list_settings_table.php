<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('waiting_list_settings')) {
            return;
        }

        Schema::create('waiting_list_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('default_wait_minutes')->default(15);
            $table->unsignedSmallInteger('notify_after_minutes')->default(10);
            $table->boolean('sms_enabled')->default(true);
            $table->boolean('email_enabled')->default(false);
            $table->string('notify_message_template')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('waiting_list_settings');
    }
};
