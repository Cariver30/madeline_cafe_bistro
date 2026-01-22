<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('extra_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('extra_id')->constrained()->cascadeOnDelete();
            $table->morphs('assignable');
            $table->timestamps();

            $table->unique(['extra_id', 'assignable_id', 'assignable_type'], 'extra_assignments_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('extra_assignments');
    }
};
