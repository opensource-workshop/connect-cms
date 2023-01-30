<?php

namespace Tests\Browser\Manage;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * > tests\bin\connect-cms-test.bat
 */
class SystemManageTest extends DuskTestCase
{
    /**
     * テストする関数の制御
     *
     * @group manage
     * @see https://readouble.com/laravel/6.x/ja/dusk.html#running-tests
     */
    public function testInvoke()
    {
        $this->login(1);
        $this->index();
        $this->updateDebugmodeOn();
        $this->updateDebugmodeOff();
        $this->mail();
        $this->mailTest();
        // $this->log();
        $this->server();
        $this->updateLog();
    }

    /**
     * デバックモードの表示
     */
    private function index()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/manage/system')
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('manage/system/index/images/index');
        });

        // デバッグ画面はあらかじめ用意しておいたサンプル画像を使用
        \Storage::disk('screenshot')->put('manage/system/index/images/system_error_example.png', \Storage::disk('manual')->get('copy_data/image/system_error_example.png'));
        \Storage::disk('screenshot')->put('manage/system/index/images/debug.png', \Storage::disk('manual')->get('copy_data/image/debug.png'));

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "manage/system/index/images/index",
             "name": "デバックモード",
             "comment": "<ul class=\"mb-0\"><li>運用環境で.env環境変数のデバックモード（APP_DEBUG=false）をfalseにしている場合でも、このセッションのみデバッグモードをONにして、トラブル調査ができます。</li></ul>"
            },
            {"path": "manage/system/index/images/system_error_example",
             "name": "デバックモードOFF時にシステムエラーが発生した場合",
             "comment": "<ul class=\"mb-0\"><li>フレーム内にエラーが発生したことを示すメッセージのみ表示など、できるだけ運用への影響を最小限にする方法がとられています。</li></ul>"
            },
            {"path": "manage/system/index/images/debug",
             "name": "セッションのデバックモードをONにしてシステムエラーが発生した場合",
             "comment": "<ul class=\"mb-0\"><li>上記のような場合に、セッション内のデバックモードをONにすることで、トラブルの調査が行えます。</li></ul>"
            }
        ]', null, 3);
    }

    /**
     * デバックモード ON
     */
    private function updateDebugmodeOn()
    {
        $this->browse(function (Browser $browser) {
            // bugfix: APP_DEBUG=trueにすると初期で「デバックモードをOff にする。」になっており、テストエラーになるので修正
            // $browser->press('デバックモードをOn にする。')
            $browser->click("button[type='submit']")
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('manage/system/updateDebugmode/images/updateDebugmodeOn');
        });
    }

    /**
     * デバックモード OFF
     */
    private function updateDebugmodeOff()
    {
        $this->browse(function (Browser $browser) {
            // $browser->press('デバックモードをOff にする。')
            $browser->click("button[type='submit']")
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('manage/system/updateDebugmode/images/updateDebugmodeOff');
        });
    }

    /**
     * メール設定の表示
     */
    private function mail()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/manage/system/mail')
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('manage/system/mail/images/mail');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "manage/system/mail/images/mail",
             "name": "メール設定",
             "comment": "<ul class=\"mb-0\"><li>Connect-CMSが使用するSMTPメール送信設定を設定できます。</li></ul>"
            }
        ]', null, 3);
    }

    /**
     * メール送信テストの表示
     */
    private function mailTest()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/manage/system/mailTest')
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('manage/system/mailTest/images/mailTest');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "manage/system/mailTest/images/mailTest",
             "name": "メール送信テスト",
             "comment": "<ul class=\"mb-0\"><li>Connect-CMSで設定されたSMTPメール送信設定をテストできます。</li></ul>"
            }
        ]', null, 3);
    }

    /**
     * エラーログ設定の表示
     */
    /*
    private function log()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/manage/system/log')
                    ->type('log_filename', 'debug_log')
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('manage/system/log/images/log');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "manage/system/log/images/log",
             "name": "エラーログ設定",
             "comment": "<ul class=\"mb-0\"><li>エラーログは storage/logs ディレクトリに出力されます。</li><li>システム的なエラーがあった際はPHPのスタックトレース形式のログが出力されます。</li></ul>"
            }
        ]', null, 3);
    }
    */

    /**
     * エラーログ設定の更新
     */
    private function updateLog()
    {
        $this->browse(function (Browser $browser) {
            $browser->press('更新')
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('manage/system/log/images/updateLog');
        });
    }

    /**
     * サーバ設定の表示
     */
    private function server()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/manage/system/server')
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('manage/system/server/images/server');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "manage/system/server/images/server",
             "name": "サーバ設定",
             "comment": "<ul class=\"mb-0\"><li>画像アップロード時にシステムエラーになる場合は、画像の変換処理でメモリが不足している可能性があります。<br />そのような場合は、ここで画像変換時に使用するメモリ上限を設定することで、エラーを回避できる可能性があります。</li></ul>"
            }
        ]', null, 3);
    }
}
