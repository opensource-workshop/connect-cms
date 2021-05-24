<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * 注釈テーブル作成
 *
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コード管理
 * @package Migration
 */
class CreateCodesHelpMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('codes_help_messages', function (Blueprint $table) {
            $table->increments('id');
            $table->string('alias_key', 191)->comment('注釈キー');      // 必須
            $table->string('name', 255)->comment('注釈名');             // 必須

            $table->string('codes_help_messages_alias_key_help_message', 255)->nullable();
            $table->string('plugin_name_help_message', 255)->nullable();
            $table->string('buckets_id_help_message', 255)->nullable();
            $table->string('prefix_help_message', 255)->nullable();

            $table->string('type_name_help_message', 255)->nullable();
            $table->string('type_code1_help_message', 255)->nullable();
            $table->string('type_code2_help_message', 255)->nullable();
            $table->string('type_code3_help_message', 255)->nullable();
            $table->string('type_code4_help_message', 255)->nullable();
            $table->string('type_code5_help_message', 255)->nullable();
            $table->string('code_help_message', 255)->nullable();
            $table->string('value_help_message', 255)->nullable();
            $table->string('additional1_help_message', 255)->nullable();
            $table->string('additional2_help_message', 255)->nullable();
            $table->string('additional3_help_message', 255)->nullable();
            $table->string('additional4_help_message', 255)->nullable();
            $table->string('additional5_help_message', 255)->nullable();
            $table->string('display_sequence_help_message', 255)->nullable();
            $table->integer('display_sequence')->comment('表示順');

            $table->integer('created_id')->nullable();
            $table->string('created_name', 255)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->integer('updated_id')->nullable();
            $table->string('updated_name', 255)->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->integer('deleted_id')->nullable();
            $table->string('deleted_name', 255)->nullable();
            $table->timestamp('deleted_at')->nullable();

            $table->index(['alias_key'], 'alias_key_at_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('codes_help_messages');
    }
}
