<?php

namespace Tests\Browser\User;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

use App\Enums\PluginName;

/**
 * ブログテスト
 *
 * @see https://github.com/opensource-workshop/connect-cms/wiki/Dusk#テスト実行 [How to test]
 */
class BlogTest extends DuskTestCase
{
    /**
     * テスト前共通処理
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        // APP_DEBUG=trueだと,phpdebugbar-header とボタンが被って、ボタンが押せずにテストエラーになるため、phpdebugbarを閉じる
        $this->closePhpdebugar();
    }

    /**
     * ブログテスト
     *
     * @group user
     * @see https://readouble.com/laravel/6.x/ja/dusk.html#running-tests
     */
    public function testBlog()
    {
        $this->login(1);

        // 固定記事をプラグイン追加
        $this->addPluginModal(PluginName::getPluginName(PluginName::blogs));

        $this->blogSetting();
        $this->blogPostCreate();
    }

    /**
     * ブログ設定
     */
    private function blogSetting()
    {
        $this->browse(function (Browser $browser) {

            // プラグインの（右上）歯車マーク押下
            // <a href="http://localhost/plugin/blogs/editBuckets/1/1#frame-1" title="ブログ設定"><small><i class="fas fa-cog bg-default cc-font-color"></i></small></a>
            // @see http://semooh.jp/jquery/api/selectors/ Selectors - jQuery 日本語リファレンス
            $browser->visit('/')
                ->click('[title="ブログ設定"]')
                ->assertSee('新規作成してください。');
            $this->screenshot($browser);

            // 現在のURL取得
            // $url = $browser->driver->getCurrentURL();

            // 新規作成リンククリック
            // <a href="http://localhost/plugin/blogs/createBuckets/1/1#frame-1" class="nav-link">新規作成</a>
            // $browser->visit('/plugin/blogs/createBuckets/1/1')
            // $browser->visit($url)
            $browser->clickLink('新規作成')
                ->assertSee('登録確定');
            $this->screenshot($browser);

            // 入力
            $browser->type('blog_name', 'ブログテスト')
                ->type('view_count', '10')
                ->assertSee('登録確定');
            $this->screenshot($browser);

            // 登録ボタン押下
            $browser->press('登録確定')
                    ->assertTitleContains('Connect-CMS');
            $this->screenshot($browser);
        });
    }

    /**
     * ブログの記事作成
     */
    private function blogPostCreate()
    {
        $this->browse(function (Browser $browser) {
            // 新規登録ボタン押下
            $browser->visit('/')
                ->press('新規登録')
                ->assertSee('登録確定');
            $this->screenshot($browser);

            // 入力
            $browser->type('post_title', 'テスト投稿')
                ->assertSee('登録確定');

            // ウィジウィグ入力
            // $browser->driver->executeScript('tinymce.activeEditor.setContent(\'<h1>Test Description</h1>\')');
            $browser->driver->executeScript('tinyMCE.get(0).setContent(\'<h1>Test Description</h1>\')');
            $browser->driver->executeScript('tinyMCE.get(1).setContent(\'<h1>Test Description2</h1>\')');
            $this->screenshot($browser);

            // 登録確定ボタン押下
            $browser->press('登録確定')
                    ->assertTitleContains('Connect-CMS');
            $this->screenshot($browser);
        });
    }
}
