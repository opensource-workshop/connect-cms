<?php

namespace Tests\Browser\Core;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

use App\Models\Common\Page;

/**
 * 閲覧パスワード付ページテスト
 */
class PagePasswordTest extends DuskTestCase
{
    /**
     * テスト前共通処理
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        // 閲覧パスワード付ページの作成
        $this->createPasswordPage();
    }

    /**
     * 閲覧パスワード付ページの作成
     */
    private function createPasswordPage()
    {
        // --- 更新
        // パスワード付ページ登録更新
        $page = Page::updateOrCreate(
            ['permanent_link' => '/password'],
            [
                'page_name' => 'パスワード',
                'permanent_link' => '/password',
                'password' => 'pass',
            ]
        );
    }

    /**
     * 閲覧パスワードページ 表示テスト
     *
     * @return void
     *
     * @group core
     * @see https://readouble.com/laravel/6.x/ja/dusk.html#running-tests
     */
    public function testPagePasswordShow()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/password')
                ->assertTitleContains('Connect-CMS');
            parent::screenshot($browser);
        });
    }

    /**
     * 閲覧パスワードページ パスワード入力でNGテスト
     *
     * @return void
     *
     * @group core
     */
    public function testPagePasswordInputNg()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/password')
                ->type('password', "xxx")
                ->press('ページ閲覧')
                ->assertSee('パスワードが異なります');
            parent::screenshot($browser);
        });
    }

    /**
     * 閲覧パスワードページ パスワード入力でOKテスト
     *
     * @return void
     *
     * @group core
     */
    public function testPagePasswordInputOk()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/password')
                ->type('password', "pass")
                ->press('ページ閲覧')
                ->assertDontSee('パスワードが必要');    // 閲覧パスワードページの文字列が含まれていない事を確認
            parent::screenshot($browser);
        });
    }
}
