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
                    ->assertTitleContains('Laravel');
            $this->screenshot($browser);
        });
    }

    /**
     * 登録処理
     */
    private function update()
    {
        $this->browse(function (Browser $browser) {
            $browser->press('更新')
                    ->assertTitleContains('Laravel');
            $this->screenshot($browser);
        });
    }
}
