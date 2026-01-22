<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('prep_areas')) {
            Schema::create('prep_areas', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('color')->nullable();
                $table->boolean('active')->default(true);
                $table->boolean('is_default')->default(false);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('prep_labels')) {
            Schema::create('prep_labels', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->foreignId('prep_area_id')->constrained('prep_areas')->cascadeOnDelete();
                $table->foreignId('printer_id')->nullable()->constrained('printers')->nullOnDelete();
                $table->boolean('active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('prep_labelables')) {
            Schema::create('prep_labelables', function (Blueprint $table) {
                $table->id();
                $table->foreignId('prep_label_id')->constrained('prep_labels')->cascadeOnDelete();
                $table->morphs('labelable');
                $table->timestamps();
                $table->unique(['prep_label_id', 'labelable_type', 'labelable_id'], 'prep_labelables_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('prep_labelables');
        Schema::dropIfExists('prep_labels');
        Schema::dropIfExists('prep_areas');
    }
};
