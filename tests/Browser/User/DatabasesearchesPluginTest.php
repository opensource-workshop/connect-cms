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
use App\Models\User\Databasesearches\Databasesearches;

/**
 * データベース検索テスト
 *
 * @see https://github.com/opensource-workshop/connect-cms/wiki/Dusk#テスト実行 [How to test]
 */
class DatabasesearchesPluginTest extends DuskTestCase
{
    /**
     * データベース検索テスト
     *
     * @group user
     * @see https://readouble.com/laravel/6.x/ja/dusk.html#running-tests
     */
    public function test()
    {
        $this->init();
        $this->login(1);

        $this->editBuckets();
        $this->listBuckets();

        $this->logout();
        $this->index();
        $this->template();
    }

    /**
     * 初期処理
     */
    private function init()
    {
        // データクリア
        Databasesearches::truncate();
        $this->initPlugin('databasesearches', '/test/databasesearch');

        // 最初にマニュアルの順番確定用にメソッドを指定する。
        $this->reserveManual('index', 'editBuckets', 'listBuckets');
    }

    /**
     * インデックス
     */
    private function index()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            $browser->visit('/test/databasesearch')
                    ->assertPathBeginsWith('/')
                    ->screenshot('user/databasesearches/index/images/index');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/databasesearches/index/images/index",
             "name": "データベース検索",
             "comment": "<ul class=\"mb-0\"><li>指定した条件でデータベース・プラグインのデータを検索し、表示できます。</li></ul>"
            }
        ]', null, 4);
    }

    /**
     * バケツ作成
     */
    private function editBuckets()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            // 新規作成
            $browser->visit("/plugin/databasesearches/editBuckets/" . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->type('databasesearches_name', 'テストのデータベース検索')
                    ->type('view_count', '20')
                    ->type('view_columns', '都道府県名,都道府県庁所在地')
                    ->screenshot('user/databasesearches/editBuckets/images/editBuckets1')
                    ->scrollIntoView('footer')
                    ->screenshot('user/databasesearches/editBuckets/images/editBuckets2')
                    ->press("登録");

            // 一度、選択確定させる。
            $bucket = Buckets::where('plugin_name', 'databasesearches')->first();
            $browser->visit('/plugin/databasesearches/listBuckets/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->radio('select_bucket', $bucket->id)
                    ->screenshot('user/databasesearches/createBuckets/images/listBuckets')
                    ->press("表示データベース検索変更");
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/databasesearches/editBuckets/images/editBuckets1", "name": "設定変更１"},
            {"path": "user/databasesearches/editBuckets/images/editBuckets2", "name": "設定変更２",
             "comment": "<ul class=\"mb-0\"><li>表示列はデータベースプラグインで定義している項目名をカンマ区切りで定義します。</li><li>SQLライクに抽出条件を設定することも可能です。</li><li>表示させる情報を、データベースの設定とは別でソートさせて表示することも可能です。</li></ul>"
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
            $browser->visit('/plugin/databasesearches/listBuckets/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->screenshot('user/databasesearches/listBuckets/images/listBuckets');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/databasesearches/listBuckets/images/listBuckets",
             "comment": "<ul class=\"mb-0\"><li>表示するデータベース検索を変更できます。</li></ul>"
            }
        ]', null, 4);
    }

    /**
     * テンプレート
     */
    private function template()
    {
        $this->putManualTemplateData($this->test_frame, 'user', '/test/databasesearch', ['databasesearches', 'データベース検索'], ['card_04' => 'card_04']);
    }
}
