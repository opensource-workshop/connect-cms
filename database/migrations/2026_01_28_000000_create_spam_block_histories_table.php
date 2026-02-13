<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * スパムブロック履歴テーブルの作成
 *
 * @author 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category スパム管理
 */
class CreateSpamBlockHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('spam_block_histories', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('spam_list_id')->nullable();
            $table->integer('forms_id')->nullable();
            $table->string('block_type', 50);
            $table->string('block_value', 255);
            $table->string('client_ip', 45)->nullable();
            $table->string('submitted_email', 255)->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index('forms_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('spam_block_histories');
    }
}
