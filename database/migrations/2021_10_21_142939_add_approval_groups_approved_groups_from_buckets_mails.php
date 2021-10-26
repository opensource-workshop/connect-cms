<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddApprovalGroupsApprovedGroupsFromBucketsMails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('buckets_mails', function (Blueprint $table) {
            $table->text('approval_groups')->nullable()->comment('承認通知-送信グループ')->after('approval_addresses');
            $table->text('approved_groups')->nullable()->comment('承認済み通知-送信グループ')->after('approved_addresses');
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
            $table->dropColumn('approval_groups');
            $table->dropColumn('approved_groups');
        });
    }
}
