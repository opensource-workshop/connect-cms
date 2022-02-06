<?php

namespace Tests\Browser\User;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

use App\Enums\PluginName;
use App\Models\Common\Frame;

/**
 * ブログテスト
 *
 * @see https://github.com/opensource-workshop/connect-cms/wiki/Dusk#テスト実行 [How to test]
 */
class BlogsPluginTest extends DuskTestCase
{
    /**
     * テスト前共通処理
     *
     * @return void
     */
    //protected function setUp(): void
    //{
    //    parent::setUp();
    //    // APP_DEBUG=trueだと,phpdebugbar-header とボタンが被って、ボタンが押せずにテストエラーになるため、phpdebugbarを閉じる
    //    $this->closePhpdebugar();
    //}

    /**
     * ブログテスト
     *
     * @group user
     * @see https://readouble.com/laravel/6.x/ja/dusk.html#running-tests
     */
    public function testBlog()
    {
        $this->index();
        $this->login(1);

        // プラグインがなければ追加(テストするFrameとページのインスタンス変数への保持も)
        $this->addPluginFirst('menus', '/test/blog', 2);

        $this->createBuckets();
        $this->settingBlogFrame();
    }

    /**
     * インデックス
     */
    private function index()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            $browser->visit('/test/blog')
                    ->assertPathBeginsWith('/')
                    ->screenshot('user/blogs/index/images/index');
        });

        // マニュアル用データ出力
        $this->putManualData('user/blogs/index/images/index');
    }

    /**
     * ブログ
     */
    private function createBuckets()
    {
        if (Frame::where('plugin_name', 'blogs')->first()) {
            // プラグインがなければ追加(テストするFrameとページのインスタンス変数への保持も)
            $this->addPluginFirst('blogs', '/test/blog', 2);

        } else {
            // プラグインがなければ追加(テストするFrameとページのインスタンス変数への保持も)
            $this->addPluginFirst('blogs', '/test/blog', 2);

            // 実行
            $this->browse(function (Browser $browser) {
                $browser->visit('/plugin/blogs/createBuckets/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                        ->assertPathBeginsWith('/')
                        ->type('blog_name', 'テストのブログ')
                        ->click('#label_rss_on')
                        ->type('rss_count', '20')
                        ->click('#label_use_like_on')
                        ->pause(500)
                        ->screenshot('user/blogs/createBuckets/images/createBuckets')
                        ->press('登録確定');
            });

            // マニュアル用データ出力
            $this->putManualData('user/blogs/createBuckets/images/createBuckets');
        }
    }

    /**
     * 表示条件設定
     */
    private function settingBlogFrame()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            $browser->visit('/plugin/blogs/settingBlogFrame/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->assertPathBeginsWith('/')
                    ->click('#label_blog_display_created_name_1')
                    ->click('#label_blog_display_twitter_button_1')
                    ->click('#label_blog_display_facebook_button_1')
                    ->pause(500)
                    ->screenshot('user/blogs/settingBlogFrame/images/settingBlogFrame')
                    ->press('設定変更');
        });

        // マニュアル用データ出力
        $this->putManualData('user/blogs/settingBlogFrame/images/settingBlogFrame');
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
