<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('printer_routes')) {
            Schema::create('printer_routes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('printer_id')->constrained('printers')->cascadeOnDelete();
                $table->foreignId('print_template_id')->constrained('print_templates')->cascadeOnDelete();
                $table->string('category_scope')->default('all');
                $table->unsignedBigInteger('category_id')->nullable();
                $table->boolean('enabled')->default(true);
                $table->timestamps();

                $table->index(['category_scope', 'category_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('printer_routes');
    }
};
