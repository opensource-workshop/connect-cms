<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSampleformsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sampleforms', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('bucket_id');
            $table->string('form_name')->nullable();
            $table->string('column_text')->nullable();
            $table->string('column_password')->nullable();
            $table->string('column_checkbox')->nullable();
            $table->string('column_radio')->nullable();
            $table->string('column_file')->nullable();
            $table->string('column_hidden')->nullable();
            $table->text('column_textarea')->nullable();
            $table->string('column_select')->nullable();
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
        Schema::dropIfExists('sampleforms');
    }
}
