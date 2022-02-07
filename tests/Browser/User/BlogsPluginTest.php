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
     * ブログテスト
     *
     * @group user
     * @see https://readouble.com/laravel/6.x/ja/dusk.html#running-tests
     */
    public function testBlog()
    {
        // 最初にマニュアルの順番確定用にメソッドを指定する。
        $this->reserveManual('index', 'show', 'create', 'edit', 'createBuckets', 'settingBlogFrame', 'listCategories', 'listBuckets');

        $this->login(1);

        // プラグインが配置されていなければ追加(テストするFrameとページのインスタンス変数への保持も)
        $this->addPluginFirst('blogs', '/test/blog', 2);

        $this->createBuckets();
        $this->settingBlogFrame();
        $this->listCategories();
        $this->listBuckets();

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
             "name": "記事の一覧",
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
        $this->putManualData('[
            {"path": "user/blogs/create/images/create",
             "comment": "<ul class=\"mb-0\"><li>記事は新しいものから表示されます。</li></ul>"
            },
            {"path": "user/blogs/create/images/create2",
             "comment": "<ul class=\"mb-0\"><li>投稿日時に未来の日時を指定した場合、その日時になったら表示されます。</li><li>「重要記事」チェックは新着で上に出し続けるなど、新着での表示方法を変更できる機能です。<br />表示形式は新着情報プラグインの設定を参照してください。</li><li>また、「重要記事」タグも表示されます。</li><li>カテゴリを設定できます。カテゴリはサイト共通のものと、このブログ特有のものを設定できます。</li><li>タグを設定できます。</li></ul>"
            }
        ]');
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

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/blogs/createBuckets/images/createBuckets",
             "comment": "<ul class=\"mb-0\"><li>RSSを表示するに設定した場合は、RSSリンクが表示されます。</li><li>いいねボタンを表示する設定にした場合は、いいねボタンが表示されます。</li></ul>"
            }
        ]');
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
        $this->putManualData('[
            {"path": "user/blogs/settingBlogFrame/images/settingBlogFrame",
             "comment": "<ul class=\"mb-0\"><li>表示条件には全て、年、年度があります。年と年度を選んだ場合は、年もしくは年度を指定します。これは、年や年度が替わった際、前年度の内容を別ページでアーカイブ表示したい場合に使用する機能です。</li><li>同じブログをフレーム毎に違う条件で絞り込みできます。</li><li>投稿者名の表示する・表示しないが選択できます。</li></ul>"
            }
        ]');
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
        $this->putManualData('[
            {"path": "user/blogs/listCategories/images/listCategories",
             "comment": "<ul class=\"mb-0\"><li>カテゴリ設定は共通カテゴリとこのブログ独自のカテゴリ設定があります。</li><li>上の表が共通カテゴリで、表示のON/OFFと順番が指定できます。</li><li>下の表でこのブログ独自のカテゴリを設定できます。</li></ul>"
            }
        ]');
    }

    /**
     * ブログ選択
     */
    private function listBuckets()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            $browser->visit('/plugin/blogs/listBuckets/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->assertPathBeginsWith('/')
                    ->screenshot('user/blogs/listBuckets/images/listBuckets');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/blogs/listBuckets/images/listBuckets",
             "comment": "<ul class=\"mb-0\"><li>表示ブログを変更できます。</li></ul>"
            }
        ]');
    }
}
