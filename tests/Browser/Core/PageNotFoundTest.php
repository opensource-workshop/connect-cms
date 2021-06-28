<?php

namespace Tests\Browser\Core;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

use App\Models\Core\Configs;
use App\Models\Common\Page;

class PageNotFoundTest extends DuskTestCase
{
    /**
     * ページなし表示のテスト
     *
     * @return void
     *
     * @group core
     * @see https://readouble.com/laravel/6.x/ja/dusk.html#running-tests
     */
    public function testPageNotFound()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/not-found')
                    ->assertSee('404 Not found');
            parent::screenshot($browser);
        });
    }

    /**
     * 404ページ設定後に、ページなしテスト
     *
     * @return void
     *
     * @group core
     */
    public function testPageNotFoundSetting404()
    {
        // --- 更新
        // 404ページ登録更新
        $page = Page::updateOrCreate(
            ['permanent_link' => '/404'],
            [
                'page_name' => '404',
                'permanent_link' => '/404',
            ]
        );

        // 404
        $configs = Configs::updateOrCreate(
            ['name'     => 'page_permanent_link_404'],
            ['category' => 'page_error',
             'value'    => '/404']
        );

        $this->browse(function (Browser $browser) {
            $browser->visit('/not-found')
                ->assertDontSee('404 Not found');   // 404ページは作ったけど、プラグインを配置していないので「404 Not found」表示がない事をチェック
            parent::screenshot($browser);
        });
    }
}
