<?php

namespace Tests\Browser\User;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

use App\Enums\PluginName;
use App\Models\Common\Buckets;
use App\Models\Common\Frame;
use App\Models\Common\Uploads;
use App\Models\User\Openingcalendars\Openingcalendars;
use App\Models\User\Openingcalendars\OpeningcalendarsDays;
use App\Models\User\Openingcalendars\OpeningcalendarsMonths;
use App\Models\User\Openingcalendars\OpeningcalendarsPatterns;

/**
 * 開館カレンダーテスト
 *
 * @see https://github.com/opensource-workshop/connect-cms/wiki/Dusk#テスト実行 [How to test]
 */
class OpeningcalendarsPluginTest extends DuskTestCase
{
    /**
     * 開館カレンダーテスト
     *
     * @group user
     * @see https://readouble.com/laravel/6.x/ja/dusk.html#running-tests
     */
    public function test()
    {
        $this->init();
        $this->login(1);

        $this->createBuckets();
        $this->listPatterns();
        $this->listBuckets();

        $this->edit();
        $this->editYearschedule();

        $this->logout();
        $this->index();   // 記事一覧
    }

    /**
     * 初期処理
     */
    private function init()
    {
        // データクリア
        Openingcalendars::truncate();
        OpeningcalendarsDays::truncate();
        OpeningcalendarsMonths::truncate();
        OpeningcalendarsPatterns::truncate();
        $this->initPlugin('openingcalendars', '/test/openingcalendar');

        // 最初にマニュアルの順番確定用にメソッドを指定する。
        $this->reserveManual('index', 'edit', 'editYearschedule', 'createBuckets', 'listPatterns', 'listBuckets');
    }

    /**
     * インデックス
     */
    private function index()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            $browser->visit('/test/openingcalendar')
                    ->assertPathBeginsWith('/')
                    ->screenshot('user/openingcalendars/index/images/index');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/openingcalendars/index/images/index",
             "name": "開館カレンダー",
             "comment": "<ul class=\"mb-0\"><li>共通的に表示される左エリアにも収まるサイズでカレンダー表示できます。</li></ul>"
            }
        ]');
    }

    /**
     * 予定登録
     */
    private function edit()
    {
        $calendar = Openingcalendars::first();

        // 当月のデータがなければ作成
        OpeningcalendarsMonths::updateOrCreate(
            ['openingcalendars_id' => $calendar->id, 'month' => date('Y-m')],
            ['openingcalendars_id' => $calendar->id, 'month' => date('Y-m')]
        );

        for ($i = 1; $i <= date('t'); $i++) {
            $ymd = date('Y-m-') . sprintf("%02d", $i);
            if ($i == 5 || $i == 12) {
                $pattern_id = 4;
            } elseif (date('w', strtotime($ymd)) == 0 && $i > 10 && $i < 20) {
                $pattern_id = 3;
            } elseif (date('w', strtotime($ymd)) == 0) {
                $pattern_id = 5;
            } elseif (date('w', strtotime($ymd)) == 6 && $i > 5 && $i < 15) {
                $pattern_id = 2;
            } elseif (date('w', strtotime($ymd)) == 6) {
                $pattern_id = 5;
            } else {
                $pattern_id = 1;
            }
            OpeningcalendarsDays::updateOrCreate(
                ['openingcalendars_id' => $calendar->id, 'opening_date' => $ymd],
                ['openingcalendars_id' => $calendar->id, 'opening_date' => $ymd, 'openingcalendars_patterns_id' => $pattern_id]
            );
        }

        // 実行
        $this->browse(function (Browser $browser) {
            $browser->visit('plugin/openingcalendars/edit/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->assertPathBeginsWith('/')
                    ->screenshot('user/openingcalendars/edit/images/edit1');

            $browser->scrollIntoView('footer')
                    ->screenshot('user/openingcalendars/edit/images/edit2');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/openingcalendars/edit/images/edit1"},
            {"path": "user/openingcalendars/edit/images/edit2",
             "comment": "<ul class=\"mb-0\"><li>月単位に登録します。最上部のすべて反映機能があるので、入力は容易です。</li></ul>"
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
            $browser->visit('/plugin/openingcalendars/createBuckets/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->assertPathBeginsWith('/')
                    ->type('openingcalendar_name', 'テストの開館カレンダー')
                    ->type('openingcalendar_sub_name', 'テスト')
                    ->select('month_format', '1')
                    ->select('week_format', '1')
                    ->type('view_before_month', '3')
                    ->type('view_after_month', '3')
                    ->screenshot('user/openingcalendars/createBuckets/images/createBuckets')
                    ->press('登録');

            // 一度、選択確定させる。
            $bucket = Buckets::where('plugin_name', 'openingcalendars')->first();
            $browser->visit('/plugin/openingcalendars/listBuckets/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->radio('select_bucket', $bucket->id)
                    ->assertPathBeginsWith('/')
                    ->press("表示開館カレンダー変更");

            // 変更
            $browser->visit('/plugin/openingcalendars/editBuckets/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->assertPathBeginsWith('/')
                    ->screenshot('user/openingcalendars/createBuckets/images/editBuckets');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/openingcalendars/createBuckets/images/createBuckets",
             "name": "作成",
             "comment": "<ul class=\"mb-0\"><li>新しい開館カレンダーを作成できます。</li></ul>"
            },
            {"path": "user/openingcalendars/createBuckets/images/editBuckets",
             "name": "変更・削除",
             "comment": "<ul class=\"mb-0\"><li>開館カレンダーを変更・削除できます。</li></ul>"
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
            $browser->visit('/plugin/openingcalendars/listBuckets/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->assertPathBeginsWith('/')
                    ->screenshot('user/openingcalendars/listBuckets/images/listBuckets');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/openingcalendars/listBuckets/images/listBuckets",
             "comment": "<ul class=\"mb-0\"><li>表示する開館カレンダーを変更できます。</li></ul>"
            }
        ]');
    }

    /**
     * パターン
     */
    private function listPatterns()
    {
        // データがあれば一度消す。最後の1行は画面で登録
        $calendar = Openingcalendars::first();
        OpeningcalendarsPatterns::truncate();

        $pattern = OpeningcalendarsPatterns::create([
            'openingcalendars_id' => $calendar->id,
            'caption' => '通常開館',
            'color' => '#ffffff',
            'pattern' => '9:30-17:00',
            'display_sequence' => 1
        ]);
        $pattern = OpeningcalendarsPatterns::create([
            'openingcalendars_id' => $calendar->id,
            'caption' => '土曜日開館',
            'color' => '#32cd32',
            'pattern' => '9:30-12:00',
            'display_sequence' => 2
        ]);
        $pattern = OpeningcalendarsPatterns::create([
            'openingcalendars_id' => $calendar->id,
            'caption' => '日曜日開館',
            'color' => '#1e90ff',
            'pattern' => '10:00-17:00',
            'display_sequence' => 3
        ]);
        $pattern = OpeningcalendarsPatterns::create([
            'openingcalendars_id' => $calendar->id,
            'caption' => '短縮開館',
            'color' => '#f08080',
            'pattern' => '11:00-16:00',
            'display_sequence' => 4
        ]);

        // 実行
        $this->browse(function (Browser $browser) {
            $browser->visit('/plugin/openingcalendars/listPatterns/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->type('add_display_sequence', '5')
                    ->type('add_caption', '閉館')
                    ->type('add_pattern', 'Closed')
                    ->type('add_color', '#c0c0c0')
                    ->assertPathBeginsWith('/')
                    ->screenshot('user/openingcalendars/listPatterns/images/listPatterns')
                    ->press('変更');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/openingcalendars/listPatterns/images/listPatterns",
             "comment": "<ul class=\"mb-0\"><li>開館パターンを自由に設定できます。</li></ul>"
            }
        ]');
    }

    /**
     * 年間カレンダー
     */
    private function editYearschedule()
    {
        $calendar = Openingcalendars::first();

        // 実行
        $this->browse(function (Browser $browser) use ($calendar) {
            $browser->visit('plugin/openingcalendars/editYearschedule/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '/' . $calendar->id . '#frame-' . $this->test_frame->id)
                    ->attach('yearschedule_pdf', __DIR__.'/openingcalendar/年間の開館カレンダーテスト用PDF.pdf')
                    ->type('yearschedule_link_text', '年間カレンダー')
                    ->assertPathBeginsWith('/')
                    ->press('アップロード')
                    ->screenshot('user/openingcalendars/editYearschedule/images/editYearschedule1');

            $browser->visit('/test/openingcalendar')
                    ->assertPathBeginsWith('/')
                    ->screenshot('user/openingcalendars/editYearschedule/images/editYearschedule2');
        });

        // マニュアル用データ出力(記事の登録はしていなくても、画像データはできているはず。reserveManual() で一旦、内容がクリアされているので、画像の登録は行う)
        $this->putManualData('[
            {"path": "user/openingcalendars/editYearschedule/images/editYearschedule1",
             "comment": "<ul class=\"mb-0\"><li>年間カレンダーはPDFをアップロードします。</li></ul>"
            },
            {"path": "user/openingcalendars/editYearschedule/images/editYearschedule2",
             "comment": "<ul class=\"mb-0\"><li>年間カレンダーはカレンダーの右上の編集アイコンから登録できます。</li></ul>"
            }
        ]');
    }
}
