<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('specials', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('active')->default(true);
            $table->json('days_of_week')->nullable();
            $table->time('starts_at')->nullable();
            $table->time('ends_at')->nullable();
            $table->timestamps();
        });

        Schema::create('special_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('special_id')->constrained('specials')->onDelete('cascade');
            $table->string('scope', 32);
            $table->unsignedBigInteger('category_id');
            $table->boolean('active')->default(true);
            $table->json('days_of_week')->nullable();
            $table->time('starts_at')->nullable();
            $table->time('ends_at')->nullable();
            $table->timestamps();

            $table->unique(['special_id', 'scope', 'category_id']);
            $table->index(['scope', 'category_id']);
        });

        Schema::create('special_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('special_id')->constrained('specials')->onDelete('cascade');
            $table->string('scope', 32);
            $table->unsignedBigInteger('item_id');
            $table->unsignedBigInteger('category_id')->nullable();
            $table->boolean('active')->default(true);
            $table->json('days_of_week')->nullable();
            $table->time('starts_at')->nullable();
            $table->time('ends_at')->nullable();
            $table->timestamps();

            $table->unique(['special_id', 'scope', 'item_id']);
            $table->index(['scope', 'item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('special_items');
        Schema::dropIfExists('special_categories');
        Schema::dropIfExists('specials');
    }
};
