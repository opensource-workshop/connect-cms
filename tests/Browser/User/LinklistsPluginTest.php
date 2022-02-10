<?php

namespace Tests\Browser\User;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

use App\Enums\PluginName;
use App\Models\Common\Buckets;
use App\Models\Common\Frame;
use App\Models\Common\Uploads;
use App\Models\Core\Dusks;
use App\Models\User\Linklists\Linklist;
use App\Models\User\Linklists\LinklistFrame;
use App\Models\User\Linklists\LinklistPost;

/**
 * リンクリストテスト
 *
 * @see https://github.com/opensource-workshop/connect-cms/wiki/Dusk#テスト実行 [How to test]
 */
class LinklistsPluginTest extends DuskTestCase
{
    /**
     * リンクリストテスト
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
        $this->listCategories();
        $this->listBuckets();
        $this->edit();

        $this->logout();
        $this->index();    // 記事一覧
    }

    /**
     * 初期処理
     */
    private function init()
    {
        // 最初にマニュアルの順番確定用にメソッドを指定する。
        $this->reserveManual('index', 'edit', 'createBuckets', 'editView', 'listCategories', 'listBuckets');

        // データクリア
        Linklist::truncate();
        LinklistFrame::truncate();
        LinklistPost::truncate();
        $this->initPlugin('linklists', '/test/linklist');
    }

    /**
     * インデックス
     */
    private function index()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            $browser->visit('/test/linklist')
                    ->assertPathBeginsWith('/')
                    ->screenshot('user/linklists/index/images/index');
        });

        // マニュアル用データ出力
        $this->putManualData('user/linklists/index/images/index');
    }

    /**
     * バケツ作成
     */
    private function createBuckets()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            // 新規作成
            $browser->visit("/plugin/linklists/createBuckets/" . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->assertPathBeginsWith('/')
                    ->type('name', 'テストのリンクリスト')
                    ->screenshot('user/linklists/createBuckets/images/createBuckets')
                    ->press("登録確定");

            // 一度、選択確定させる。
            $bucket = Buckets::where('plugin_name', 'linklists')->first();
            $browser->visit('/plugin/linklists/listBuckets/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->radio('select_bucket', $bucket->id)
                    ->assertPathBeginsWith('/')
                    ->press("表示リンクリスト変更");

            // 変更
            $browser->visit("/plugin/linklists/editBuckets/" . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->assertPathBeginsWith('/')
                    ->screenshot('user/linklists/createBuckets/images/editBuckets');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/linklists/createBuckets/images/createBuckets",
             "name": "作成",
             "comment": "<ul class=\"mb-0\"><li>新しいリンクリストを作成できます。</li></ul>"
            },
            {"path": "user/linklists/createBuckets/images/editBuckets",
             "name": "変更・削除",
             "comment": "<ul class=\"mb-0\"><li>リンクリストを変更・削除できます。</li></ul>"
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
            $browser->visit('/plugin/linklists/listCategories/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->assertPathBeginsWith('/')
                    ->screenshot('user/linklists/listCategories/images/listCategories');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/linklists/listCategories/images/listCategories",
             "comment": "<ul class=\"mb-0\"><li>カテゴリ設定は共通カテゴリとこのブログ独自のカテゴリ設定があります。</li><li>上の表が共通カテゴリで、表示のON/OFFと順番が指定できます。</li><li>下の表でこのブログ独自のカテゴリを設定できます。</li></ul>"
            }
        ]');
    }

    /**
     * バケツ選択
     */
    private function listBuckets()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            $browser->visit('/plugin/linklists/listBuckets/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->assertPathBeginsWith('/')
                    ->screenshot('user/linklists/listBuckets/images/listBuckets')
                    ->press("表示リンクリスト変更");
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/linklists/listBuckets/images/listBuckets",
             "comment": "<ul class=\"mb-0\"><li>表示するリンクリストを変更できます。</li></ul>"
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
            $browser->visit('/plugin/linklists/editView/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->screenshot('user/linklists/editView/images/editView');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/whatsnews/editView/images/editView",
             "comment": "<ul class=\"mb-0\"><li>リンクリストの表示形式や1ページの表示件数を設定できます。</li></ul>"
            }
        ]');
    }

    /**
     * 記事登録
     */
    private function edit()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            // 一度クリア
            LinklistPost::truncate();

            $browser->visit('/plugin/linklists/edit/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->type('title', 'Connect-CMS公式')
                    ->type('url', 'https://connect-cms.jp/')
                    ->type('description', 'Connect-CMSの情報はこのサイトから。')
                    ->screenshot('user/linklists/edit/images/create')
                    ->press('登録確定');

            $browser->visit('/plugin/linklists/edit/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->type('title', '株式会社オープンソース・ワークショップ')
                    ->type('url', 'https://opensource-workshop.jp/')
                    ->type('description', 'Connect-CMSのクラウドサービス')
                    ->press('登録確定');

            $browser->visit('/test/linklist')
                    ->screenshot('user/linklists/edit/images/list');

            $browser->visit('/plugin/linklists/edit/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '/1#frame-' . $this->test_frame->id)
                    ->screenshot('user/linklists/edit/images/edit');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/linklists/edit/images/create",
             "name": "リンクリスト記事の登録",
             "comment": "<ul class=\"mb-0\"><li>タイトルやURLなどを登録できます。</li></ul>"
            },
            {"path": "user/linklists/edit/images/list",
             "name": "ログイン時の一覧表示",
             "comment": "<ul class=\"mb-0\"><li>タイトルの横の編集アイコンから内容を変更できます。</li></ul>"
            },
            {"path": "user/linklists/edit/images/edit",
             "name": "リンクリスト記事の変更・削除",
             "comment": "<ul class=\"mb-0\"><li>タイトルやURLなどを変更・削除できます。</li></ul>"
            }
        ]');
    }
}
