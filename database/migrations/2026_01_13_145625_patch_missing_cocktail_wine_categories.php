<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->ensureCategoryTable('cocktail_categories');
        $this->ensureCategoryTable('wine_categories');
    }

    public function down(): void
    {
        // No-op: this patch only fills missing structures.
    }

    private function ensureCategoryTable(string $table): void
    {
        if (!Schema::hasTable($table)) {
            Schema::create($table, function (Blueprint $tableBlueprint) {
                $tableBlueprint->id();
                $tableBlueprint->string('name');
                $tableBlueprint->unsignedInteger('order')->default(0);
                $tableBlueprint->boolean('show_on_cover')->default(false);
                $tableBlueprint->string('cover_title')->nullable();
                $tableBlueprint->string('cover_subtitle')->nullable();
                $tableBlueprint->timestamps();
            });
            return;
        }

        if (!Schema::hasColumn($table, 'order')) {
            Schema::table($table, function (Blueprint $tableBlueprint) {
                $tableBlueprint->unsignedInteger('order')->default(0);
            });

            $position = 0;
            DB::table($table)->orderBy('id')->chunk(200, function ($categories) use (&$position, $table) {
                foreach ($categories as $category) {
                    $position++;
                    DB::table($table)->where('id', $category->id)->update(['order' => $position]);
                }
            });
        }

        if (!Schema::hasColumn($table, 'show_on_cover')) {
            Schema::table($table, function (Blueprint $tableBlueprint) {
                $tableBlueprint->boolean('show_on_cover')->default(false);
            });
        }

        if (!Schema::hasColumn($table, 'cover_title')) {
            Schema::table($table, function (Blueprint $tableBlueprint) {
                $tableBlueprint->string('cover_title')->nullable();
            });
        }

        if (!Schema::hasColumn($table, 'cover_subtitle')) {
            Schema::table($table, function (Blueprint $tableBlueprint) {
                $tableBlueprint->string('cover_subtitle')->nullable();
            });
        }
    }
};
