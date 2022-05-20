<?php

namespace Tests\Browser\Manage;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * > tests\bin\connect-cms-test.bat
 */
class PluginManageTest extends DuskTestCase
{
    /**
     * テスト前共通処理
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        // bugfix: APP_DEBUG=trueだと,phpdebugbar-header とボタンが被って、ボタンが押せずにテストエラーになるため、phpdebugbarを閉じる
        $this->closePhpdebugar();
    }

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
        $this->update();
    }

    /**
     * index の表示
     */
    private function index()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/manage/plugin')
                    ->type('plugins[1][display_sequence]', '1')
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('manage/plugin/index/images/index');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "manage/plugin/index/images/index",
             "name": "プラグイン管理",
             "comment": "<ul class=\"mb-0\"><li>プラグイン管理で使用するプラグインをチェックし、プラグイン追加ダイアログで表示される順番を設定します。</li></ul>"
            }
        ]', null, 3);
    }

    /**
     * 登録処理
     */
    private function update()
    {
        $this->browse(function (Browser $browser) {
            $browser->press('更新')
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('manage/plugin/update/images/update');
        });
    }
}
