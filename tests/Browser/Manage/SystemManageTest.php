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
    }

    /**
     * デバックモードの表示
     */
    private function index()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/manage/system')
                    ->assertTitleContains('Connect-CMS');
            $this->screenshot($browser);
        });
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
                    ->assertTitleContains('Connect-CMS');
            $this->screenshot($browser);
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
                    ->assertTitleContains('Connect-CMS');
            $this->screenshot($browser);
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
                    ->assertTitleContains('Connect-CMS');
            $this->screenshot($browser);
        });
    }

    /**
     * エラーログ設定の更新
     */
    private function updateLog()
    {
        $this->browse(function (Browser $browser) {
            $browser->press('更新')
                    ->assertTitleContains('Connect-CMS');
            $this->screenshot($browser);
        });
    }
}
