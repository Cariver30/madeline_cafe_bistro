<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cantina_categories', function (Blueprint $table) {
            if (! Schema::hasColumn('cantina_categories', 'order')) {
                $table->unsignedInteger('order')->nullable()->after('name');
            }
            if (! Schema::hasColumn('cantina_categories', 'show_on_cover')) {
                $table->boolean('show_on_cover')->default(false)->after('order');
            }
            if (! Schema::hasColumn('cantina_categories', 'cover_title')) {
                $table->string('cover_title')->nullable()->after('show_on_cover');
            }
            if (! Schema::hasColumn('cantina_categories', 'cover_subtitle')) {
                $table->string('cover_subtitle')->nullable()->after('cover_title');
            }
        });

        Schema::table('cantina_items', function (Blueprint $table) {
            if (! Schema::hasColumn('cantina_items', 'visible')) {
                $table->boolean('visible')->default(true)->after('image');
            }
            if (! Schema::hasColumn('cantina_items', 'featured_on_cover')) {
                $table->boolean('featured_on_cover')->default(false)->after('visible');
            }
            if (! Schema::hasColumn('cantina_items', 'position')) {
                $table->unsignedInteger('position')->nullable()->after('featured_on_cover');
            }
        });

        if (Schema::hasColumn('cantina_categories', 'order')) {
            $position = 1;
            DB::table('cantina_categories')->orderBy('id')->chunk(200, function ($categories) use (&$position) {
                foreach ($categories as $category) {
                    DB::table('cantina_categories')
                        ->where('id', $category->id)
                        ->update(['order' => $position++]);
                }
            });
        }

        if (Schema::hasColumn('cantina_items', 'position')) {
            $positions = [];
            DB::table('cantina_items')
                ->select('id', 'category_id')
                ->orderBy('category_id')
                ->orderBy('id')
                ->chunk(200, function ($items) use (&$positions) {
                    foreach ($items as $item) {
                        $positions[$item->category_id] = ($positions[$item->category_id] ?? 0) + 1;
                        DB::table('cantina_items')
                            ->where('id', $item->id)
                            ->update(['position' => $positions[$item->category_id]]);
                    }
                });
        }
    }

    public function down(): void
    {
        Schema::table('cantina_items', function (Blueprint $table) {
            $columns = ['visible', 'featured_on_cover', 'position'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('cantina_items', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('cantina_categories', function (Blueprint $table) {
            $columns = ['order', 'show_on_cover', 'cover_title', 'cover_subtitle'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('cantina_categories', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
