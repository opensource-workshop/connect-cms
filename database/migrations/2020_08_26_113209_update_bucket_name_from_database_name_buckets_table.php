<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateBucketNameFromDatabaseNameBucketsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // bugfix:データベースでは「無題」でバケット名を登録して更新していない不具合があったため、バケット名をデータベース名で更新するパッチ
        DB::statement('UPDATE buckets, `databases` SET buckets.bucket_name = `databases`.databases_name WHERE buckets.id = `databases`.bucket_id');
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
