<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateBucketNameFromWhatsnewNameBucketsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // bugfix:新着では「無題」でバケット名を登録して更新していない不具合があったため、バケット名を新着設定名で更新するパッチ
        DB::statement('UPDATE buckets, whatsnews SET buckets.bucket_name = whatsnews.whatsnew_name WHERE buckets.id = whatsnews.bucket_id');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
