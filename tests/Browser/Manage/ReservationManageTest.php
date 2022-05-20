<?php

namespace Tests\Browser\Manage;

use Illuminate\Foundation\Testing\DatabaseMigrations;
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
        $this->reserveManual('index', 'regist', 'categories', 'columnSets', 'registColumnSet');
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
                    ->press('登録確定');
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
                    ->press('変更')
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
}
