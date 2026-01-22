<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cocktail_categories', function (Blueprint $table) {
            $table->unsignedInteger('order')->default(0)->after('name');
        });

        Schema::table('wine_categories', function (Blueprint $table) {
            $table->unsignedInteger('order')->default(0)->after('name');
        });

        $position = 0;
        DB::table('cocktail_categories')->orderBy('id')->chunk(200, function ($categories) use (&$position) {
            foreach ($categories as $category) {
                $position++;
                DB::table('cocktail_categories')->where('id', $category->id)->update(['order' => $position]);
            }
        });

        $position = 0;
        DB::table('wine_categories')->orderBy('id')->chunk(200, function ($categories) use (&$position) {
            foreach ($categories as $category) {
                $position++;
                DB::table('wine_categories')->where('id', $category->id)->update(['order' => $position]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('cocktail_categories', function (Blueprint $table) {
            $table->dropColumn('order');
        });

        Schema::table('wine_categories', function (Blueprint $table) {
            $table->dropColumn('order');
        });
    }
};
