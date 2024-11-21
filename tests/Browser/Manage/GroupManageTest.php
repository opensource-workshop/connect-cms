<?php

namespace Tests\Browser\Manage;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * > tests\bin\connect-cms-test.bat
 */
class GroupManageTest extends DuskTestCase
{
    /**
     * テストする関数の制御
     *
     * @group manage
     * @see https://readouble.com/laravel/6.x/ja/dusk.html#running-tests
     */
    public function testInvoke()
    {
        $this->init();
        $this->login(1);
        $this->index();
        $this->edit('テスト一般');
        $this->update();
    }

    /**
     * 初期処理
     */
    private function init()
    {
        // 最初にマニュアルの順番確定用にメソッドを指定する。（edit2はユーザ設定後の画面）
        $this->reserveManual('index', 'edit', 'edit2');
    }

    /**
     * index の表示
     */
    private function index()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/manage/group')
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('manage/group/index/images/index');
        });

        // マニュアル用データ出力(ユーザ登録後に出力)
    }

    /**
     * グループ登録画面
     */
    private function edit($name)
    {
        $this->browse(function (Browser $browser) use ($name) {
            $browser->visit('/manage/group/edit')
                    ->type('name', $name)
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('manage/group/edit/images/edit');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "manage/group/edit/images/edit",
             "name": "グループ登録",
             "comment": "<ul class=\"mb-0\"><li>グループ名と表示順を指定してグループを登録できます。</li></ul>"
            }
        ]', null, 3);
    }

    /**
     * グループ登録処理
     */
    private function update()
    {
        $this->browse(function (Browser $browser) {
            $browser->press('グループ登録')
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('manage/group/update/images/update');
        });
    }
}
