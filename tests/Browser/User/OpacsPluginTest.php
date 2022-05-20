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
use App\Models\User\Opacs\Opacs;
use App\Models\User\Opacs\OpacsBooks;
use App\Models\User\Opacs\OpacsBooksLents;
use App\Models\User\Opacs\OpacsConfigs;
use App\Models\User\Opacs\OpacsFrames;

/**
 * Opacテスト
 *
 * @see https://github.com/opensource-workshop/connect-cms/wiki/Dusk#テスト実行 [How to test]
 */
class OpacsPluginTest extends DuskTestCase
{
    /**
     * Opacテスト
     *
     * @group user
     * @see https://readouble.com/laravel/6.x/ja/dusk.html#running-tests
     */
    public function test()
    {
        $this->init();
        $this->login(1);

        $this->createBuckets();
        $this->settingOpacFrame();
        $this->listBuckets();
        $this->create();

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
        Opacs::truncate();
        OpacsBooks::truncate();
        OpacsBooksLents::truncate();
        OpacsConfigs::truncate();
        OpacsFrames::truncate();
        $this->initPlugin('opacs', '/test/opac');

        // 最初にマニュアルの順番確定用にメソッドを指定する。
        $this->reserveManual('index', 'show', 'create', 'createBuckets', 'settingOpacFrame', 'listBuckets');
    }

    /**
     * インデックス
     */
    private function index()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            $browser->visit('/test/opac')
                    ->assertPathBeginsWith('/')
                    ->screenshot('user/opacs/index/images/index1');

            $browser->visit('/test/opac')
                    ->type('keyword', 'php')
                    ->press('#button_search')
                    ->screenshot('user/opacs/index/images/index2');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/opacs/index/images/index1",
             "name": "書籍検索",
             "comment": "<ul class=\"mb-0\"><li>書籍を検索できます。</li><li>タイトルや著者名で検索もできるので、本を探しやすくなっています。</li></ul>"
            },
            {"path": "user/opacs/index/images/index2",
             "name": "書籍検索結果",
             "comment": "<ul class=\"mb-0\"><li>書籍を検索した状態。</li></ul>"
            }
        ]', null, 4);
    }

    /**
     * 詳細
     */
    private function show()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            $browser->visit("/plugin/opacs/show/" . $this->test_frame->page_id . '/' . $this->test_frame->id . '/1#frame-' . $this->test_frame->id)
                    ->screenshot('user/opacs/show/images/show');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/opacs/show/images/show",
             "name": "書籍詳細",
             "comment": "<ul class=\"mb-0\"><li>書籍の詳細情報を見ることができます。</li></ul>"
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
            $browser->visit("/plugin/opacs/createBuckets/" . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->assertPathBeginsWith('/')
                    ->type('opac_name', 'テストのOpac')
                    ->type('view_count', '10')
                    ->screenshot('user/opacs/createBuckets/images/createBuckets')
                    ->press("登録確定");

            // 一度、選択確定させる。
            $bucket = Buckets::where('plugin_name', 'opacs')->first();
            $browser->visit('/plugin/opacs/listBuckets/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->radio('select_bucket', $bucket->id)
                    ->assertPathBeginsWith('/')
                    ->press("表示OPAC変更");

            // 変更
            $browser->visit("/plugin/opacs/editBuckets/" . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->assertPathBeginsWith('/')
                    ->screenshot('user/opacs/createBuckets/images/editBuckets');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/opacs/createBuckets/images/createBuckets",
             "name": "作成",
             "comment": "<ul class=\"mb-0\"><li>新しいOPACを作成できます。</li></ul>"
            },
            {"path": "user/opacs/createBuckets/images/editBuckets",
             "name": "変更・削除",
             "comment": "<ul class=\"mb-0\"><li>OPACを変更・削除できます。</li></ul>"
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
            $browser->visit('/plugin/opacs/listBuckets/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->assertPathBeginsWith('/')
                    ->screenshot('user/opacs/listBuckets/images/listBuckets')
                    ->press("表示OPAC変更");
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/opacs/listBuckets/images/listBuckets",
             "comment": "<ul class=\"mb-0\"><li>表示するOPACを変更できます。</li></ul>"
            }
        ]', null, 4);
    }

    /**
     * フレーム表示設定
     */
    private function settingOpacFrame()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            $browser->visit('/plugin/opacs/settingOpacFrame/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->click('#label_view_form1')
                    ->screenshot('user/opacs/settingOpacFrame/images/settingOpacFrame')
                    ->press("設定変更");
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/opacs/settingOpacFrame/images/settingOpacFrame",
             "comment": "<ul class=\"mb-0\"><li>OPACの表示形式を設定できます。</li></ul>"
            }
        ]', null, 4);
    }

    /**
     * 書籍登録
     */
    private function create()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            $browser->visit('/plugin/opacs/create/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->type('isbn', '4873116686')
                    ->screenshot('user/opacs/create/images/create1')
                    ->press('書誌データ取得')
                    ->pause(500)
                    ->type('barcode', '900000001')
                    ->screenshot('user/opacs/create/images/create2')
                    ->press('登録確定');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/opacs/create/images/create1",
             "name": "リンクリスト記事の登録",
             "comment": "<ul class=\"mb-0\"><li>書籍登録時、ISBNを入力して書籍情報を取得できます。</li></ul>"
            },
            {"path": "user/opacs/create/images/create2",
             "name": "ログイン時の一覧表示",
             "comment": "<ul class=\"mb-0\"><li>書籍の登録</li></ul>"
            }
        ]', null, 4);
    }
}
