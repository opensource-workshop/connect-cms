<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOpeningcalendarsPatternsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('openingcalendars_patterns', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('openingcalendars_id');
            $table->string('color');
            $table->string('pattern');
            $table->integer('display_sequence')->default('0');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('openingcalendars_patterns');
    }
}
