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
use App\Models\User\Calendars\Calendar;
use App\Models\User\Calendars\CalendarPost;

/**
 * カレンダーテスト
 *
 * @see https://github.com/opensource-workshop/connect-cms/wiki/Dusk#テスト実行 [How to test]
 */
class CalendarsPluginTest extends DuskTestCase
{
    /**
     * ブログテスト
     *
     * @group user
     * @see https://readouble.com/laravel/6.x/ja/dusk.html#running-tests
     */
    public function testBlog()
    {
        // 最初にマニュアルの順番確定用にメソッドを指定する。
        $this->reserveManual('index', 'show', 'edit', 'template', 'createBuckets', 'listBuckets');

        $this->login(1);

        // プラグインが配置されていなければ追加(テストするFrameとページのインスタンス変数への保持も)
        $this->addPluginFirst('calendars', '/test/calendar', 2);

        $this->createBuckets();
        $this->listBuckets();

        $this->edit();

        $this->logout();
        $this->index();    // 記事一覧
        $this->show();     // 記事詳細
        $this->template(); // テンプレート
    }

    /**
     * インデックス
     */
    private function index()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            $browser->visit('/test/calendar')
                    ->assertPathBeginsWith('/')
                    ->screenshot('user/calendars/index/images/index1');

            $browser->resize(400, 800);

            $browser->visit('/test/calendar')
                    ->assertPathBeginsWith('/')
                    ->screenshot('user/calendars/index/images/index2');

            $browser->resize(1280, 800);
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/calendars/index/images/index1",
             "name": "月表示",
             "comment": "<ul class=\"mb-0\"><li>月表示のカレンダー上に予定が表示されます。</li></ul>"
            },
            {"path": "user/calendars/index/images/index2",
             "name": "月表示（スマホ表示）",
             "comment": "<ul class=\"mb-0\"><li>スマートフォンの場合は縦長のカレンダーに予定が表示されます。</li></ul>"
            }
        ]');
    }

    /**
     * 予定登録
     */
    private function edit($title = null)
    {
        // ブログ（バケツ）があって且つ、その月に記事が3件未満の場合に記事作成
        $ym = date("Y-m");
        CalendarPost::where('start_date', 'like', $ym . '%')->delete();

        // 実行
        $this->browse(function (Browser $browser) use ($ym) {
            $browser->visit('plugin/calendars/edit/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '?date=' . $ym . '-01#frame-' . $this->test_frame->id)
                    ->assertPathBeginsWith('/')
                    ->type('title', 'テストの予定')
                    ->type('start_time', '10:00')
                    ->type('end_date', $ym . '-01')
                    ->type('end_time', '12:00')
                    ->screenshot('user/calendars/edit/images/edit1')
                    ->press('登録確定');

            $browser->visit('plugin/calendars/edit/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '?date=' . $ym . '-08#frame-' . $this->test_frame->id)
                    ->assertPathBeginsWith('/')
                    ->type('title', 'テストの予定２')
                    ->click('#label_allday_flag')
                    ->pause(500)
                    ->driver->executeScript('tinyMCE.get(0).setContent(\'この予定は全日予定です。\')');

            $browser->screenshot('user/calendars/edit/images/edit2')
                    ->press('登録確定');

            $browser->visit('plugin/calendars/edit/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '?date=' . $ym . '-20#frame-' . $this->test_frame->id)
                    ->assertPathBeginsWith('/')
                    ->type('title', 'テストの予定３')
                    ->type('start_time', '12:00')
                    ->type('end_date', $ym . '-21')
                    ->type('end_time', '18:00')
                    ->screenshot('user/calendars/edit/images/edit3')
                    ->press('登録確定');
        });

        // マニュアル用データ出力(記事の登録はしていなくても、画像データはできているはず。reserveManual() で一旦、内容がクリアされているので、画像の登録は行う)
        $this->putManualData('[
            {"path": "user/calendars/edit/images/edit1",
             "comment": "<ul class=\"mb-0\"><li>カレンダーに予定を登録できます。</li><li>タイトル、開始日時、終了日時、本文が登録できます。</li><li>全日予定にすると、時間のない予定として登録できます。</li></ul>"
            }
        ]');
    }

    /**
     * 予定詳細
     */
    private function show()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            // データ取得
            $post = CalendarPost::where('start_date', date("Y-m-01"))->first();
            $browser->visit('plugin/calendars/show/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '/' . $post->id . '#frame-' . $this->test_frame->id)
                    ->assertPathBeginsWith('/')
                    ->screenshot('user/calendars/show/images/show');
        });

        // マニュアル用データ出力
        $this->putManualData('user/calendars/show/images/show');
    }

    /**
     * バケツ作成
     */
    private function createBuckets()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            Calendar::truncate();
            Buckets::where('plugin_name', 'calendars')->delete();

            // 新規作成
            $browser->visit('/plugin/calendars/createBuckets/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->assertPathBeginsWith('/')
                    ->type('name', 'テストのカレンダー')
                    ->screenshot('user/calendars/createBuckets/images/createBuckets')
                    ->press('登録確定');

            // 一度、選択確定させる。
            $bucket = Buckets::where('plugin_name', 'calendars')->first();
            $browser->visit('/plugin/calendars/listBuckets/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->radio('select_bucket', $bucket->id)
                    ->assertPathBeginsWith('/')
                    ->press("表示カレンダー変更");

            // 変更
            $browser->visit("/plugin/calendars/editBuckets/" . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->assertPathBeginsWith('/')
                    ->screenshot('user/calendars/createBuckets/images/editBuckets');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/calendars/createBuckets/images/createBuckets",
             "name": "作成",
             "comment": "<ul class=\"mb-0\"><li>新しいカレンダーを作成できます。</li></ul>"
            },
            {"path": "user/calendars/createBuckets/images/editBuckets",
             "name": "変更・削除",
             "comment": "<ul class=\"mb-0\"><li>カレンダーを変更・削除できます。</li></ul>"
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
            $browser->visit('/plugin/calendars/listBuckets/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->assertPathBeginsWith('/')
                    ->screenshot('user/calendars/listBuckets/images/listBuckets');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/calendars/listBuckets/images/listBuckets",
             "comment": "<ul class=\"mb-0\"><li>表示するカレンダーを変更できます。</li></ul>"
            }
        ]');
    }

    /**
     * テンプレート
     */
    private function template()
    {
        Dusks::where('plugin_name', 'calendars')->where('method_name', 'template')->delete();
        $this->putManualTemplateData(
            $this->test_frame,
            'user',
            '/test/calendar',
            ['calendars', 'カレンダー'],
            ['day' => '日表示', 'small_month' => '月表示（小）', 'breadcrumbs' => 'パンくず', 'sitemap' => 'サイトマップ']
        );
    }
}
