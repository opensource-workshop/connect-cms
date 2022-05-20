<?php

namespace Tests\Browser\Manage;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class HolidayManageTest extends DuskTestCase
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
        $this->edit();
        $this->overrideEdit();
    }

    /**
     * ログ一覧
     */
    private function index()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            $browser->visit('/manage/holiday')
                    ->assertTitle('Connect-CMS')
                    ->screenshot('manage/holiday/index/images/index');
        });

        $this->putManualData('[
            {"path": "manage/holiday/index/images/index",
             "name": "祝日一覧",
             "comment": "<ul class=\"mb-0\"><li>表示年で必要な年を選択してください。</li></ul>"
            }
        ]', null, 3);
    }

    /**
     * 祝日登録
     */
    private function edit()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            $browser->visit('/manage/holiday/edit')
                    ->assertTitle('Connect-CMS')
                    ->screenshot('manage/holiday/edit/images/edit');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "manage/holiday/edit/images/edit",
             "name": "祝日登録",
             "comment": "<ul class=\"mb-0\"><li>新たな祝日を登録できます。</li></ul>"
            }
        ]', null, 3);
    }

    /**
     * 祝日上書き
     */
    private function overrideEdit()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            $browser->visit('/manage/holiday/overrideEdit/2022-01-10')
                    ->assertTitle('Connect-CMS')
                    ->screenshot('manage/holiday/overrideEdit/images/overrideEdit');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "manage/holiday/overrideEdit/images/overrideEdit",
             "name": "祝日上書き",
             "comment": "<ul class=\"mb-0\"><li>既存の祝日を無効にすることができます。</li></ul>"
            }
        ]', null, 3);
    }
}
