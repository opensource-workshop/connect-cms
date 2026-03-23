<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropCodestudiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('codestudies');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('codestudies', function (Blueprint $table) {
            $table->increments('id');
            $table->string('study_lang');
            $table->text('title')->nullable();
            $table->text('code_text')->nullable();
            $table->integer('created_id')->nullable();
            $table->string('created_name', 255)->nullable();
            $table->timestamps();
            $table->integer('updated_id')->nullable();
            $table->string('updated_name', 255)->nullable();
            $table->integer('deleted_id')->nullable();
            $table->string('deleted_name', 255)->nullable();
            $table->softDeletes();
        });
    }
}
