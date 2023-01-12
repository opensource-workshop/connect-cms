<?php

namespace Tests\Browser\Manage;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class MigrationManageTest extends DuskTestCase
{
    /**
     * テストする関数の制御
     *
     * @group manage
     * @see https://readouble.com/laravel/6.x/ja/dusk.html#running-tests
     */
    public function testInvoke()
    {
        $this->login(1); // user id = 1(admin)でログイン
        $this->index();
    }

    /**
     * ログ一覧
     */
    private function index()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            $browser->visit('/manage/migration')
                    ->assertTitle('Connect-CMS')
                    ->screenshot('manage/migration/index/images/index');
        });

        $this->putManualData('[
            {"path": "manage/migration/index/images/index",
             "name": "NetCommons2移行",
             "comment": "<ul class=\"mb-0\"><li>NetCommons2のフルバックアップはNetCommons2の管理画面で作成、ダウンロードすることができます。</li><li>NetCommons2のフルバックアップファイルのサイズが大きい場合は、アップロードで失敗したり、処理時間が長くてエラーになったりすることがあります。<br />そのような場合は、ApacheやPHPの設定を見直してください。</li></ul>"
            }
        ]', null, 3);
    }
}
