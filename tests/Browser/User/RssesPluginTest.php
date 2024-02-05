<?php

namespace Tests\Browser\User;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

use App\Enums\PluginName;
use App\Models\Common\Buckets;
use App\Models\Common\Frame;
use App\Models\Core\Dusks;
use App\Models\User\Rsses\Rsses;
use App\Models\User\Rsses\RssUrls;

/**
 * RSSテスト
 *
 * @see https://github.com/opensource-workshop/connect-cms/wiki/Dusk#テスト実行 [How to test]
 */
class RssesPluginTest extends DuskTestCase
{
    /**
     * RSSテスト
     *
     * @group user
     * @see https://readouble.com/laravel/6.x/ja/dusk.html#running-tests
     */
    public function test()
    {
        $this->init();
        $this->login(1);

        $this->createBuckets();
        $this->listBuckets();

        $this->editUrl();

        $this->logout();
        $this->index();   // 記事一覧
    }

    /**
     * 初期処理
     */
    private function init()
    {
        // データクリア
        Rsses::truncate();
        RssUrls::truncate();
        $this->initPlugin('rsses', '/test/rss');

        // 最初にマニュアルの順番確定用にメソッドを指定する。
        $this->reserveManual('index', 'editUrl', 'createBuckets', 'listBuckets');
    }

    /**
     * インデックス
     */
    private function index()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            $browser->visit('/test/rss')
                    ->assertPathBeginsWith('/')
                    ->screenshot('user/rsses/index/images/index');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/rsses/index/images/index",
             "name": "RSS",
             "comment": "<ul class=\"mb-0\"><li>RSSを取得して表示します</li></ul>"
            }
        ]', null, 4, 'basic');
    }

    /**
     * Url登録
     */
    private function editUrl($title = null)
    {
        // フレームの一時保存
        $this->frame1 = $this->test_frame;

        // 実行
        $this->browse(function (Browser $browser) {
            $browser->visit('plugin/rsses/editUrl/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->type('url', 'https://connect-cms.jp/redirect/plugin/blogs/rss/2/5')
                    ->type('title', '最新ニュース')
                    ->type('caption', 'Connect-CMS公式サイト')
                    ->assertPathBeginsWith('/')
                    ->screenshot('user/rsses/editUrl/images/editUrl1');

            $browser->press('追加')
                    ->assertPathBeginsWith('/')
                    ->screenshot('user/rsses/editUrl/images/editUrl2');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/rsses/editUrl/images/editUrl1",
             "name": "Url登録",
             "comment": "<ul class=\"mb-0\"><li>RSS取得先URLを入力し、追加ボタンを押すことで登録されます。</li></ul>"
            },
            {"path": "user/rsses/editUrl/images/editUrl2",
             "name": "Url追加後",
             "comment": "<ul class=\"mb-0\"><li>Urlを追加すると、「既存の設定行」に追加されます。</li></ul>"
            },
        ]', null, 4, 'basic');
    }

    /**
     * バケツ作成
     */
    private function createBuckets()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            $browser->visit('/plugin/rsses/createBuckets/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->assertPathBeginsWith('/')
                    ->type('rsses_name', 'テストのRSS')
                    ->screenshot('user/rsses/createBuckets/images/createBuckets')
                    ->press('登録確定');

            // 一度、選択確定させる。
            $bucket = Buckets::where('plugin_name', 'rsses')->first();
            $browser->visit('/plugin/rsses/listBuckets/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->radio('select_bucket', $bucket->id)
                    ->assertPathBeginsWith('/')
                    ->screenshot('user/rsses/listBuckets/images/listBuckets')
                    ->press("変更確定");

            // 変更
            $browser->visit("/plugin/rsses/editBuckets/" . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->assertPathBeginsWith('/')
                    ->screenshot('user/rsses/createBuckets/images/editBuckets');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/rsses/createBuckets/images/createBuckets",
             "name": "作成",
             "comment": "<ul class=\"mb-0\"><li>新しいRSSバケツを作成できます。</li></ul>"
            },
            {"path": "user/rsses/createBuckets/images/editBuckets",
             "name": "変更・削除",
             "comment": "<ul class=\"mb-0\"><li>RSSバケツを変更・削除できます。</li></ul>"
            }
        ]', null, 4, 'basic');
    }

    /**
     * 選択
     */
    private function listBuckets()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            $browser->visit('/plugin/rsses/listBuckets/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->assertPathBeginsWith('/')
                    ->screenshot('user/rsses/listBuckets/images/listBuckets');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/rsses/listBuckets/images/listBuckets",
             "comment": "<ul class=\"mb-0\"><li>表示するRSSバケツを変更できます。</li></ul>"
            }
        ]', null, 4);
    }
}
