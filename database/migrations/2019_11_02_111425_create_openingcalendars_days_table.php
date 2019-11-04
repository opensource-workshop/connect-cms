<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOpeningcalendarsDaysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('openingcalendars_days', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('openingcalendars_id');
            $table->date('opening_date');
            $table->integer('openingcalendars_patterns_id');
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
        Schema::dropIfExists('openingcalendars_days');
    }
}
