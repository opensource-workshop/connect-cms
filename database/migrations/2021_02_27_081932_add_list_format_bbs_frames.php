<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddListFormatBbsFrames extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bbs_frames', function (Blueprint $table) {
            //
            $table->integer('list_format')->nullable()->comment('一覧での展開方法')->after('thread_sort_flag');
            $table->integer('thread_format')->nullable()->comment('詳細でのスレッド記事の展開方法')->after('list_format');
            $table->integer('list_underline')->nullable()->comment('スレッド記事の下線')->after('thread_format');
            $table->string('thread_caption')->nullable()->comment('スレッド記事枠のタイトル')->after('list_underline');
        });

        // 初期データ設定
        // スレッド記事枠のタイトル：返信一覧
        // 一覧での展開方法：根記事のみ展開（1）
        // 詳細でのスレッド記事の展開方法：すべて閉じておく（2）
        \DB::statement('UPDATE bbs_frames frames SET frames.thread_caption = "返信一覧", frames.list_format = 1, frames.thread_format = 2');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bbs_frames', function (Blueprint $table) {
            //
            $table->dropColumn('list_format');
            $table->dropColumn('thread_format');
            $table->dropColumn('list_underline');
            $table->dropColumn('thread_caption');
        });
    }
}
