<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersColumnsSelectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_columns_selects', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('users_columns_id')->unsigned();
            $table->string('value', 255);
            // $table->string('caption')->nullable();   // database, formで使われていなく全てNULLのカラム
            // $table->string('default')->nullable();   // database, formで使われていない全てNULLのカラム
            $table->integer('display_sequence')->default('0')->comment('表示順');

            $table->integer('created_id')->nullable();
            $table->string('created_name', 255)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->integer('updated_id')->nullable();
            $table->string('updated_name', 255)->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users_columns_selects');
    }
}
