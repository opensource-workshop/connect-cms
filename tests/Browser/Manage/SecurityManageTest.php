<?php

namespace Tests\Browser\Manage;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * > tests\bin\connect-cms-test.bat
 */
class SecurityManageTest extends DuskTestCase
{
    /**
     * テストする関数の制御
     *
     * @group manage
     * @see https://readouble.com/laravel/6.x/ja/dusk.html#running-tests `php artisan dusk --group=manage`
     */
    public function testInvoke()
    {
        $this->login(1);
        $this->index();
        $this->security();
        $this->saveLoginPermit();
        $this->purifier();
        $this->savePurifier();
    }

    /**
     * index の表示
     */
    private function index()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/manage/security')
                    ->assertTitleContains('Laravel');
            $this->screenshot($browser);
        });
    }

    /**
     * ログイン制限画面
     */
    private function security()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/manage/security')
                    ->type('add_apply_sequence', '1')
                    ->type('add_ip_address', '*')
                    ->select('add_role', 'role_reporter')
                    ->click('#label_add_reject_on')
                    ->assertTitleContains('Laravel');
            $this->screenshot($browser);
        });
    }

    /**
     * ログイン制限登録処理
     */
    private function saveLoginPermit()
    {
        $this->browse(function (Browser $browser) {
            $browser->press('変更')
                    ->assertTitleContains('Laravel');
            $this->screenshot($browser);
        });
    }

    /**
     * HTML記述制限画面
     */
    private function purifier()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/manage/security/purifier')
                    ->click('#label_role_approval_0')
                    ->check('confirm_purifier')
                    ->assertTitleContains('Laravel');
            $this->screenshot($browser);
        });
    }

    /**
     * HTML記述制限登録処理
     */
    private function savePurifier()
    {
        $this->browse(function (Browser $browser) {
            $browser->press('登録')
                    ->assertTitleContains('Laravel');
            $this->screenshot($browser);
        });
    }
}
