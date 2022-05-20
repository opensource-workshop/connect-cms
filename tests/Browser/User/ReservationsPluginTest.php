<?php

namespace Tests\Browser\User;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

use App\Enums\PluginName;
use App\Models\Common\Buckets;
use App\Models\Common\Uploads;
use App\Models\Core\Dusks;
use App\Models\User\Reservations\Reservation;
use App\Models\User\Reservations\ReservationsCategory;
use App\Models\User\Reservations\ReservationsChoiceCategory;
use App\Models\User\Reservations\ReservationsColumn;
use App\Models\User\Reservations\ReservationsColumnsSelect;
use App\Models\User\Reservations\ReservationsColumnsSet;
use App\Models\User\Reservations\ReservationsFacility;
use App\Models\User\Reservations\ReservationsInput;
use App\Models\User\Reservations\ReservationsInputsColumn;

/**
 * 施設予約テスト
 *
 * @see https://github.com/opensource-workshop/connect-cms/wiki/Dusk#テスト実行 [How to test]
 */
class ReservationsPluginTest extends DuskTestCase
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
        $this->choiceFacilities();
        $this->editView();
        $this->listBuckets();

        $this->editBooking();

        $this->logout();
        $this->index();
        $this->showBooking();
    }

    /**
     * 初期処理
     */
    private function init()
    {
        // データクリア（施設などは管理画面テストでクリアする。ここでは予約データのみ）
        Reservation::truncate();
        ReservationsInput::truncate();
        ReservationsInputsColumn::truncate();
        ReservationsChoiceCategory::truncate();
        $this->initPlugin('reservations', '/test/reservation');

        // 最初にマニュアルの順番確定用にメソッドを指定する。
        $this->reserveManual('index', 'showBooking', 'editBooking', 'createBuckets', 'editView', 'choiceFacilities', 'listBuckets');
    }

    /**
     * インデックス
     */
    private function index()
    {
        // ブラウザ
        $this->browse(function (Browser $browser) {
            $browser->visit('/test/reservation')
                    ->assertPathBeginsWith('/')
                    ->screenshot('user/reservations/index/images/index_month');

            $browser->clickLink('週')
                    ->screenshot('user/reservations/index/images/index_week');

//            $browser->clickLink('16:00~17:00 テストの予約')
//                    ->screenshot('user/reservations/index/images/bookingDetailModal');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/reservations/index/images/index_month",
             "name": "予約の一覧（月表示）",
             "comment": "<ul class=\"mb-0\"><li>月のカレンダー形式で予約を表示します。</li></ul>"
            },
            {"path": "user/reservations/index/images/index_week",
             "name": "予約の一覧（週表示）",
             "comment": "<ul class=\"mb-0\"><li>週形式で予約を表示します。</li></ul>"
            }
        ]');
/*
,
            {"path": "user/reservations/index/images/bookingDetailModal",
             "name": "予約の確認",
             "comment": "<ul class=\"mb-0\"><li>予約をクリックすることで、内容を確認できます。</li></ul>"
            }
*/
    }

    /**
     * 予約登録
     */
    private function editBooking()
    {
        // ブラウザ
        $this->browse(function (Browser $browser) {
            $browser->visit('/test/reservation')
                    ->screenshot('user/reservations/editBooking/images/index');

            // 実行日の第一月曜日(施設管理で平日のみ予約許可にしているので)
            $monday_1 = new \DateTime('first Monday of ' . date('Y-m'));

            $browser->visit('plugin/reservations/editBooking/' . $this->test_frame->page_id . '/' . $this->test_frame->id .
                            '?facility_id=1&target_date=' . $monday_1->format('Y-m-d') . '#frame-' . $this->test_frame->id)
                    ->type('start_datetime', '10:00')
                    ->type('end_datetime', '12:00')
                    ->type('columns_value[1]', 'テストの予約①')
                    ->screenshot('user/reservations/editBooking/images/editBooking')
                    ->press('登録確定')
                    ->screenshot('user/reservations/editBooking/images/editBooking2');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/reservations/editBooking/images/editBooking",
             "name": "施設予約の登録",
             "comment": "<ul class=\"mb-0\"><li>予約日、時間、繰り返し、件名を入力して登録します。</li></ul>"
            }
        ]');
    }

    /**
     * 予約詳細
     */
    private function showBooking()
    {
        // 最新の予約を取得
        $post = ReservationsInput::orderBy('id', 'desc')->first();

        // ブラウザ
        $this->browse(function (Browser $browser) use ($post) {
            $browser->visit('plugin/reservations/showBooking/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '/' . $post->id . '#frame-' . $this->test_frame->id)
                    ->screenshot('user/reservations/showBooking/images/showBooking');
        });

        // マニュアル用データ出力
        $this->putManualData('user/reservations/showBooking/images/showBooking');
    }

    /**
     * バケツ作成
     */
    private function createBuckets()
    {
        // ブラウザ
        $this->browse(function (Browser $browser) {
            // 新規作成
            $browser->visit('/plugin/reservations/createBuckets/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->type('reservation_name', 'テストの施設予約')
                    ->screenshot('user/reservations/createBuckets/images/createBuckets')
                    ->press('登録確定');

            // 一度、選択確定させる。
            $bucket = Buckets::where('plugin_name', 'reservations')->first();
            $browser->visit('/plugin/reservations/listBuckets/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->radio('select_bucket', $bucket->id)
                    ->press("表示する施設予約を変更")
                    ->screenshot('user/reservations/createBuckets/images/listBuckets');

            // 変更
            $browser->visit("/plugin/reservations/editBuckets/" . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->pause(500)
                    ->screenshot('user/reservations/createBuckets/images/editBuckets');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/reservations/createBuckets/images/createBuckets",
             "name": "作成",
             "comment": "<ul class=\"mb-0\"><li>新しい施設予約を作成できます。</li></ul>"
            },
            {"path": "user/reservations/createBuckets/images/editBuckets",
             "name": "変更・削除",
             "comment": "<ul class=\"mb-0\"><li>施設予約を変更・削除できます。</li></ul>"
            }
        ]');
    }


    /**
     * 施設設定
     */
    private function choiceFacilities()
    {
        // ブラウザ
        $this->browse(function (Browser $browser) {
            // チェックボックスがBootstrap拡張＆ラベル文字がないのでクリックできない。そのため、データを作る。
            ReservationsChoiceCategory::create(['reservations_id' => 1, 'reservations_categories_id' => 1, 'view_flag' => 0, 'display_sequence' => 1]);
            ReservationsChoiceCategory::create(['reservations_id' => 1, 'reservations_categories_id' => 2, 'view_flag' => 1, 'display_sequence' => 2]);
            ReservationsChoiceCategory::create(['reservations_id' => 1, 'reservations_categories_id' => 3, 'view_flag' => 1, 'display_sequence' => 3]);

            // 新規作成
            $browser->visit('/plugin/reservations/choiceFacilities/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->screenshot('user/reservations/choiceFacilities/images/choiceFacilities')
                    ->press('変更');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/reservations/choiceFacilities/images/choiceFacilities",
             "name": "施設設定",
             "comment": "<ul class=\"mb-0\"><li>表示する施設を選択します。</li></ul>"
            }
        ]');
    }

    /**
     * ブログ選択
     */
    private function listBuckets()
    {
        // ブラウザ
        $this->browse(function (Browser $browser) {
            $browser->visit('/plugin/reservations/listBuckets/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->assertPathBeginsWith('/')
                    ->screenshot('user/reservations/listBuckets/images/listBuckets');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/reservations/listBuckets/images/listBuckets",
             "comment": "<ul class=\"mb-0\"><li>表示する施設予約を変更できます。</li></ul>"
            }
        ]');
    }

    /**
     * フレーム表示設定
     */
    private function editView()
    {
        // ブラウザ
        $this->browse(function (Browser $browser) {
            $browser->visit('/plugin/reservations/editView/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->screenshot('user/reservations/editView/images/editView');

            $browser->click('#label_facility_display_type_only')
                    ->screenshot('user/reservations/editView/images/display_type_only');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/reservations/editView/images/editView",
             "name": "表示設定",
             "comment": "<ul class=\"mb-0\"><li>施設予約の表示形式を設定できます。</li></ul>"
            },
            {"path": "user/reservations/editView/images/display_type_only",
             "name": "１つの施設を選んで表示",
             "comment": "<ul class=\"mb-0\"><li>施設を選択できます。<br />この形式の場合は、予約一覧の表示画面では、施設の選択肢が出てきます。</li></ul>"
            }
        ]');
    }
}
