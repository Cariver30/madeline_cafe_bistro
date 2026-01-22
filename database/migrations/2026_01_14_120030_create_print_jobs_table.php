<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('print_jobs')) {
            Schema::create('print_jobs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('printer_id')->constrained('printers')->cascadeOnDelete();
                $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
                $table->foreignId('print_template_id')->constrained('print_templates')->cascadeOnDelete();
                $table->string('content_type')->default('text/plain');
                $table->longText('payload');
                $table->string('status')->default('pending');
                $table->timestamp('printed_at')->nullable();
                $table->timestamps();

                $table->index(['printer_id', 'status']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('print_jobs');
    }
};
