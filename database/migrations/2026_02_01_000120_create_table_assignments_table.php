<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('table_assignments')) {
            return;
        }

        Schema::create('table_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('waiting_list_entry_id')->constrained('waiting_list_entries')->cascadeOnDelete();
            $table->foreignId('dining_table_id')->constrained('dining_tables')->cascadeOnDelete();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('released_at')->nullable();
            $table->timestamps();

            $table->unique(['waiting_list_entry_id', 'dining_table_id'], 'table_assignments_entry_table_unique');
            $table->index(['dining_table_id', 'released_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('table_assignments');
    }
};
