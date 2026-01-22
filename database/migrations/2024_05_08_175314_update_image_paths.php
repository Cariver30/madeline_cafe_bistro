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
        DB::table('dishes')->get()->each(function ($dish) {
            $imagePath = ltrim($dish->image, '/');

            if (!Str::startsWith($imagePath, 'storage/')) {
                $imagePath = 'storage/' . $imagePath;
            }

            DB::table('dishes')
                ->where('id', $dish->id)
                ->update(['image' => $imagePath]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
