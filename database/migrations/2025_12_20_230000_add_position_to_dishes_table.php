<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dishes', function (Blueprint $table) {
            $table->unsignedInteger('position')->default(0)->after('category_id');
        });

        $positions = [];
        DB::table('dishes')->select('id', 'category_id')->orderBy('category_id')->orderBy('id')->chunk(200, function ($dishes) use (&$positions) {
            foreach ($dishes as $dish) {
                $positions[$dish->category_id] = ($positions[$dish->category_id] ?? 0) + 1;
                DB::table('dishes')->where('id', $dish->id)->update(['position' => $positions[$dish->category_id]]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('dishes', function (Blueprint $table) {
            $table->dropColumn('position');
        });
    }
};
