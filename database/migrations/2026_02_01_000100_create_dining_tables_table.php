<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('dining_tables')) {
            return;
        }

        Schema::create('dining_tables', function (Blueprint $table) {
            $table->id();
            $table->string('label')->unique();
            $table->unsignedSmallInteger('capacity')->default(2);
            $table->string('section')->nullable();
            $table->enum('status', ['available', 'reserved', 'occupied', 'dirty', 'out_of_service'])->default('available');
            $table->unsignedInteger('position')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['status', 'section']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dining_tables');
    }
};
