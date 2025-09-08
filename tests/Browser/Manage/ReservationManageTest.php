<?php

namespace Tests\Browser\Manage;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Artisan;

use App\Models\User\Reservations\Reservation;
use App\Models\User\Reservations\ReservationsCategory;
use App\Models\User\Reservations\ReservationsChoiceCategory;
use App\Models\User\Reservations\ReservationsColumn;
use App\Models\User\Reservations\ReservationsColumnsSelect;
use App\Models\User\Reservations\ReservationsColumnsSet;
use App\Models\User\Reservations\ReservationsFacility;
use App\Models\User\Reservations\ReservationsInput;
use App\Models\User\Reservations\ReservationsInputsColumn;

use App\Traits\ConnectCommonTrait;

class ReservationManageTest extends DuskTestCase
{
    use ConnectCommonTrait;

    /**
     * テストする関数の制御
     *
     * @group manage
     * @see https://readouble.com/laravel/6.x/ja/dusk.html#running-tests
     */
    public function testInvoke()
    {
        $this->init();
        $this->login(1);
        $this->categories();
        $this->regist();
        $this->registOther("中会議室", "小会議室１", "小会議室２", "プロジェクタ", "ドローンセットＡ", "ドローンセットＢ");
        $this->columnSets();
        $this->registColumnSet();
        $this->bookings();
        $this->index();
    }

    /**
     * 初期処理
     */
    private function init()
    {
        // データクリア（施設データを作り直すので、予約データもクリアする）
        Reservation::truncate();
        ReservationsCategory::truncate();
        ReservationsChoiceCategory::truncate();
        ReservationsColumn::truncate();
        ReservationsColumnsSelect::truncate();
        ReservationsColumnsSet::truncate();
        ReservationsFacility::truncate();
        ReservationsInput::truncate();
        ReservationsInputsColumn::truncate();

        // 初期データをseeder から登録
        Artisan::call('db:seed', ['--class'=> 'DefaultReservationsTableSeeder']);

        // 最初にマニュアルの順番確定用にメソッドを指定する。
        $this->reserveManual('index', 'regist', 'categories', 'columnSets', 'registColumnSet', 'bookings');
    }

    /**
     * 施設一覧
     */
    private function index()
    {
        // ブラウザ操作
        $this->browse(function (Browser $browser) {
            $browser->visit('/manage/reservation')
                    ->assertPathBeginsWith('/')
                    ->screenshot('manage/reservation/index/images/index');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "manage/reservation/index/images/index",
             "name": "施設一覧",
             "comment": "<ul class=\"mb-0\"><li>登録されている施設を一覧表示できます。</li></ul>"
            }
        ]', null, 3);
    }

    /**
     * 施設登録
     */
    private function regist()
    {
        // ブラウザ操作
        $this->browse(function (Browser $browser) {
            $browser->visit('/manage/reservation/regist')
                    ->type('facility_name', '大会議室')
                    ->select('reservations_categories_id', '2')
                    ->select('columns_set_id', '1')
                    ->screenshot('manage/reservation/regist/images/regist1');

            $browser->scrollIntoView('footer')
                    ->screenshot('manage/reservation/regist/images/regist2')
                    ->press('登録確定')
                    ->assertSee('大会議室');    // 500エラーでも正常終了していたため、チェック追加
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "manage/reservation/regist/images/regist1",
             "name": "施設登録１"
            },
            {"path": "manage/reservation/regist/images/regist2",
             "name": "施設登録２",
             "comment": "<ul class=\"mb-0\"><li>施設を登録します。</li></ul>"
            }
        ]', null, 3);
    }

    /**
     * 施設登録その他
     */
    private function registOther(...$names)
    {
        // ブラウザ操作
        $this->browse(function (Browser $browser) use ($names) {
            foreach ($names as $name) {

                $reservations_category = '2';
                if (strpos($name, 'ドローン') !== false) {
                    $reservations_category = '3';
                }

                $browser->visit('/manage/reservation/regist')
                        ->waitForText('登録確定')
                        ->type('facility_name', $name)
                        ->select('reservations_categories_id', $reservations_category)
                        ->select('columns_set_id', '1')
                        ->press('登録確定');
            }
        });
    }

    /**
     * 施設カテゴリ設定
     */
    private function categories()
    {
        // ブラウザ操作
        $this->browse(function (Browser $browser) {
            $browser->visit('/manage/reservation/categories')
                    ->type('add_display_sequence', '2')
                    ->type('add_category', '会議室')
                    ->press('変更');

            $browser->visit('/manage/reservation/categories')
                    ->type('add_display_sequence', '3')
                    ->type('add_category', 'ドローン')
                    ->press('変更');

            $browser->visit('/manage/reservation/categories')
                    ->screenshot('manage/reservation/categories/images/categories');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "manage/reservation/categories/images/categories",
             "name": "施設カテゴリ設定",
             "comment": "<ul class=\"mb-0\"><li>施設に紐づけるカテゴリを登録できます。</li></ul>"
            }
        ]', null, 3);
    }

    /**
     * 項目セット一覧
     */
    private function columnSets()
    {
        // ブラウザ操作
        $this->browse(function (Browser $browser) {
            $browser->visit('/manage/reservation/columnSets')
                    ->screenshot('manage/reservation/columnSets/images/columnSets');

            $browser->visit('/manage/reservation/editColumns/1')
                    ->screenshot('manage/reservation/columnSets/images/editColumns');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "manage/reservation/columnSets/images/columnSets",
             "name": "項目セット一覧",
             "comment": "<ul class=\"mb-0\"><li>項目セットの一覧を表示します。</li></ul>"
            },
            {"path": "manage/reservation/columnSets/images/editColumns",
             "name": "項目設定",
             "comment": "<ul class=\"mb-0\"><li>項目セットの項目を設定します。</li></ul>"
            }
        ]', null, 3);
    }

    /**
     * 項目セット登録
     */
    private function registColumnSet()
    {
        // ブラウザ操作
        $this->browse(function (Browser $browser) {
            $browser->visit('/manage/reservation/registColumnSet')
                    ->screenshot('manage/reservation/registColumnSet/images/registColumnSet');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "manage/reservation/registColumnSet/images/registColumnSet",
             "name": "項目セット登録",
             "comment": "<ul class=\"mb-0\"><li>項目セットを登録します。</li></ul>"
            }
        ]', null, 3);
    }

    /**
     * 予約一覧
     */
    private function bookings()
    {
        // 予約データ作成（ここではデータベースに直接データを作成。一般ユーザ画面のテストは後でプラグイン側で実施）
        // 実行日の第一水曜日(施設管理で平日のみ予約許可にしているので)
        // 後で一般ユーザ画面のテストで予約データは作り直されます。
        $monday_1 = new \DateTime('first Monday of ' . date('Y-m'));

        $reservations_input = ReservationsInput::create([
            "inputs_parent_id" => 1,
            "facility_id" => 1,
            "start_datetime" => $monday_1->format('Y-m-d 10:00'),
            "end_datetime" => $monday_1->format('Y-m-d 12:00'),
            "first_committed_at" => $monday_1->format('Y-m-d 09:00'),
            "status" => 0,
        ]);

        // テストのために複数代入を許可するのではなく、update で値をセット
        $reservations_input->created_id = 1;
        $reservations_input->created_name = "システム管理者";
        $reservations_input->created_at = $monday_1->format('Y-m-d 09:00');
        $reservations_input->updated_id = 1;
        $reservations_input->updated_name = "システム管理者";
        $reservations_input->updated_at = $monday_1->format('Y-m-d 09:00');
        $reservations_input->save();


        ReservationsInputsColumn::create([
            "inputs_parent_id" => 1,
            "facility_id" => 1,
            "column_id" => 1,
            "value" => "テストの予約①",
        ]);

        // ブラウザ操作
        $this->browse(function (Browser $browser) {
            $browser->visit('/manage/reservation/bookings')
                    ->screenshot('manage/reservation/bookings/images/bookings');
        });
        $this->browse(function (Browser $browser) {
            $browser->click('#app_reservation_search_condition')
                    ->waitForText('施設名')
                    ->screenshot('manage/reservation/bookings/images/bookings2');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "manage/reservation/bookings/images/bookings",
             "name": "予約一覧",
             "comment": "<ul class=\"mb-0\"><li>予約一覧の閲覧とCSVによるダウンロードが可能です。</li></ul>"
            },
            {"path": "manage/reservation/bookings/images/bookings2",
             "name": "絞り込み画面",
             "comment": "<ul class=\"mb-0\"><li>施設名と登録者で絞り込むことができます。</li><li>施設ID等で並べ替えすることができます。</li></ul>"
            }
        ]', null, 3);
    }
}
