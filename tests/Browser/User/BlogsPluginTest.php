<?php

namespace Tests\Browser\User;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

use App\Enums\PluginName;
use App\Models\Common\Frame;
use App\Models\Common\Uploads;
use App\Models\User\Blogs\Blogs;
use App\Models\User\Blogs\BlogsPosts;

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
        // 最初にマニュアルの順番確定用にメソッドを指定する。
        $this->reserveManual('index', 'show', 'create', 'edit', 'createBuckets', 'settingBlogFrame', 'listCategories');

        $this->login(1);

        // プラグインが配置されていなければ追加(テストするFrameとページのインスタンス変数への保持も)
        $this->addPluginFirst('blogs', '/test/blog', 2);

        $this->createBuckets();
        $this->settingBlogFrame();
        $this->listCategories();

        $this->create("テスト投稿　１件目");  // 記事登録
        $this->create("テスト投稿　２件目");  // 記事登録 2件目
        $this->create("テスト投稿　３件目");  // 記事登録 3件目
        $this->edit();

        $this->logout();
        $this->index();   // 記事一覧
        $this->show();    // 記事詳細
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

        // 最新の記事を取得
        $post = BlogsPosts::orderBy('id', 'desc')->first();

        $this->login(1);

        // 実行
        $this->browse(function (Browser $browser) use ($post) {
            $browser->visit('/test/blog')
                    ->click('#button_copy' . $post->id)
                    ->assertPathBeginsWith('/')
                    ->screenshot('user/blogs/index/images/index2');
        });

        $this->logout();

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/blogs/index/images/index",
             "comment": "<ul class=\"mb-0\"><li>記事は新しいものから表示されます。</li></ul>"
            },
            {"path": "user/blogs/index/images/index2",
             "name": "記事のコピー",
             "comment": "<ul class=\"mb-0\"><li>編集権限がある場合、記事の編集ボタンの右にある▼ボタンで、記事のコピーができます。</li></ul>"
            }
        ]');
    }

    /**
     * 記事記入
     */
    private function create($title = null)
    {
        // ブログ（バケツ）があって且つ、記事が3件未満の場合に記事作成
        if (Frame::where('plugin_name', 'blogs')->first() && BlogsPosts::count() < 3) {

            // 記事で使う画像の取得
            $upload = Uploads::where('client_original_name', 'blobid0000000000001.jpg')->first();

            $body = '<h3>' . $title . 'の本文です。</h3>';
            if ($upload) {
                $body .= '<br /><img src="/file/' . $upload->id . '" />';
            }

            // 実行
            $this->browse(function (Browser $browser) use ($title, $body) {

                $browser->visit('plugin/blogs/create/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                        ->assertPathBeginsWith('/')
                        ->type('post_title', $title)
                        ->driver->executeScript('tinyMCE.get(0).setContent(\'' . $body . '\')');

                $browser->screenshot('user/blogs/create/images/create');

                $browser->scrollIntoView('footer')
                        ->screenshot('user/blogs/create/images/create2')
                        ->press('登録確定');
            });
        }

        // マニュアル用データ出力(記事の登録はしていなくても、画像データはできているはず。reserveManual() で一旦、内容がクリアされているので、画像の登録は行う)
        $this->putManualData('user/blogs/create/images/create,user/blogs/create/images/create2');
    }

    /**
     * 記事編集
     */
    private function edit()
    {
        // 最新の記事を取得
        $post = BlogsPosts::orderBy('id', 'desc')->first();

        // 実行
        $this->browse(function (Browser $browser) use ($post) {

            $browser->visit('plugin/blogs/edit/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '/' . $post->id . '#frame-' . $this->test_frame->id)
                    ->assertPathBeginsWith('/')
                    ->screenshot('user/blogs/edit/images/edit');

            $browser->scrollIntoView('footer')
                    ->screenshot('user/blogs/edit/images/edit2');
        });

        // マニュアル用データ出力
        $this->putManualData('user/blogs/edit/images/edit,user/blogs/edit/images/edit2');
    }

    /**
     * 記事詳細
     */
    private function show()
    {
        // 最新の記事を取得
        $post = BlogsPosts::orderBy('id', 'desc')->first();

        // 実行
        $this->browse(function (Browser $browser) use ($post) {

            $browser->visit('plugin/blogs/show/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '/' . $post->id . '#frame-' . $this->test_frame->id)
                    ->assertPathBeginsWith('/')
                    ->screenshot('user/blogs/show/images/show');
        });

        // マニュアル用データ出力
        $this->putManualData('user/blogs/show/images/show');
    }

    /**
     * バケツ作成
     */
    private function createBuckets()
    {
        // バケツがなければ作成する。（プラグイン＆フレームの配置まではできているはず）
        if (Blogs::count() == 0) {
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
        }

        // マニュアル用データ出力(バケツの登録はしていなくても、画像データはできているはず。reserveManual() で一旦、内容がクリアされているので、画像の登録は行う)
        $this->putManualData('user/blogs/createBuckets/images/createBuckets');
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
     * カテゴリー
     */
    private function listCategories()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            $browser->visit('/plugin/blogs/listCategories/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->assertPathBeginsWith('/')
                    ->screenshot('user/blogs/listCategories/images/listCategories');
        });

        // マニュアル用データ出力
        $this->putManualData('user/blogs/listCategories/images/listCategories');
    }
}
