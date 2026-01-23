<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (! Schema::hasColumn('settings', 'cta_image_cantina')) {
                $table->string('cta_image_cantina')->nullable()->after('cta_image_cocktails');
            }
            if (! Schema::hasColumn('settings', 'cover_cta_cantina_bg_color')) {
                $table->text('cover_cta_cantina_bg_color')->nullable()->after('cover_cta_cocktails_bg_color');
            }
            if (! Schema::hasColumn('settings', 'cover_cta_cantina_text_color')) {
                $table->text('cover_cta_cantina_text_color')->nullable()->after('cover_cta_cocktails_text_color');
            }
            if (! Schema::hasColumn('settings', 'show_cta_cantina')) {
                $table->boolean('show_cta_cantina')->default(true)->after('show_cta_cocktails');
            }
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $columns = [
                'cta_image_cantina',
                'cover_cta_cantina_bg_color',
                'cover_cta_cantina_text_color',
                'show_cta_cantina',
            ];

            $columns = array_filter($columns, fn ($column) => Schema::hasColumn('settings', $column));
            if (count($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
