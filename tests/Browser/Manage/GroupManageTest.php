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
        $this->login(1);
        $this->index(); // マニュアル用インデックスデータ作成のため。
        $this->edit('テスト一般');
        $this->update();
        $this->index(); // 中身のある画面で上書き
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

        // マニュアル用データ出力
        $this->putManualData('manage/group/index/images/index');
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
        $this->putManualData('manage/group/edit/images/edit');
    }

    /**
     * グループ登録処理
     */
    private function update()
    {
        $this->browse(function (Browser $browser) {
            $browser->press('グループ変更')
                    ->assertTitleContains('Connect-CMS');
            $this->screenshot($browser);
        });
    }
}
