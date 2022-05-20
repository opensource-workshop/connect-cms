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
use App\Models\User\Searchs\Searchs;

/**
 * サイト内検索テスト
 *
 * @see https://github.com/opensource-workshop/connect-cms/wiki/Dusk#テスト実行 [How to test]
 */
class SearchsPluginTest extends DuskTestCase
{
    /**
     * サイト内検索テスト
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

        $this->logout();
        $this->index();
    }

    /**
     * 初期処理
     */
    private function init()
    {
        // データクリア
        Searchs::truncate();
        $this->initPlugin('searchs', '/test/search');

        // 最初にマニュアルの順番確定用にメソッドを指定する。
        $this->reserveManual('index', 'createBuckets', 'listBuckets');
    }

    /**
     * インデックス
     */
    private function index()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            $browser->visit('/test/search')
                    ->assertPathBeginsWith('/')
                    ->screenshot('user/searchs/index/images/index');

            $browser->type('search_keyword', 'テスト')
                    ->press("#button_search")
                    ->pause(500)
                    ->screenshot('user/searchs/index/images/result');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/searchs/index/images/index",
             "name": "検索フォーム",
             "comment": "<ul class=\"mb-0\"><li>サイト内検索ボックスが表示され、キーワードで検索できます。</li></ul>"
            },
            {"path": "user/searchs/index/images/result",
             "name": "検索結果",
             "comment": "<ul class=\"mb-0\"><li>指定したキーワードがあるコンテンツの一覧が表示されます。</li><li>クリックすることで、そのページに遷移します。</li></ul>"
            }
        ]', null, 4);
    }

    /**
     * バケツ作成
     */
    private function createBuckets()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            // 新規作成
            $browser->visit("/plugin/searchs/createBuckets/" . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->type('search_name', 'テストのサイト内検索')
                    ->type('count', '20')
                    ->click('#label_target_plugin_contents')
                    ->click('#label_target_plugin_blogs')
                    ->click('#label_target_plugin_bbses')
                    ->click('#label_target_plugin_databases')
                    ->pause(500)
                    ->screenshot('user/searchs/createBuckets/images/createBuckets1');

            $browser->scrollIntoView('footer')
                    ->screenshot('user/searchs/createBuckets/images/createBuckets2')
                    ->press("登録");

            // 一度、選択確定させる。
            $bucket = Buckets::where('plugin_name', 'searchs')->first();
            $browser->visit('/plugin/searchs/listBuckets/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->radio('select_bucket', $bucket->id)
                    ->screenshot('user/searchs/createBuckets/images/listBuckets')
                    ->press("表示サイト内検索変更");

            // 変更
            $browser->visit("/plugin/searchs/editBuckets/" . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->assertPathBeginsWith('/')
                    ->screenshot('user/searchs/createBuckets/images/editBuckets1');

            $browser->scrollIntoView('footer')
                    ->assertPathBeginsWith('/')
                    ->screenshot('user/searchs/createBuckets/images/editBuckets2');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/searchs/createBuckets/images/createBuckets1", "name": "新規作成１"},
            {"path": "user/searchs/createBuckets/images/createBuckets2", "name": "新規作成２",
             "comment": "<ul class=\"mb-0\"><li>表示する内容やプラグインを選択できます。</li><li>特定のフレームだけを検索対象とすることもできます。<br />（ただし、固定記事プラグインはフレームを選択できません。そのため「選択したものだけ表示する」を選択した場合、固定記事プラグインは検索対象外になります）</li><li>他ページからのキーワードを受け取る設定にすると、「固定記事」プラグインで検索ボックスを作成し、form のaction でこのページを指定して、結果を表示することができます。</li></ul>"
            },
            {"path": "user/searchs/createBuckets/images/editBuckets1", "name": "変更・削除１"},
            {"path": "user/searchs/createBuckets/images/editBuckets2", "name": "変更・削除２",
             "comment": "<ul class=\"mb-0\"><li>サイト内検索を変更・削除できます。</li></ul>"
            }
        ]', null, 4);
    }

    /**
     * バケツ選択
     */
    private function listBuckets()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            $browser->visit('/plugin/searchs/listBuckets/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->assertPathBeginsWith('/')
                    ->screenshot('user/searchs/listBuckets/images/listBuckets')
                    ->press("表示サイト内検索変更");
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/searchs/listBuckets/images/listBuckets",
             "comment": "<ul class=\"mb-0\"><li>表示するサイト内検索を変更できます。</li></ul>"
            }
        ]', null, 4);
    }
}
