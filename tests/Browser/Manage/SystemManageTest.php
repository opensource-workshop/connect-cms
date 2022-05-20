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
        $this->log();
        $this->updateLog();
        $this->server();
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
     * エラーログ設定の表示
     */
    private function log()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/manage/system/log')
                    ->type('log_filename', 'debug_log')
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('manage/system/log/images/log');
        });

        // マニュアル用データ出力
        $this->putManualData('manage/system/log/images/log', null, 3);
    }

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
        $this->putManualData('manage/system/server/images/server', null, 3);
    }
}
