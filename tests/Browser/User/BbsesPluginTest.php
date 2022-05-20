<?php

namespace Tests\Browser\User;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

use App\Enums\PluginName;
use App\Models\Common\Buckets;
use App\Models\Common\Uploads;
use App\Models\Core\Dusks;
use App\Models\User\Bbses\Bbs;
use App\Models\User\Bbses\BbsFrame;
use App\Models\User\Bbses\BbsPost;

/**
 * 掲示板テスト
 *
 * @see https://github.com/opensource-workshop/connect-cms/wiki/Dusk#テスト実行 [How to test]
 */
class BbsesPluginTest extends DuskTestCase
{
    /**
     * 掲示板テスト
     *
     * @group user
     * @see https://readouble.com/laravel/6.x/ja/dusk.html#running-tests
     */
    public function test()
    {
        $this->init();
        $this->login(1);

        $this->createBuckets();
        $this->editView();
        $this->listBuckets();

        $this->edit("テスト投稿　１件目");  // 記事登録
        $this->edit("テスト投稿　２件目");  // 記事登録 2件目
        $this->edit("テスト投稿　３件目");  // 記事登録 3件目

        $this->logout();
        $this->index();
        $this->show();
    }

    /**
     * 初期処理
     */
    private function init()
    {
        // データクリア
        Bbs::truncate();
        BbsFrame::truncate();
        BbsPost::truncate();
        $this->initPlugin('bbses', '/test/bbs');

        // 最初にマニュアルの順番確定用にメソッドを指定する。
        $this->reserveManual('index', 'show', 'edit', 'createBuckets', 'editView', 'listBuckets');
    }

    /**
     * インデックス
     */
    private function index()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            $browser->visit('/test/bbs')
                    ->assertPathBeginsWith('/')
                    ->screenshot('user/bbses/index/images/index1');

            BbsFrame::find(1)->update(['list_format' => 2]);
            $browser->visit('/test/bbs')
                    ->screenshot('user/bbses/index/images/index2');
            BbsFrame::find(1)->update(['list_format' => 0]);
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/bbses/index/images/index1",
             "name": "記事の一覧",
             "comment": "<ul class=\"mb-0\"><li>記事は新しいものから表示されます。</li></ul>"
            },
            {"path": "user/bbses/index/images/index2",
             "name": "記事の一覧（一覧での展開方法をすべて閉じておく）",
             "comment": "<ul class=\"mb-0\"><li>一覧ではタイトルのみ表示することもできます。</li></ul>"
            }
        ]', null, 4);
    }

    /**
     * 記事記入
     */
    private function edit($title = null)
    {
        // 記事で使う画像の取得
        $upload = $this->firstOrCreateFileUpload('manual', 'copy_data/image/blobid0000000000001.png', 'blobid0000000000001.png', 'image/png', 'png', 'bbses', $this->test_frame->page_id);

        $body = $title . 'の本文です。';
        if ($upload) {
            $body .= '<br /><img src="/file/' . $upload->id . '" />';
        }

        // 実行
        $this->browse(function (Browser $browser) use ($title, $body) {

            $browser->visit('plugin/bbses/edit/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->type('title', $title)
                    ->driver->executeScript('tinyMCE.get(0).setContent(\'' . $body . '\')');

            $browser->pause(500)
                    ->screenshot('user/bbses/edit/images/create')
                    ->press('登録確定');

            // 最新の記事を取得
            $post = BbsPost::orderBy('id', 'desc')->first();

            $browser->visit('plugin/bbses/show/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '/' . $post->id . '#frame-' . $this->test_frame->id)
                    ->click('#label_reply' . $this->test_frame->id)
                    ->pause(500)
                    ->screenshot('user/bbses/edit/images/show')
                    ->press('#button_reply' . $this->test_frame->id)
                    ->pause(500)
                    ->screenshot('user/bbses/edit/images/reply');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/bbses/edit/images/create",
             "name": "記事の編集",
             "comment": "<ul class=\"mb-0\"><li>記事は新しいものから表示されます。</li></ul>"
            },
            {"path": "user/bbses/edit/images/show",
             "name": "記事の詳細",
             "comment": "<ul class=\"mb-0\"><li>記事の詳細から返信ができます。</li></ul>"
            },
            {"path": "user/bbses/edit/images/reply",
             "name": "記事の返信",
             "comment": "<ul class=\"mb-0\"><li>引用するをチェックして返信を押した状態</li></ul>"
            }
        ]', null, 4);
    }

    /**
     * 記事詳細
     */
    private function show()
    {
        // 最新の記事を取得
        $post = BbsPost::orderBy('id', 'desc')->first();

        // 実行
        $this->browse(function (Browser $browser) use ($post) {

            $browser->visit('plugin/bbses/show/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '/' . $post->id . '#frame-' . $this->test_frame->id)
                    ->screenshot('user/bbses/show/images/show');
        });

        // マニュアル用データ出力
        $this->putManualData('user/bbses/show/images/show', null, 4);
    }

    /**
     * バケツ作成
     */
    private function createBuckets()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            // 新規作成
            $browser->visit('/plugin/bbses/createBuckets/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->type('name', 'テストの掲示板')
                    ->click('#label_use_like_on')
                    ->pause(500)
                    // bugfix: 絵文字はテスト非対応。Facebook\WebDriver\Exception\UnknownErrorException: unknown error: ChromeDriver only supports characters in the BMP
                    // ->type('like_button_name', '👍')
                    ->type('like_button_name', 'イイネ！')
                    ->screenshot('user/bbses/createBuckets/images/createBuckets')
                    ->press('登録確定');

            // 一度、選択確定させる。
            $bucket = Buckets::where('plugin_name', 'bbses')->first();
            $browser->visit('/plugin/bbses/listBuckets/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->radio('select_bucket', $bucket->id)
                    ->press("表示掲示板変更");

            // 変更
            $browser->visit("/plugin/bbses/editBuckets/" . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->pause(500)
                    ->screenshot('user/bbses/createBuckets/images/editBuckets');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/bbses/createBuckets/images/createBuckets",
             "name": "作成",
             "comment": "<ul class=\"mb-0\"><li>新しい掲示板を作成できます。</li></ul>"
            },
            {"path": "user/bbses/createBuckets/images/editBuckets",
             "name": "変更・削除",
             "comment": "<ul class=\"mb-0\"><li>掲示板を変更・削除できます。</li></ul>"
            }
        ]', null, 4);
    }

    /**
     * ブログ選択
     */
    private function listBuckets()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            $browser->visit('/plugin/bbses/listBuckets/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->assertPathBeginsWith('/')
                    ->screenshot('user/bbses/listBuckets/images/listBuckets');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/bbses/listBuckets/images/listBuckets",
             "comment": "<ul class=\"mb-0\"><li>表示ブログを変更できます。</li></ul>"
            }
        ]', null, 4);
    }

    /**
     * フレーム表示設定
     */
    private function editView()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            $browser->visit('/plugin/bbses/editView/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->screenshot('user/bbses/editView/images/editView');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/bbses/editView/images/editView",
             "comment": "<ul class=\"mb-0\"><li>掲示板の表示形式を設定できます。</li></ul>"
            }
        ]', null, 4);
    }

    /**
     * テンプレート
     */
    private function template()
    {
        $this->putManualTemplateData($this->test_frame, 'user', '/test/blog', ['blogs', 'ブログ'], ['datefirst' => '日付先頭', 'titleindex' => 'タイトルのみ']);
    }
}
