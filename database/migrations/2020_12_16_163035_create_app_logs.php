<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAppLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('app_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('ip_address', 255)->nullable()->comment('IPアドレス');
            $table->string('plugin_name', 255)->nullable()->comment('プラグイン名');
            $table->string('uri')->nullable()->comment('URI');
            $table->string('route_name', 255)->nullable()->comment('Route名');
            $table->string('method', 255)->nullable()->comment('get,post,cron...');
            $table->string('type', 255)->nullable()->comment('種別');
            $table->string('return_code', 255)->nullable()->comment('成否');
            $table->text('value')->nullable()->comment('値など');
            $table->text('userid')->nullable()->comment('ログインID');
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
        Schema::dropIfExists('app_logs');
    }
}
