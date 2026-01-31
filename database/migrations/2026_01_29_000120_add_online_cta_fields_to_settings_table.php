<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (! Schema::hasColumn('settings', 'cta_image_online')) {
                $table->text('cta_image_online')->nullable()->after('cta_image_reservations');
            }
            if (! Schema::hasColumn('settings', 'cover_cta_online_bg_color')) {
                $table->text('cover_cta_online_bg_color')->nullable()->after('cover_cta_reservations_bg_color');
            }
            if (! Schema::hasColumn('settings', 'cover_cta_online_text_color')) {
                $table->text('cover_cta_online_text_color')->nullable()->after('cover_cta_reservations_text_color');
            }
            if (! Schema::hasColumn('settings', 'show_cta_online')) {
                $table->boolean('show_cta_online')->default(true)->after('show_cta_reservations');
            }
            if (! Schema::hasColumn('settings', 'button_label_online')) {
                $table->text('button_label_online')->nullable()->after('button_label_reservations');
            }
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $columns = collect([
                'cta_image_online',
                'cover_cta_online_bg_color',
                'cover_cta_online_text_color',
                'show_cta_online',
                'button_label_online',
            ])->filter(fn ($column) => Schema::hasColumn('settings', $column))->all();

            if (! empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
