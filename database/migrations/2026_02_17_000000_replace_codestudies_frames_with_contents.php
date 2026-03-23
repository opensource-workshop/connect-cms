<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class ReplaceCodestudiesFramesWithContents extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::transaction(function () {
            $frames = DB::table('frames')
                ->select('id', 'page_id', 'frame_title')
                ->where('plugin_name', 'codestudies')
                ->orderBy('id')
                ->get();

            foreach ($frames as $frame) {
                $timestamp = now();
                $bucket_id = DB::table('buckets')->insertGetId([
                    'bucket_name' => $this->getBucketName($frame),
                    'plugin_name' => 'contents',
                    'container_page_id' => $frame->page_id,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ]);

                DB::table('contents')->insert([
                    'bucket_id' => $bucket_id,
                    'content_text' => $this->getNoticeMessage(),
                    'content2_text' => null,
                    'read_more_flag' => 0,
                    'read_more_button' => '続きを読む',
                    'close_more_button' => '閉じる',
                    'status' => 0,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ]);

                DB::table('frames')
                    ->where('id', $frame->id)
                    ->update([
                        'plugin_name' => 'contents',
                        'bucket_id' => $bucket_id,
                        'updated_at' => $timestamp,
                    ]);
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // rollback時に変換前のバケツIDを復元できないため、処理しない。
    }

    /**
     * 固定記事バケツ名
     *
     * @param object $frame
     * @return string
     */
    private function getBucketName($frame)
    {
        if (!empty($frame->frame_title)) {
            return $frame->frame_title;
        }

        return 'コードスタディ廃止のお知らせ';
    }

    /**
     * 廃止メッセージ
     *
     * @return string
     */
    private function getNoticeMessage()
    {
        return '<p>コードスタディプラグインは廃止されました。</p>'
            . '<p>このフレームは固定記事に置き換えています。</p>'
            . '<p>必要な情報がある場合は、サイト管理者にお問い合わせください。</p>';
    }
}
