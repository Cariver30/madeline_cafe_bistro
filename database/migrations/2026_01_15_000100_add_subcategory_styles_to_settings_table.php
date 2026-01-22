<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (!Schema::hasColumn('settings', 'subcategory_name_bg_color_menu')) {
                $table->text('subcategory_name_bg_color_menu')->nullable();
            }
            if (!Schema::hasColumn('settings', 'subcategory_name_text_color_menu')) {
                $table->text('subcategory_name_text_color_menu')->nullable();
            }
            if (!Schema::hasColumn('settings', 'subcategory_name_bg_color_cocktails')) {
                $table->text('subcategory_name_bg_color_cocktails')->nullable();
            }
            if (!Schema::hasColumn('settings', 'subcategory_name_text_color_cocktails')) {
                $table->text('subcategory_name_text_color_cocktails')->nullable();
            }
            if (!Schema::hasColumn('settings', 'subcategory_name_bg_color_wines')) {
                $table->text('subcategory_name_bg_color_wines')->nullable();
            }
            if (!Schema::hasColumn('settings', 'subcategory_name_text_color_wines')) {
                $table->text('subcategory_name_text_color_wines')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $columns = [
                'subcategory_name_bg_color_menu',
                'subcategory_name_text_color_menu',
                'subcategory_name_bg_color_cocktails',
                'subcategory_name_text_color_cocktails',
                'subcategory_name_bg_color_wines',
                'subcategory_name_text_color_wines',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('settings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
