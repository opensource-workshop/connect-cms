<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            //$table->bigIncrements('id');                         // change laravel6. create migration 2021_01_27_154006_change_users_table_in_laravel6.
            $table->increments('id');
            $table->string('name');
            //$table->string('email')->unique();
            $table->string('email')->unique()->nullable();
            //$table->timestamp('email_verified_at')->nullable();  // change laravel6. create migration 2021_01_27_154006_change_users_table_in_laravel6.
            $table->string('password');
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
