<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOpeningcalendarsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('openingcalendars', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('bucket_id');
            $table->string('openingcalendar_name');
            $table->string('openingcalendar_sub_name');
            $table->integer('month_format');
            $table->integer('week_format');
            $table->integer('view_before_month');
            $table->integer('view_after_month');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('openingcalendars');
    }
}
