<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSpamListsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('spam_lists', function (Blueprint $table) {
            $table->increments('id');
            $table->string('target_plugin_name', 255)->comment('対象プラグイン名（forms等）');
            $table->integer('target_id')->nullable()->comment('対象ID（フォーム毎の場合はforms_id、全体の場合はnull）');
            $table->string('block_type', 50)->comment('ブロック種別: email, domain, ip_address');
            $table->string('block_value', 255)->comment('ブロック対象の値');
            $table->text('memo')->nullable()->comment('メモ');
            $table->integer('created_id')->nullable();
            $table->string('created_name', 255)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->integer('updated_id')->nullable();
            $table->string('updated_name', 255)->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();

            $table->index(['target_plugin_name', 'target_id', 'block_type'], 'spam_lists_target_index');
            $table->index(['block_type', 'block_value'], 'spam_lists_block_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('spam_lists');
    }
}
