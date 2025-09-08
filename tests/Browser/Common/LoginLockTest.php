<?php

namespace Tests\Browser\Common;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

use App\Models\Common\Frame;
use App\Models\Core\Dusks;

use App\Enums\PluginName;

/**
 * ログイン・ロックテスト
 *
 * @see https://github.com/opensource-workshop/connect-cms/wiki/Dusk#テスト実行 [How to test]
 */
class LoginLockTest extends DuskTestCase
{
    /**
     * テストする関数の制御
     *
     * @group user
     * @see https://readouble.com/laravel/6.x/ja/dusk.html#running-tests
     */
    public function testInvoke()
    {
        $this->loginLockTest();
    }

    /**
     * ログイン・ロック
     */
    private function loginLockTest()
    {
        $this->assertTrue(true);

        // ログイン画面
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->clickLink('ログイン')
                    ->type('#userid', 'admin')
                    ->type('#password', 'error001')
                    ->click('@login-button')
                    ->waitForText('ログインできません')
                    ->type('#userid', 'admin')
                    ->type('#password', 'error002')
                    ->click('@login-button')
                    ->waitForText('ログインできません')
                    ->type('#userid', 'admin')
                    ->type('#password', 'error003')
                    ->click('@login-button')
                    ->waitForText('ログインできません')
                    ->type('#userid', 'admin')
                    ->type('#password', 'error004')
                    ->click('@login-button')
                    ->waitForText('ログインできません')
                    ->type('#userid', 'admin')
                    ->type('#password', 'error005')
                    ->click('@login-button')
                    ->waitForText('ログインできません')
                    ->type('#userid', 'admin')
                    ->type('#password', 'error006')
                    ->click('@login-button')
                    ->waitForText('ログインできません')
                    ->screenshot('common/index/lock/images/loginLock');
        });

        // マニュアル用データ出力
        $dusk = Dusks::putManualData(
            ['html_path' => 'common/index/lock/index.html'],
            ['category' => 'common',
             'sort' => 2,
             'plugin_name' => 'index',
             'plugin_title' => 'ログイン関係',
             'plugin_desc' => 'ログイン・ログアウトなどログインに関係する操作方法について説明します。',
             'method_name' => 'lock',
             'method_title' => 'ログイン・ロック',
             'method_desc' => '一定回数ログインが失敗した場合にロックします。',
             'method_detail' => '',
             'html_path' => 'common/index/lock/index.html',
             'img_args' => '[
                 {"path": "common/index/lock/images/loginLock",
                  "name": "ロックされた状態",
                  "comment": "<ul class=\"mb-0\"><li>ログインに5回 失敗した場合に、該当のIPアドレスを最初の失敗から60秒ロックします。</li></ul>"
                 }
             ]',
             'level' => 'basic',
             'test_result' => 'OK']
        );
    }
}
