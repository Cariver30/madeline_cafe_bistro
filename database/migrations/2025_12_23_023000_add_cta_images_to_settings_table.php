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
            $table->string('cta_image_menu')->nullable()->after('coffee_hero_image');
            $table->string('cta_image_cafe')->nullable()->after('cta_image_menu');
            $table->string('cta_image_cocktails')->nullable()->after('cta_image_cafe');
            $table->string('cta_image_events')->nullable()->after('cta_image_cocktails');
            $table->string('cta_image_reservations')->nullable()->after('cta_image_events');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'cta_image_menu',
                'cta_image_cafe',
                'cta_image_cocktails',
                'cta_image_events',
                'cta_image_reservations',
            ]);
        });
    }
};
