<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wines', function (Blueprint $table) {
            $table->unsignedInteger('position')->default(0)->after('category_id');
        });

        $positions = [];
        DB::table('wines')->select('id', 'category_id')->orderBy('category_id')->orderBy('id')->chunk(200, function ($items) use (&$positions) {
            foreach ($items as $item) {
                $positions[$item->category_id] = ($positions[$item->category_id] ?? 0) + 1;
                DB::table('wines')->where('id', $item->id)->update(['position' => $positions[$item->category_id]]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('wines', function (Blueprint $table) {
            $table->dropColumn('position');
        });
    }
};
