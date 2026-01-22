<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('printers')) {
            Schema::create('printers', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('model')->nullable();
                $table->string('connection')->default('cloudprnt');
                $table->string('device_id')->nullable();
                $table->string('token')->unique();
                $table->string('location')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamp('last_seen_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('printers');
    }
};
