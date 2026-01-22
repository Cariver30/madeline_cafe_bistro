<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('grapes', function (Blueprint $table) {
        $table->id();
        $table->string('name')->unique();
        $table->foreignId('wine_type_id')->nullable()->constrained('wine_types')->nullOnDelete();
        $table->text('description')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grapes');
    }
};
