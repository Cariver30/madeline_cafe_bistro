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
            if (!Schema::hasColumn('settings', 'loyalty_points_per_visit')) {
                $table->unsignedInteger('loyalty_points_per_visit')->default(10)->after('cta_image_reservations');
            }
            if (!Schema::hasColumn('settings', 'loyalty_terms')) {
                $table->text('loyalty_terms')->nullable()->after('loyalty_points_per_visit');
            }
            if (!Schema::hasColumn('settings', 'loyalty_email_copy')) {
                $table->text('loyalty_email_copy')->nullable()->after('loyalty_terms');
            }
            if (!Schema::hasColumn('settings', 'tab_label_menu')) {
                $table->string('tab_label_menu')->nullable()->after('loyalty_email_copy');
            }
            if (!Schema::hasColumn('settings', 'tab_label_cocktails')) {
                $table->string('tab_label_cocktails')->nullable()->after('tab_label_menu');
            }
            if (!Schema::hasColumn('settings', 'tab_label_wines')) {
                $table->string('tab_label_wines')->nullable()->after('tab_label_cocktails');
            }
            if (!Schema::hasColumn('settings', 'tab_label_events')) {
                $table->string('tab_label_events')->nullable()->after('tab_label_wines');
            }
            if (!Schema::hasColumn('settings', 'tab_label_loyalty')) {
                $table->string('tab_label_loyalty')->nullable()->after('tab_label_events');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $columns = collect([
                'loyalty_points_per_visit',
                'loyalty_terms',
                'loyalty_email_copy',
                'tab_label_menu',
                'tab_label_cocktails',
                'tab_label_wines',
                'tab_label_events',
                'tab_label_loyalty',
            ])->filter(fn ($column) => Schema::hasColumn('settings', $column))->all();

            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
