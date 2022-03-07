<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNoticeEveryoneFromBucketsMails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('buckets_mails', function (Blueprint $table) {
            $table->integer('notice_everyone')->default('0')->comment('投稿通知-全ユーザ通知')->after('notice_addresses');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('buckets_mails', function (Blueprint $table) {
            $table->dropColumn('notice_everyone');
        });
    }
}
