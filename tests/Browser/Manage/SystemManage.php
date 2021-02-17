<?php

namespace Tests\Browser\Manage;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * > tests\bin\connect-cms-test.bat
 */
class SystemManage extends DuskTestCase
{
    /**
     * テストする関数の制御
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
                    ->assertTitleContains('Laravel');
            $this->screenshot($browser);
        });
    }

    /**
     * デバックモード ON
     */
    private function updateDebugmodeOn()
    {
        $this->browse(function (Browser $browser) {
            $browser->press('デバックモードをOn にする。')
                    ->assertTitleContains('Laravel');
            $this->screenshot($browser);
        });
    }

    /**
     * デバックモード OFF
     */
    private function updateDebugmodeOff()
    {
        $this->browse(function (Browser $browser) {
            $browser->press('デバックモードをOff にする。')
                    ->assertTitleContains('Laravel');
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
                    ->assertTitleContains('Laravel');
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
                    ->assertTitleContains('Laravel');
            $this->screenshot($browser);
        });
    }
}
