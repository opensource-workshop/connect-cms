<?php

namespace Tests\Browser\Manage;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * > tests\bin\connect-cms-test.bat
 */
class AuthManageTest extends DuskTestCase
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
        $this->auth();
        $this->update();
        $this->netcommons2();
        $this->netcommons2Update();
    }

    /**
     * 認証設定の表示
     */
    private function index()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/manage/auth')
                    ->assertTitleContains('Laravel');
            $this->screenshot($browser);
        });
    }

    /**
     * 認証設定の入力
     */
    private function auth()
    {
        $this->browse(function (Browser $browser) {
            $browser->click('#label_use_auth_method_1')
                    ->check('confirm_auth')
                    ->assertTitleContains('Laravel');
            $this->screenshot($browser);
        });
    }

    /**
     * 認証設定の更新
     */
    private function update()
    {
        $this->browse(function (Browser $browser) {
            $browser->press('更新')
                    ->assertTitleContains('Laravel');
            $this->screenshot($browser);
        });
    }

    /**
     * NetCommons2認証の表示
     */
    private function netcommons2()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/manage/auth/netcommons2');
            $this->screenshot($browser);

            $browser->type('auth_netcomons2_site_url', 'http://nc2.localhost')
                    ->type('auth_netcomons2_site_key', 'key_1234')
                    ->type('auth_netcomons2_salt', 'salt_1234')
                    ->type('auth_netcomons2_add_role', 'original_role:student')
                    ->type('auth_netcomons2_admin_password', 'admin_password_1234')
                    ->assertTitleContains('Laravel');
            $this->screenshot($browser);
        });
    }

    /**
     * NetCommons2認証の更新
     */
    private function netcommons2Update()
    {
        $this->browse(function (Browser $browser) {
            $browser->press('更新')
                    ->assertTitleContains('Laravel');
            $this->screenshot($browser);
        });
    }
}
