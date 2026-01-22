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
            if (!Schema::hasColumn('settings', 'background_image')) {
                $table->string('background_image')->nullable();
            }

            if (!Schema::hasColumn('settings', 'category_name_bg_color')) {
                $table->string('category_name_bg_color')->nullable();
            }

            if (!Schema::hasColumn('settings', 'category_name_text_color')) {
                $table->string('category_name_text_color')->nullable();
            }

            if (!Schema::hasColumn('settings', 'category_name_font_size')) {
                $table->integer('category_name_font_size')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            foreach ([
                'background_image',
                'category_name_bg_color',
                'category_name_text_color',
                'category_name_font_size',
            ] as $column) {
                if (Schema::hasColumn('settings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
