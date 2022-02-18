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
        $this->editBucketsMails();

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
        $this->reserveManual('index', 'show', 'edit', 'createBuckets', 'editView', 'listBuckets', 'editBucketsMails');
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
                    ->screenshot('user/bbses/index/images/index');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/bbses/index/images/index",
             "name": "記事の一覧",
             "comment": "<ul class=\"mb-0\"><li>記事は新しいものから表示されます。</li></ul>"
            }
        ]');
/*
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
*/
    }

    /**
     * 記事記入
     */
    private function edit($title = null)
    {
        // 記事で使う画像の取得
        $upload = Uploads::where('client_original_name', 'blobid0000000000001.jpg')->first();

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
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/bbses/edit/images/create",
             "comment": "<ul class=\"mb-0\"><li>記事は新しいものから表示されます。</li></ul>"
            }
        ]');
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
        $this->putManualData('user/bbses/show/images/show');
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
                    ->type('like_button_name', '👍')
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
        ]');
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
        ]');
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
        ]');
    }

    /**
     * メール設定
     */
    private function editBucketsMails()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            $browser->visit('/plugin/bbses/editBucketsMails/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->screenshot('user/bbses/editBucketsMails/images/editBucketsMails')
                    ->click('#label_notice_on')
                    ->pause(500)
                    ->scrollIntoView('#label_notice_on')
                    ->screenshot('user/bbses/editBucketsMails/images/editBucketsMailsNotice')
                    ->click('#label_relate_on')
                    ->pause(500)
                    ->scrollIntoView('#label_relate_on')
                    ->screenshot('user/bbses/editBucketsMails/images/editBucketsMailsRelate')
                    ->click('#label_approval_on')
                    ->pause(500)
                    ->scrollIntoView('#label_approval_on')
                    ->screenshot('user/bbses/editBucketsMails/images/editBucketsMailsApproval')
                    ->click('#label_approved_on')
                    ->pause(500)
                    ->scrollIntoView('#label_approved_on')
                    ->screenshot('user/bbses/editBucketsMails/images/editBucketsMailsApproved');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/bbses/editBucketsMails/images/editBucketsMails",
             "name": "送信タイミング設定",
             "comment": "<ul class=\"mb-0\"><li>タイミング毎にメールの送信を設定できます。</li></ul>"
            },
            {"path": "user/bbses/editBucketsMails/images/editBucketsMailsNotice",
             "name": "投稿通知",
             "comment": "<ul class=\"mb-0\"><li>投稿通知の設定です。</li></ul>"
            },
            {"path": "user/bbses/editBucketsMails/images/editBucketsMailsRelate",
             "name": "関連記事通知",
             "comment": "<ul class=\"mb-0\"><li>関連記事の投稿通知の設定です。</li></ul>"
            },
            {"path": "user/bbses/editBucketsMails/images/editBucketsMailsApproval",
             "name": "承認通知",
             "comment": "<ul class=\"mb-0\"><li>承認通知の設定です。</li></ul>"
            },
            {"path": "user/bbses/editBucketsMails/images/editBucketsMailsApproved",
             "name": "承認済み通知",
             "comment": "<ul class=\"mb-0\"><li>承認済み通知の設定です。</li></ul>"
            }
        ]');
    }

    /**
     * テンプレート
     */
    private function template()
    {
        $this->putManualTemplateData($this->test_frame, 'user', '/test/blog', ['blogs', 'ブログ'], ['datefirst' => '日付先頭', 'titleindex' => 'タイトルのみ']);
    }
}
