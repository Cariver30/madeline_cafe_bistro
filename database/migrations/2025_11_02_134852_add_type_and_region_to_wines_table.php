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
    Schema::table('wines', function (Blueprint $table) {
        $table->foreignId('region_id')->nullable()->constrained()->nullOnDelete();
        $table->foreignId('type_id')->nullable()->constrained('wine_types')->nullOnDelete();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wines', function (Blueprint $table) {
            //
        });
    }
};
