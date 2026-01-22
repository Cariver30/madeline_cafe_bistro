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
    Schema::table('settings', function (Blueprint $table) {
        $table->string('facebook_url')->nullable();
        $table->string('twitter_url')->nullable();
        $table->string('instagram_url')->nullable();
        $table->string('phone_number')->nullable();
        $table->text('business_hours')->nullable();
    });
}

public function down()
{
    Schema::table('settings', function (Blueprint $table) {
        $table->dropColumn('facebook_url');
        $table->dropColumn('twitter_url');
        $table->dropColumn('instagram_url');
        $table->dropColumn('phone_number');
        $table->dropColumn('business_hours');
    });
}

};
