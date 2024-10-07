<?php

namespace Tests\Unit\Mail;

use App\Mail\ConnectMail;
// use App\Models\Core\Configs;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConnectMailTest extends TestCase
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
     * ConnectMailテスト
     */
    public function testConnectMail(): void
    {
        // *** 前準備
        // メール配信設定を使う
        // Configs::factory()->create([
        //     'name' => 'use_unsubscribe',
        //     'value' => '1',
        //     'category' => 'general',
        // ]);

        $subject = 'テスト件名';
        $body = 'テスト本文';
        $mail_options = ['subject' => $subject, 'template' => 'mail.send'];
        $mail_datas = ['content' => $body];

        // *** 実行
        $mailable = new ConnectMail($mail_options, $mail_datas);

        // *** チェック
        // 本文はあっているか
        $mailable->assertSeeInHtml($body);

        // メールヘッダをチェックしたかったが、見つからなかった
    }
}
