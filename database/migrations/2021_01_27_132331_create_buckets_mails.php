<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBucketsMails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('buckets_mails', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('buckets_id')->comment('バケツID');
            $table->integer('timing')->default('0')->comment('タイミング');

            $table->integer('notice_on')->default('0')->comment('投稿通知-on');
            $table->integer('notice_create')->default('0')->comment('投稿通知-登録時');
            $table->integer('notice_update')->default('0')->comment('投稿通知-変更時');
            $table->integer('notice_delete')->default('0')->comment('投稿通知-削除時');
            $table->text('notice_addresses')->nullable()->comment('投稿通知-送信アドレス');
            $table->text('notice_groups')->nullable()->comment('投稿通知-送信グループ');
            $table->text('notice_roles')->nullable()->comment('投稿通知-送信権限');
            $table->text('notice_subject')->nullable()->comment('投稿通知-件名');
            $table->text('notice_body')->nullable()->comment('投稿通知-本文');

            $table->integer('relate_on')->default('0')->comment('関連記事通知-on');
            $table->text('relate_subject')->nullable()->comment('関連記事通知-件名');
            $table->text('relate_body')->nullable()->comment('関連記事通知-本文');

            $table->integer('approval_on')->default('0')->comment('承認通知-on');
            $table->text('approval_addresses')->nullable()->comment('承認通知-送信アドレス');
            $table->text('approval_subject')->nullable()->comment('承認通知-件名');
            $table->text('approval_body')->nullable()->comment('承認通知-本文');

            $table->integer('approved_on')->default('0')->comment('承認通知-on');
            $table->integer('approved_author')->default('0')->comment('投稿者へ通知する');
            $table->text('approved_addresses')->nullable()->comment('承認済み通知-送信アドレス');
            $table->text('approved_subject')->nullable()->comment('承認済み通知-件名');
            $table->text('approved_body')->nullable()->comment('承認済み通知-本文');

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
        Schema::dropIfExists('buckets_mails');
    }
}
