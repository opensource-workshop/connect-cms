<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFirstCommittedAtBbsPosts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bbs_posts', function (Blueprint $table) {
            $table->timestamp('first_committed_at')->nullable()->comment('初回確定日時')->after('thread_updated_at');
        });

        // 初期データ設定：初回確定日時が入っていないと、次回の更新時に新規とみなして、設定によっては登録通知メールが送られるため、初期値を設定
        \DB::statement('UPDATE bbs_posts SET first_committed_at = created_at');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bbs_posts', function (Blueprint $table) {
            $table->dropColumn('first_committed_at');
        });
    }
}
