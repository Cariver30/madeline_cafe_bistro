<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('print_templates')) {
            Schema::create('print_templates', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('type')->default('ticket');
                $table->text('body');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('print_templates');
    }
};
