<?php

namespace Tests\Unit\Jobs;

use App\Enums\NoticeEmbeddedTag;
use App\Enums\PluginName;
use App\Jobs\PostNoticeJob;
use App\Mail\PostNotice;
use App\Models\Common\Buckets;
use App\Models\Common\BucketsMail;
use App\Models\Core\Configs;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PostNoticeJobTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 初期設定
     */
    protected function setUp(): void
    {
        $this->refreshApplication();
        $this->refreshDatabase();

        parent::setUp();
    }

    /**
     * ジョブ実行テスト
     */
    public function testJob(): void
    {
        // *** 前準備
        // キューの実行をsyncに設定
        config(['queue.default' => 'sync']);
        // 実際のメール送信しない
        Mail::fake();

        // アプリログ：メール送信ログを保存する
        Configs::factory()->create([
            'name' => 'save_log_type_sendmail',
            'value' => '1',
            'category' => 'app_log',
        ]);

        $plugin_name = PluginName::getPluginName(PluginName::blogs);
        $bucket = Buckets::factory()->create([
            'plugin_name' => $plugin_name,
        ]);

        $email = 'test@example.com';
        $subject = '【[[site_name]]】通知 [[body]] [[url]] [[delete_comment]]';
        $body = 'テスト本文';

        BucketsMail::factory()->create([
            'buckets_id' => $bucket->id,
            'notice_addresses' => $email,
            'notice_on' => 1,
            'notice_create' => 1,
            'notice_subject' => $subject,
            'notice_body' => $body,
        ]);

        $embedded_tags = [
            NoticeEmbeddedTag::site_name => 'サンプルサイト',
            // NoticeEmbeddedTag::method => NoticeJobType::getDescription($notice_method),
            NoticeEmbeddedTag::title => 'テストタイトル',
            NoticeEmbeddedTag::body => 'HTMLを除いた本文',
            NoticeEmbeddedTag::url => 'http://localhost/plugin/xxxx',
            NoticeEmbeddedTag::delete_comment => '削除時コメント',
            NoticeEmbeddedTag::created_name => '一般',
            // NoticeEmbeddedTag::created_at => $post->created_at,
            NoticeEmbeddedTag::updated_name => 'モデレータ',
            // NoticeEmbeddedTag::updated_at => $post->updated_at,
        ];

        // *** 実行
        $job = new PostNoticeJob($bucket, $embedded_tags);
        $job->handle();

        // *** チェック
        // mailableが送られたことをアサート & メール送信先Toはあってるか
        Mail::assertSent(PostNotice::class, function ($mail) use ($email) {
            return $mail->hasTo($email);
        });

        // アプリログ確認, 送信ログが保存されているか
        $this->assertDatabaseHas('app_logs', [
            'plugin_name' => $plugin_name,
            'type' => 'SendMail',
        ]);
    }
}
