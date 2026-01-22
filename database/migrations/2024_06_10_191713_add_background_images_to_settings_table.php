<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBackgroundImagesToSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('settings', function (Blueprint $table) {
            if (!Schema::hasColumn('settings', 'background_image_cover')) {
                $table->string('background_image_cover')->nullable();
            }
            if (!Schema::hasColumn('settings', 'background_image_menu')) {
                $table->string('background_image_menu')->nullable();
            }
            if (!Schema::hasColumn('settings', 'background_image_cocktails')) {
                $table->string('background_image_cocktails')->nullable();
            }
            if (!Schema::hasColumn('settings', 'background_image_wines')) {
                $table->string('background_image_wines')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('background_image_cover');
            $table->dropColumn('background_image_menu');
            $table->dropColumn('background_image_cocktails');
            $table->dropColumn('background_image_wines');
        });
    }
}
