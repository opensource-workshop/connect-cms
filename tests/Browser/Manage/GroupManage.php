<?php

namespace Tests\Browser\Manage;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * > tests\bin\connect-cms-test.bat
 */
class GroupManage extends DuskTestCase
{
    /**
     * テストする関数の制御
     */
    public function testInvoke()
    {
        $this->login(1);
        $this->edit('テスト一般');
        $this->update();
        $this->index();
    }

    /**
     * index の表示
     */
    private function index()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/manage/group')
                    ->assertTitleContains('Laravel');
            $this->screenshot($browser);
        });
    }

    /**
     * グループ登録画面
     */
    private function edit($name)
    {
        $this->browse(function (Browser $browser) use ($name) {
            $browser->visit('/manage/group/edit')
                    ->type('name', $name)
                    ->assertTitleContains('Laravel');
            $this->screenshot($browser);
        });
    }

    /**
     * グループ登録処理
     */
    private function update()
    {
        $this->browse(function (Browser $browser) {
            $browser->press('グループ変更')
                    ->assertTitleContains('Laravel');
            $this->screenshot($browser);
        });
    }
}
