<?php

namespace Tests\Unit\Jobs;

use App\Enums\PluginName;
use App\Jobs\ConnectMailJob;
use App\Mail\ConnectMail;
use App\Models\Core\Configs;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ConnectMailJobTest extends TestCase
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

        $subject = 'テスト件名';
        $body = 'テスト本文';
        $emails = ['test@example.com'];
        $mail_options = ['subject' => $subject, 'template' => 'mail.send'];
        $plugin_name = PluginName::getPluginName(PluginName::blogs);

        // *** 実行
        $job = new ConnectMailJob($emails, $mail_options, $body, $plugin_name);
        $job->handle();

        // *** チェック
        // mailableが送られたことをアサート & メール送信先Toはあってるか
        Mail::assertSent(ConnectMail::class, function ($mail) use ($emails) {
            return $mail->hasTo($emails[0]);
            // $mail->subject = nullだった。テストだとそういうものなのかな？
        });

        // アプリログ確認, 送信ログが保存されているか
        $this->assertDatabaseHas('app_logs', [
            'plugin_name' => $plugin_name,
            'type' => 'SendMail',
        ]);
    }
}
