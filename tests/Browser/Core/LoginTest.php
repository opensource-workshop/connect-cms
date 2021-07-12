<?php

namespace Tests\Browser\Core;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * ログインテスト
 *
 * @see https://github.com/opensource-workshop/connect-cms/wiki/Dusk#テスト実行 [How to test]
 */
class LoginTest extends DuskTestCase
{
    /**
     * ログイン全未入力で失敗
     *
     * @return void
     *
     * @group core
     * @see https://readouble.com/laravel/6.x/ja/dusk.html#running-tests
     */
    public function testLoginEmptyFailure()
    {
        // [debug コンソール]
        // fwrite(STDOUT, __METHOD__ . PHP_EOL);

        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->assertSee('ログイン');
            parent::screenshot($browser);

            $browser->press('ログイン')
                ->assertSee('ログイン');
            parent::screenshot($browser);
        });
    }

    /**
     * いないユーザでログイン失敗
     *
     * @return void
     *
     * @group core
     */
    public function testLoginNotUserFailure()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->assertSee('ログイン');
            parent::screenshot($browser);

            // 入力
            $browser->type('userid', 'not-user')
                ->type('password', 'not-password')
                ->press('ログイン')
                ->assertSee('ログインできません');
            $this->screenshot($browser);
        });
    }

    /**
     * ログイン成功
     *
     * @return void
     *
     * @group core
     */
    public function testLogin()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->assertSee('ログイン');
            parent::screenshot($browser);

            // 入力
            $browser->type('userid', 'admin')
                ->type('password', 'C-admin')
                ->press('ログイン')
                ->assertDontSee('ログイン')     // "ログイン" 文字がない事
                ->assertDontSee('500');        // "500" 文字がない事
            $this->screenshot($browser);
        });
    }
}
