<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateConfigsLoginPermitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('configs_login_permits', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('apply_sequence')->default('0');
            $table->string('ip_address');
            $table->string('role')->nullable();
            $table->integer('reject')->default('0');
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
        Schema::dropIfExists('configs_login_permits');
    }
}
