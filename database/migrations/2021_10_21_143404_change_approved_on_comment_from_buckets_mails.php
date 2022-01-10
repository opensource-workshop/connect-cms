<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeApprovedOnCommentFromBucketsMails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('buckets_mails', function (Blueprint $table) {
            $table->integer('approved_on')->default('0')->comment('承認済み通知-on')->change();
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
            $table->integer('approved_on')->default('0')->comment('承認通知-on')->change();
        });
    }
}
