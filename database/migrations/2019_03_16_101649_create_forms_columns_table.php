<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFormsColumnsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('forms_columns', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('forms_inputs_id');
            $table->string('column_type');
            $table->string('column_name');
            $table->integer('required');
            $table->integer('frame_col')->nullable();
            $table->integer('display_sequence');
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
        Schema::dropIfExists('forms_columns');
    }
}
