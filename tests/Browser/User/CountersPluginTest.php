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
        $this->insertCounter();
        $this->editView();
        $this->listCounters();
        $this->listBuckets();

        // 左エリアの下にもカウンターを置く。バケツはメインエリアと同じものを参照。順番は、上にメニュー、下にカウンター
        $this->addPluginModal('counters', '/', 1, false);
        Frame::where('area_id', 1)->where('plugin_name', 'menus')->update(['display_sequence' => 1]);
        $frame = Frame::where('area_id', 2)->where('plugin_name', 'counters')->first();
        Frame::where('area_id', 1)->where('plugin_name', 'counters')->update(['display_sequence' => 2, 'bucket_id' => $frame->bucket_id]);
        $left_frame = Frame::where('area_id', 1)->where('plugin_name', 'counters')->first();
        $left_counter_frame = CounterFrame::first()->replicate()->fill(['frame_id' => $left_frame->id]);
        $left_counter_frame->save();

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
     * カウンターデータの生成
     */
    private function insertCounter()
    {
        // 過去31日分を生成する。固定の数（画面キャプチャをテストの度に、全部入れ替えにならないように）。
        $day_count = [20, 101, 91, 51, 91, 102, 43, 77, 65, 108, 204, 54, 26, 87, 27, 65, 90, 64, 62, 18, 19, 64, 250, 64, 21, 63, 68, 32, 65, 29, 87];
        $total_count = 0;
        for ($i = count($day_count) - 1; $i >= 0; $i--) {
            $total_count = $total_count + $day_count[$i];
            CounterCount::updateOrCreate(
                ['counted_at' => date("Y-m-d")],
                [
                    "counter_id" => 1,
                    "counted_at" => date("Y-m-d", strtotime("-" . $i . " day")),
                    "day_count" => $day_count[$i],
                    "total_count" => $total_count,
                ]
            );
        }
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
                    ->screenshot('user/counters/listCounters/images/listCounters1');

            // フッタをキャプチャ
            $browser->scrollIntoView('footer')
                    ->screenshot('user/counters/listCounters/images/listCounters2');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/counters/listCounters/images/listCounters1"},
            {"path": "user/counters/listCounters/images/listCounters2",
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
                    ->screenshot('user/counters/editView/images/editView1');

            // フッタをキャプチャ
            $browser->scrollIntoView('footer')
                    ->screenshot('user/counters/editView/images/editView2');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/counters/editView/images/editView1"},
            {"path": "user/counters/editView/images/editView2",
             "comment": "<ul class=\"mb-0\"><li>カウンターの表示形式や累計、本日、昨日の表示などを設定できます。</li></ul>"
            }
        ]');
    }
}
