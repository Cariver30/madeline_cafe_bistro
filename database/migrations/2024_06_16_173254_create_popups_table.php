<?php

// database/migrations/xxxx_xx_xx_xxxxxx_create_popups_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePopupsTable extends Migration
{
    public function up()
    {
        Schema::create('popups', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('image');
            $table->string('view');
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->boolean('active')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('popups');
    }
}

