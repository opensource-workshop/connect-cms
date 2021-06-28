<?php

namespace Tests\Browser\Core;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

use App\Models\Core\Configs;
use App\Models\Common\Page;

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
        $this->pluginAddModal();

        // ログアウト
        $this->browse(function (Browser $browser) {
            $browser->logout();
        });

        $this->browse(function (Browser $browser) {
            $browser->visit('/plugin/contents/frame_setting/1/1#frame-1')
                    ->assertSee('403 Forbidden');
            parent::screenshot($browser);
        });
    }

    /**
     * プラグイン追加
     */
    private function pluginAddModal()
    {
        $this->browse(function (Browser $browser) {
            // 管理機能からプラグイン追加で固定記事を追加する。
            $browser->visit('/')
                    ->clickLink('管理機能')
                    ->assertTitleContains('Connect-CMS');
            $this->screenshot($browser);

            $browser->clickLink('プラグイン追加')
                    ->assertTitleContains('Connect-CMS');

            // 早すぎると、プラグイン追加ダイアログが表示しきれないので、1秒待つ。
            $browser->pause(1000);
            $this->screenshot($browser);

            $browser->select('add_plugin', 'contents')
                    ->assertTitleContains('Connect-CMS');
            $this->screenshot($browser);
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

        // 403
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
