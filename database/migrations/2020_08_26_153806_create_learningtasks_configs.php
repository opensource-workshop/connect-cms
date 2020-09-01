<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLearningtasksConfigs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('learningtasks_configs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('learningtasks_id')->comment('課題セットID');
            $table->integer('post_id')->comment('課題ID');
            $table->string('type', 255)->nullable()->comment('対象');
            $table->string('task_status', 255)->nullable()->comment('ステータス');
            $table->string('value', 255)->nullable()->comment('値');
            $table->integer('created_id')->nullable();
            $table->string('created_name', 255)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->integer('updated_id')->nullable();
            $table->string('updated_name', 255)->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->integer('deleted_id')->nullable();
            $table->string('deleted_name', 255)->nullable();
            $table->timestamp('deleted_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('learningtasks_configs');
    }
}
