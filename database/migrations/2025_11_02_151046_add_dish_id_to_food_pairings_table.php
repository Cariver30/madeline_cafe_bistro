<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('food_pairings', function (Blueprint $table) {
            $table->foreignId('dish_id')->nullable()->constrained()->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('food_pairings', function (Blueprint $table) {
            $table->dropForeign(['dish_id']);
            $table->dropColumn('dish_id');
        });
    }
};
