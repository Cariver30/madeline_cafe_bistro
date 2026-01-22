<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_promotions', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('subject');
            $table->string('preview_text')->nullable();
            $table->string('hero_image')->nullable();
            $table->text('body_html');
            $table->json('attachments')->nullable();
            $table->enum('status', ['draft', 'sending', 'sent', 'failed'])->default('draft');
            $table->timestamp('sent_at')->nullable();
            $table->unsignedInteger('send_count')->default(0);
            $table->text('send_error')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_promotions');
    }
};
