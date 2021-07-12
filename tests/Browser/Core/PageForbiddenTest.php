<?php

namespace Tests\Browser\Core;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

use App\Models\Core\Configs;
use App\Models\Common\Page;

use App\Enums\PluginName;

class PageForbiddenTest extends DuskTestCase
{
    /**
     * 管理画面表示で権限なし(403)テスト
     *
     * @return void
     *
     * @group core
     * @see https://readouble.com/laravel/6.x/ja/dusk.html#running-tests
     */
    public function testPageForbiddenManage()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/manage')
                    ->assertSee('403 Forbidden');
            parent::screenshot($browser);
        });
    }

    /**
     * 一般プラグインの設定画面表示で権限なし(403)テスト
     *
     * @return void
     *
     * @group core
     */
    public function testPageForbiddenPlugin()
    {
        $this->login(1);

        // 固定記事をプラグイン追加
        $this->addPluginModal(PluginName::getPluginName(PluginName::contents));

        // ログアウト
        $this->logout();

        $this->browse(function (Browser $browser) {
            $browser->visit('/plugin/contents/frame_setting/1/1#frame-1')
                    ->assertSee('403 Forbidden');
            parent::screenshot($browser);
        });
    }

    /**
     * 403ページ設定後に、ログイン必要ページを未ログイン表示で権限なし(403)テスト
     *
     * @return void
     *
     * @group core
     */
    public function testPageForbiddenNeedLogin()
    {
        // --- 更新
        // 403ページ登録更新
        $page = Page::updateOrCreate(
            ['permanent_link' => '/403'],
            [
                'page_name' => '403',
                'permanent_link' => '/403',
            ]
        );
        // ログイン必要ページ登録更新
        $page = Page::updateOrCreate(
            ['permanent_link' => '/need-login'],
            [
                'page_name' => 'ログイン必要ページ',
                'permanent_link' => '/need-login',
                'membership_flag' => 2,     // 2:ログインユーザ全員参加
            ]
        );

        // 403ページ設定
        $configs = Configs::updateOrCreate(
            ['name'     => 'page_permanent_link_403'],
            ['category' => 'page_error',
             'value'    => '/403']
        );

        $this->browse(function (Browser $browser) {
            $browser->visit('/need-login')
                ->assertDontSee('403 Forbidden');   // 403ページは作ったけど、プラグインを配置していないので「403 Forbidden」表示がない事をチェック
            parent::screenshot($browser);
        });
    }
}
