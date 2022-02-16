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
use App\Models\User\Counters\Counter;
use App\Models\User\Counters\CounterCount;
use App\Models\User\Counters\CounterFrame;

/**
 * カウンターテスト
 *
 * @see https://github.com/opensource-workshop/connect-cms/wiki/Dusk#テスト実行 [How to test]
 */
class CountersPluginTest extends DuskTestCase
{
    /**
     * カウンターテスト
     *
     * @group user
     * @see https://readouble.com/laravel/6.x/ja/dusk.html#running-tests
     */
    public function test()
    {
        $this->init();
        $this->login(1);

        $this->createBuckets();

        // 左エリアにカウンターを追加（$this->test_frame と$this->test_page は退避して戻す）
//        $test_frame = $this->test_frame;
//        $test_page = $this->test_page;

//echo "TEST\n";
//echo $this->test_frame->bucket_id;
/*
        $this->addPluginFirst('counters', '/', 1);

        Frame::where('area_id', 1)->where('plugin_name', 'menus')->update(['display_sequence' => 1]);

        $frame = Frame::where('area_id', 2)->where('plugin_name', 'counters')->first();

        Frame::where('area_id', 1)->where('plugin_name', 'counters')->update(['display_sequence' => 2, 'bucket_id' => $frame->bucket_id]);
echo "\n" . $frame->bucket_id . "\n";
*/
//Frame::where('plugin_name', 'counters')->where->('area_id', 2)
//echo $this->test_frame->bucket_id;



        $this->addPluginModal('counters', '/', 1, false);
        Frame::where('area_id', 1)->where('plugin_name', 'menus')->update(['display_sequence' => 1]);
        $frame = Frame::where('area_id', 2)->where('plugin_name', 'counters')->first();
        Frame::where('area_id', 1)->where('plugin_name', 'counters')->update(['display_sequence' => 2, 'bucket_id' => $frame->bucket_id]);
        $left_frame = Frame::where('area_id', 1)->where('plugin_name', 'counters')->first();
        $left_counter_frame = CounterFrame::first()->replicate()->fill(['frame_id' => $left_frame->id]);
        $left_counter_frame->save();



        $this->editView();
        $this->listCounters();
        $this->listBuckets();

        $this->logout();
        $this->index();    // 記事一覧
    }

    /**
     * 初期処理
     */
    private function init()
    {
        // データクリア
        Counter::truncate();
        CounterCount::truncate();
        CounterFrame::truncate();
        $this->initPlugin('counters', '/test/counter');

        // 最初にマニュアルの順番確定用にメソッドを指定する。
        $this->reserveManual('index', 'createBuckets', 'editView', 'listCounters', 'listBuckets');
    }

    /**
     * インデックス
     */
    private function index()
    {
        $this->browse(function (Browser $browser) {
            // キャプチャ
            $browser->visit('/test/counter')
                    ->assertPathBeginsWith('/')
                    ->screenshot('user/counters/index/images/index1');

            // フッタをキャプチャ
            $browser->scrollIntoView('footer')
                    ->screenshot('user/counters/index/images/index2');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/counters/index/images/index1",
             "name": "カウンター１",
             "comment": "<ul class=\"mb-0\"><li>メインエリアに置いたところ。</li></ul>"
            },
            {"path": "user/counters/index/images/index2",
             "name": "カウンター２",
             "comment": "<ul class=\"mb-0\"><li>左エリアに置いたところ。</li></ul>"
            }
        ]');
    }

    /**
     * バケツ作成
     */
    private function createBuckets()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            // 新規作成
            $browser->visit("/plugin/counters/createBuckets/" . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->assertPathBeginsWith('/')
                    ->type('name', 'テストのカウンター')
                    ->screenshot('user/counters/createBuckets/images/createBuckets')
                    ->press("登録確定");

            // 一度、選択確定させる。
            $bucket = Buckets::where('plugin_name', 'counters')->first();
            $browser->visit('/plugin/counters/listBuckets/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->radio('select_bucket', $bucket->id)
                    ->assertPathBeginsWith('/')
                    ->press("表示カウンター変更");

            // 変更
            $browser->visit("/plugin/counters/editBuckets/" . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->assertPathBeginsWith('/')
                    ->screenshot('user/counters/createBuckets/images/editBuckets');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/counters/createBuckets/images/createBuckets",
             "name": "作成",
             "comment": "<ul class=\"mb-0\"><li>新しいカウンターを作成できます。</li></ul>"
            },
            {"path": "user/counters/createBuckets/images/editBuckets",
             "name": "変更・削除",
             "comment": "<ul class=\"mb-0\"><li>カウンターを変更・削除できます。</li></ul>"
            }
        ]');
    }

    /**
     * バケツ作成
     */
    private function setLeftBuckets()
    {
        $this->addPluginModal('counters', '/', 1, false);

        Frame::where('area_id', 1)->where('plugin_name', 'menus')->update(['display_sequence' => 1]);
        $frame = Frame::where('area_id', 2)->where('plugin_name', 'counters')->first();
        Frame::where('area_id', 1)->where('plugin_name', 'counters')->update(['display_sequence' => 2, 'bucket_id' => $frame->bucket_id]);
    }

    /**
     * カウント一覧
     */
    private function listCounters()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            $browser->visit('/plugin/counters/listCounters/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->assertPathBeginsWith('/')
                    ->screenshot('user/counters/listCounters/images/listCounters');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/counters/listCounters/images/listCounters",
             "comment": "<ul class=\"mb-0\"><li>日ごとのカウントが一覧で確認できます。</li><li>一覧は30件（1ヵ月分を想定）で、ページ送りします。</li></ul>"
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
            $browser->visit('/plugin/counters/listBuckets/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->assertPathBeginsWith('/')
                    ->screenshot('user/counters/listBuckets/images/listBuckets');
//                    ->press("表示カウンター変更");
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/counters/listBuckets/images/listBuckets",
             "comment": "<ul class=\"mb-0\"><li>表示するカウンターを変更できます。</li></ul>"
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
            $browser->visit('/plugin/counters/editView/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->screenshot('user/counters/editView/images/editView');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/counters/editView/images/editView",
             "comment": "<ul class=\"mb-0\"><li>カウンターの表示形式や累計、本日、昨日の表示などを設定できます。</li></ul>"
            }
        ]');
    }
}
