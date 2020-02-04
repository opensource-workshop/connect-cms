<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * コードテーブル作成
 *
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コード管理
 * @package Migration
 */
class CreateCodes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('codes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('plugin_name', 255)->nullable();
            $table->integer('buckets_id')->nullable();
            $table->string('prefix', 255)->nullable();

            $table->string('type_name', 255)->nullable();
            $table->string('type_code1', 255)->nullable();
            $table->string('type_code2', 255)->nullable();
            $table->string('type_code3', 255)->nullable();
            $table->string('type_code4', 255)->nullable();
            $table->string('type_code5', 255)->nullable();
            $table->string('code', 255)->nullable();
            $table->string('value', 255)->nullable();
            // $table->string('additional1', 255)->nullable();
            // $table->string('additional2', 255)->nullable();
            // $table->string('additional3', 255)->nullable();
            // $table->string('additional4', 255)->nullable();
            // $table->string('additional5', 255)->nullable();
            $table->integer('display_sequence')->nullable();
            $table->integer('created_id')->nullable();
            $table->string('created_name', 255)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->integer('updated_id')->nullable();
            $table->string('updated_name', 255)->nullable();
            $table->timestamp('updated_at')->nullable();
            // $table->integer('deleted_id')->nullable();
            // $table->string('deleted_name', 255)->nullable();
            // $table->timestamp('deleted_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('codes');
    }
}
