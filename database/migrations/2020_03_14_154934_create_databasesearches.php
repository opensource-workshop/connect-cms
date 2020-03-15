<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDatabasesearches extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('databasesearches', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('bucket_id');
            $table->string('databasesearches_name');
            $table->integer('view_count');
            $table->text('view_columns');
            $table->text('condition')->nullable();
            $table->integer('frame_select')->default('0');
            $table->text('target_frame_ids')->nullable();
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
        Schema::dropIfExists('databasesearches');
    }
}
