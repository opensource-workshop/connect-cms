<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBucketsRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('buckets_roles', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('buckets_id')->nullable();
            $table->string('role', 255)->nullable();
            $table->integer('post_flag')->default(0);
            $table->integer('approval_flag')->default(0);
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
        Schema::dropIfExists('buckets_roles');
    }
}
