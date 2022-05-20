<?php

namespace Tests\Browser\Manage;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class NumberManageTest extends DuskTestCase
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
     * テーマ一覧
     */
    private function index()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            $browser->visit('/manage/number')
                    ->assertTitle('Connect-CMS')
                    ->screenshot('manage/number/index/images/index');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "manage/number/index/images/index",
             "name": "連番設定",
             "comment": "<ul class=\"mb-0\"><li>連番とは、問い合わせフォームなどの連番のことです。</li><li>プラグイン中で採番した番号を見ることができます。</li><li>連番をクリアすることもできます。</li></ul>"
            }
        ]', null, 3);
    }
}
