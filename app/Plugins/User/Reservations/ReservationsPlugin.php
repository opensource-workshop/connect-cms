<?php

namespace App\Plugins\User\Reservations;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

use App\Models\Common\Buckets;
use App\Models\Common\Frame;

use DB;

use App\Models\User\Reservations\Reservations;
use App\Models\User\Reservations\reservations_facilities;
use App\Models\User\Reservations\reservations_columns;
use App\Models\User\Reservations\reservations_columns_selects;
use App\Models\User\Reservations\reservations_inputs;
use App\Models\User\Reservations\reservations_inputs_columns;

use App\Plugins\User\UserPluginBase;

/**
 * 施設予約プラグイン
 *
 * @author 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 施設予約プラグイン
 * @package Contoroller
 */
class ReservationsPlugin extends UserPluginBase
{

    /* オブジェクト変数 */

    /**
     * 変更時のPOSTデータ
     */
    public $post = null;

    /* コアから呼び出す関数 */

    /**
     *  関数定義（コアから呼び出す）
     */
    public function getPublicFunctions()
    {
        // 標準関数以外で画面などから呼ばれる関数の定義
        $functions = array();
        $functions['get']  = [
            'week',
            'month',
            'editBucketsRoles',
            'editFacilities',
            'editColumns',
            'editColumnDetail',
        ];
        $functions['post'] = [
            'saveBucketsRoles',
            'addFacility',
            'updateFacility',
            'updateFacilitySequence',
            'addColumn',
            'updateColumn',
            'updateColumnSequence',
            'addSelect',
            'updateSelect',
            'updateSelectSequence',
            'editBooking',
            'saveBooking',
            'destroyBooking',
        ];
        return $functions;
    }

    /**
     *  編集画面の最初のタブ（コアから呼び出す）
     *
     *  スーパークラスをオーバーライド
     */
    public function getFirstFrameEditAction()
    {
        return "editBuckets";
    }

    /**
     *  POST取得関数（コアから呼び出す）
     *  コアがPOSTチェックの際に呼び出す関数
     */
    public function getPost($id)
    {

        // 一度読んでいれば、そのPOSTを再利用する。
        if (!empty($this->post)) {
            return $this->post;
        }

        // POST を取得する。
        $this->post = null;
        // DB read

        return $this->post;
    }

    /* private関数 */

    /**
     *  紐づく施設予約ID とフレームデータの取得
     */
    private function getReservationsFrame($frame_id)
    {
        // Frame と紐づく施設データを取得
        $frame = DB::table('frames')
                 ->select('frames.*', 'reservations.id as reservations_id', 'reservations.*')
                 ->leftJoin('reservations', 'reservations.bucket_id', '=', 'frames.bucket_id')
                 ->where('frames.id', $frame_id)
                 ->first();

        return $frame;
    }

    /**
     *  フレームデータの取得
     */
    private function getFrame($frame_id)
    {
        return DB::table('frames')->select('frames.*')->where('frames.id', $frame_id)->first();
    }

    /**
     *  施設予約登録チェック設定
     */
    private function makeValidator($request)
    {
        // 項目のエラーチェック
        $validator = Validator::make($request->all(), [
            //'post_title' => ['required'],
            //'posted_at'  => ['required', 'date_format:Y-m-d H:i:s'],
            //'post_text'  => ['required'],
        ]);
        $validator->setAttributeNames([
            //'post_title' => 'タイトル',
            //'posted_at'  => '投稿日時',
            //'post_text'  => '本文',
        ]);
        return $validator;
    }

    /**
     *  要承認の判断
     */
    private function isApproval($frame_id)
    {
        return $this->buckets->needApprovalUser(Auth::user());
    }

    /* スタティック関数 */

    /**
     *  新着情報用メソッド
     */
    public static function getWhatsnewArgs()
    {

        // 戻り値('sql_method'、'link_pattern'、'link_base')

        $return[] = array();
        //$return[] = Reservations::where('日付など')
        //                        ->get(;
        //$return[] = 'show_page_frame_post';
        //$return[] = '/plugin/reservations/show';

        return $return;
    }

    /* 画面アクション関数 */

    /**
     *  予約追加処理
     */
    public function saveBooking($request, $page_id, $frame_id, $target_ymd)
    {
        // 認証チェック
        if (!Auth::check()) {
            return $this->view_error("403_inframe", null, 'ログインしてから操作してください。');
        }

        $target_ymd = $request->target_date;
        // URLパラメータチェック
        $year = substr($target_ymd, 0, 4);
        $month = substr($target_ymd, 4, 2);
        $day = substr($target_ymd, 6, 2);
        if (!checkdate($month, $day, $year)) {
            return $this->view_error("404_inframe", null, '日時パラメータ不正(' . $year . '/' . $month . '/' . $day . ')');
        }

        // バリデーション用の配列を生成（基本項目）
        $validationArray =
            [
                'start_datetime'  => ['required'],
                'end_datetime'  => ['required', 'after:start_datetime'],
            ]
        ;
        $attributeArray =
            [
                'start_datetime'  => '開始時間',
                'end_datetime'  => '終了時間',
            ]
        ;

        // バリデーション用の配列を生成（可変項目）
        $required_columns = reservations_columns::query()->where('reservations_id', $request->reservations_id)->whereNull('hide_flag')->where('required', \Required::on)->get();
        foreach ($required_columns as $column) {
            $key_str = 'columns_value.' . $column->id;
            $validationArray[$key_str] = ['required'];
            $attributeArray[$key_str] = $column->column_name;
        }

        // バリデーション定義
        $validator = Validator::make(
            $request->all(),
            $validationArray
        );
        $validator->setAttributeNames($attributeArray);

        // バリデーション実施、エラー時は予約画面へ戻る
        if ($validator->fails()) {
            return $this->editBooking($request, $page_id, $frame_id, $validator->errors());
        }

        // 施設データ
        $facility = reservations_facilities::query()->where('id', $request->facility_id)->first();

        // 予約ヘッダ 登録 ※予約IDがある場合は更新
        $reservations_inputs = $request->booking_id ?
            reservations_inputs::query()->where('id', $request->booking_id)->first() :
            new reservations_inputs();
        // 新規登録時のみの登録項目
        if (!$request->booking_id) {
            $reservations_inputs->reservations_id = $request->reservations_id;
            $reservations_inputs->facility_id = $request->facility_id;
            $reservations_inputs->input_user_id = Auth::user()->userid;
        }
        $reservations_inputs->start_datetime = new Carbon($target_ymd . ' ' . $request->start_datetime . ':00');
        $reservations_inputs->end_datetime = new Carbon($target_ymd . ' ' . $request->end_datetime . ':00');
        $reservations_inputs->update_user_id = Auth::user()->userid;
        $reservations_inputs->save();

        // 項目IDを取得
        $keys = array_keys($request->columns_value);
        foreach ($keys as $key) {
            // 予約明細 更新レコード取得
            $reservations_inputs_columns = reservations_inputs_columns::query()
                    ->where('reservations_id', $request->reservations_id)
                    ->where('inputs_id', $reservations_inputs->id)
                    ->where('column_id', $key)
                    ->first();

            // 更新レコードが取得できなかったらnew
            if (!$reservations_inputs_columns) {
                $reservations_inputs_columns = new reservations_inputs_columns();
                // 新規登録時のみの登録項目
                $reservations_inputs_columns->reservations_id = $request->reservations_id;
                $reservations_inputs_columns->inputs_id = $reservations_inputs->id;
                $reservations_inputs_columns->column_id = $key;
            }
            $reservations_inputs_columns->value = $request->columns_value[$key];
            ;
            $reservations_inputs_columns->save();
        }
        $str_mode = $request->booking_id ? '更新' : '登録';
        $message = '予約を' . $str_mode . 'しました。【場所】' . $facility->facility_name . ' 【日時】' . date_format($reservations_inputs->start_datetime, 'Y年m月d日 H時i分') . ' ～ ' . date_format($reservations_inputs->end_datetime, 'H時i分');

        // 登録後はカレンダー表示
        return $this->index($request, $page_id, $frame_id, null, null, $message);
    }

    /**
     *  予約追加画面の表示
     */
    public function editBooking($request, $page_id, $frame_id, $errors = null)
    {
        if ($errors) {
            // エラーあり：入力値をフラッシュデータとしてセッションへ保存
            $request->flash();
        } else {
            // エラーなし：セッションから入力値を消去
            $request->flush();
        }

        // 認証チェック
        if (!Auth::check()) {
            return $this->view_error("403_inframe", null, 'ログインしてから操作してください。');
        }

        $booking = null;

        if ($request->booking_id) {

            /**
             * 予約の更新モード
             */

            // 予約データ
            $booking = reservations_inputs::query()->where('id', $request->booking_id)->first();

            // 施設予約データ
            $reservation = Reservations::query()->where('id', $booking->reservations_id)->first();

            // 施設データ
            $facility = reservations_facilities::query()->where('id', $booking->facility_id)->first();

            // 予約項目データ（予約入力値付）
            $columns = reservations_columns::query()
                    ->select(
                        'reservations_columns.*',
                        'reservations_inputs_columns.value',
                    )
                    ->leftjoin('reservations_inputs_columns', function ($join) use ($booking) {
                        $join->on('reservations_inputs_columns.column_id', '=', 'reservations_columns.id');
                        $join->where('reservations_inputs_columns.inputs_id', '=', $booking->id);
                    })
                    ->where('reservations_columns.reservations_id', $booking->reservations_id)
                    ->whereNull('reservations_columns.hide_flag')
                    ->orderBy('reservations_columns.display_sequence')
                    ->get()
                    ;

            // 予約項目データの内、選択肢が指定されていた場合の選択肢データ
            $selects = reservations_columns_selects::query()->where('reservations_id', $booking->reservations_id)->whereNull('hide_flag')->orderBy('id', 'asc')->orderBy('display_sequence', 'asc')->get();

            $target_date = new Carbon($booking->start_datetime);
        } else {
            /**
             * 予約の新規登録モード
             */
            // パラメータチェック
            $target_ymd = $request->target_date;
            $year = substr($target_ymd, 0, 4);
            $month = substr($target_ymd, 4, 2);
            $day = substr($target_ymd, 6, 2);
            if (!checkdate($month, $day, $year)) {
                return $this->view_error("404_inframe", null, '日時パラメータ不正(' . $year . '/' . $month . '/' . $day . ')');
            }

            // 施設予約データ
            $reservation = Reservations::query()->where('id', $request->reservations_id)->first();

            // 施設データ
            $facility = reservations_facilities::query()->where('id', $request->facility_id)->first();

            // 予約項目データ
            $columns = reservations_columns::query()->where('reservations_id', $request->reservations_id)->whereNull('hide_flag')->orderBy('display_sequence')->get();

            // 予約項目データの内、選択肢が指定されていた場合の選択肢データ
            $selects = reservations_columns_selects::query()->where('reservations_id', $request->reservations_id)->whereNull('hide_flag')->orderBy('id', 'asc')->orderBy('display_sequence', 'asc')->get();

            $target_date = new Carbon($target_ymd);
        }

        return $this->view(
            'reservations_calendar_edit_booking',
            [
                'target_date' => $target_date,
                'reservation' => $reservation,
                'facility' => $facility,
                'columns' => $columns,
                'selects' => $selects,
                'booking' => $booking,
                'errors'      => $errors,
            ]
        );
    }

    /**
     *  データ初期表示関数
     *  コアがページ表示の際に呼び出す関数
     */
    public function index($request, $page_id, $frame_id, $view_format = null, $carbon_target_date = null, $message = null)
    {
        // 施設予約＆フレームデータ
        $reservations_frame = $this->getReservationsFrame($frame_id);
        if (empty($reservations_frame)) {
            return;
        }

        // カレンダー表示タイプの指定がない場合はフレームに紐づくコンテンツの初期表示設定で表示する
        if (empty($view_format)) {
            $view_format = $reservations_frame->calendar_initial_display_type;
        }

        // 予約データ
        $reservations = reservations::query()->where('id', $reservations_frame->reservations_id)->first();

        // 施設データ
        $facilities = reservations_facilities::query()->where('reservations_id', $reservations_frame->reservations_id)->whereNull('hide_flag')->orderBy('display_sequence')->get();

        // 予約項目データ
        $columns = reservations_columns::query()->where('reservations_id', $reservations_frame->reservations_id)->whereNull('hide_flag')->orderBy('display_sequence')->get();

        // 予約項目データの内、選択肢が指定されていた場合の選択肢データ
        $selects = reservations_columns_selects::query()->where('reservations_id', $reservations_frame->reservations_id)->orderBy('id', 'asc')->orderBy('display_sequence', 'asc')->get();

        // 予約項目データの内、選択肢が指定されていた場合に選択肢データが登録済みかチェック
        $isExistSelect = true;
        $filtered_columns = $columns->filter(function ($column) {
            // 選択肢が設定可能なデータ型のみ抽出
            return $column->column_type == \ReservationColumnType::radio;
        });
        foreach ($filtered_columns as $column) {
            $filtered_selects = $selects->filter(function ($select) use ($column) {
                return $column->id == $select->column_id;
            });
            if ($filtered_selects->isEmpty()) {
                $isExistSelect = false;
            }
        }

        // 対象日時未設定（初期表示）の場合は現在日時をセット
        if (empty($carbon_target_date)) {
            $carbon_target_date = Carbon::today();
        }

        /**
         * カレンダー表示データの生成
         */
        $dates = [];
        $search_start_date = null;
        $search_end_date = null;

        if ($view_format == \ReservationCalendarDisplayType::month) {

            /**
             * 月表示用のデータ
             */
            $firstDay = new Carbon("$carbon_target_date->year-$carbon_target_date->month-01");
            // カレンダーを四角形にするため、前月となる左上の隙間用のデータを入れるためずらす
            $firstDay->subDay($firstDay->dayOfWeek);
            // 35マス（7列×5行）で収まらない場合の加算日数の算出
            $addDay =
                // 当月の日数が31日、且つ、前の月末日が木曜か金曜の場合
                $carbon_target_date->copy()->endOfmonth()->day == 31 && ($firstDay->copy()->endOfmonth()->isThursday() || $firstDay->copy()->endOfmonth()->isFriday()) ||
                // 当月の日数が30日、且つ、前の月末日が金曜の場合
                $carbon_target_date->copy()->endOfmonth()->day == 30 && ($firstDay->copy()->endOfmonth()->isFriday())
                ? 7 : 0;
            // 当月の月末日以降の処理
            $count = 31 + $addDay;
            $count =  ceil($count / 7) * 7;
            // dd("addDay：$addDay","カレンダー1日目：$firstDay","カレンダー1日目の曜日：$firstDay->dayOfWeek","count:$count");
    
            for ($i = 0; $i < $count; $i++, $firstDay->addDay()) {
                $dates[] = $firstDay->copy();
            }
        } else {

            /**
             * 週表示用のデータ
             */
            $firstDay = $carbon_target_date->copy();
            for ($i = 0; $i < 7; $i++, $firstDay->addDay()) {
                $dates[] = $firstDay->copy();
            }
        }

        // 予約データを検索する為の条件生成
        $search_start_date = $dates[0];
        $search_end_date = end($dates)->endOfDay();

        /**
         * カレンダー情報は入れ子の連想配列で返却する
         * calendars['施設名'] : 施設データ
         * calendars['calendar_cells'] : カレンダーセルデータの連想配列
         *   calendar_cell['date'] : Carbon日付データ
         *   calendar_cell['bookings'] : 予約データの連想配列
         *     calendar_cell['booking_header'] : 予約データの親テーブル（reservations_inputs）情報
         *     calendar_cell['booking_details'] : 予約データの子テーブル（reservations_inputs_columns）情報
         */
        $calendars = null;
        // 施設毎に予約情報を付加したカレンダーデータを生成
        // $time_start = microtime(true); //debug用
        foreach ($facilities as $facility) {
            $calendar = null;
            $calendar_cells = null;
            $calendar['facility'] = $facility;

            // カレンダー表示期間内で該当施設に紐づく予約データを抽出
            $bookingHeaders = reservations_inputs::query()
                ->where('reservations_id', $reservations->id)
                ->where('facility_id', $facility->id)
                ->whereBetween('start_datetime', [$search_start_date, $search_end_date])
                ->orderBy('start_datetime')
                ->get();

            foreach ($dates as $date) {
                $calendar_cell = null;
                // セルの日付に日付データを追加
                $calendar_cell['date'] = $date;
                // 日付データと予約データを突き合わせて該当日に予約データを付加
                foreach ($bookingHeaders as $bookingHeader) {
                    if ($date->format('Ymd') == $bookingHeader->start_datetime->format('Ymd')) {
                        // セルの予約配列に予約データを追加
                        $booking = null;
                        $booking['booking_header'] = $bookingHeader;
                        $booking['booking_details'] = reservations_inputs_columns::query()
                            ->leftjoin('reservations_columns', function ($join) {
                                $join->on('reservations_inputs_columns.column_id', '=', 'reservations_columns.id');
                            })
                            ->where('reservations_inputs_columns.reservations_id', $reservations->id)
                            ->where('inputs_id', $bookingHeader->id)
                            ->orderBy('reservations_inputs_columns.column_id')
                            ->get();

                        $calendar_cell['bookings'][] = $booking;
                    }
                }
                // パフォーマンス比較（10施設30予約）したところ、大して変わらないので初期の実装コード↑を採用
                // $calendar_cell['bookings'] = $bookings->filter(function($booking) use($date) {
                //     return $booking['start_datetime']->format('Ymd') == $date->format('Ymd');
                // });

                $calendar_cells[] = $calendar_cell;
            }
            $calendar['calendar_cells'] = $calendar_cells;
            $calendars[$facility->facility_name] = $calendar;
        }

        // $time = microtime(true) - $time_start;  //debug用
        // dd($time . '秒');  //debug用
        // dd($calendars);  //debug用
        return $this->view(
            'reservations_calendar_common',
            [
            'view_format' => $view_format,
            'carbon_target_date' => $carbon_target_date,
            'reservations' => $reservations,
            'facilities' => $facilities,
            'columns' => $columns,
            'selects' => $selects,
            'isExistSelect' => $isExistSelect,
            'calendars' => $calendars,
            'message' => $message,
            ]
        );
    }

    /**
     *  週表示関数
     */
    public function week($request, $page_id, $frame_id, $target_ymd)
    {
        $year = substr($target_ymd, 0, 4);
        $month = substr($target_ymd, 4, 2);
        $day = substr($target_ymd, 6, 2);
        if (!checkdate($month, $day, $year)) {
            return $this->view_error("404_inframe", null, '日時パラメータ不正(' . $year . '/' . $month . '/' . $day . ')');
        }
        $carbon_target_date = new Carbon("$target_ymd");
        return $this->index($request, $page_id, $frame_id, \ReservationCalendarDisplayType::week, $carbon_target_date, null);
    }

    /**
     *  月表示関数
     */
    public function month($request, $page_id, $frame_id, $target_ym)
    {
        $year = substr($target_ym, 0, 4);
        $month = substr($target_ym, 4, 2);
        if (!checkdate($month, '01', $year)) {
            return $this->view_error("404_inframe", null, '日時パラメータ不正(' . $year . '/' . $month . ')');
        }
        $carbon_target_date = new Carbon("$year-$month-01");
        return $this->index($request, $page_id, $frame_id, \ReservationCalendarDisplayType::month, $carbon_target_date, null);
    }

    /**
     *  新規予約画面
     */
    public function create($request, $page_id, $frame_id, $id = null, $errors = null)
    {
        // セッション初期化などのLaravel 処理。
        $request->flash();

        // 施設予約＆フレームデータ
        $reservations_frame = $this->getReservationsFrame($frame_id);

        // 空のデータ(画面で初期値設定で使用するため)
        //$reservations = new Reservations();
        //$reservations->posted_at = date('Y-m-d H:i:s');

        // 表示テンプレートを呼び出す。(blade でold を使用するため、withInput 使用)
        return $this->view(
            'reservations_input',
            [
            'reservation_frame' => $reservation_frame,
            'errors'            => $errors,
            ]
        )->withInput($request->all);
    }

    /**
     *  詳細表示関数
     */
    public function show($request, $page_id, $frame_id, $id = null)
    {
        // Frame データ
        $reservations_frame = $this->getReservationsFrame($frame_id);

        // 予約取得
        $reservation = $this->getPost($id);
        if (empty($reservation)) {
            return $this->view_error("403_inframe", null, 'show データなし');
        }

        // 詳細画面を呼び出す。
        return $this->view(
            'reservations_show',
            [
            'reservations_frame'  => $reservations_frame,
            'reservation'         => $reservation,
            ]
        );
    }

    /**
     * 予約編集画面
     */
    public function edit($request, $page_id, $frame_id, $id = null, $errors = null)
    {
        // セッション初期化などのLaravel 処理。
        $request->flash();

        // Frame データ
        $reservations_frame = $this->getReservationsFrame($frame_id);

        // 予約取得
        $reservation = $this->getPost($id);
        if (empty($reservation)) {
            return $this->view_error("403_inframe", null, 'edit データなし');
        }

        // 変更画面を呼び出す。(blade でold を使用するため、withInput 使用)
        return $this->view(
            'reservations_edit',
            [
            'reservations_frame'  => $reservations_frame,
            'reservation'         => $reservation,
            ]
        )->withInput($request->all);
    }

    /**
     *  予約登録処理
     */
    public function save($request, $page_id, $frame_id, $id = null)
    {
        // 項目のエラーチェック
        $validator = $this->makeValidator($request);

        // エラーがあった場合は入力画面に戻る。
        if ($validator->fails()) {
            return ( $this->create($request, $page_id, $frame_id, $id, $validator->errors()) );
        }

        // id があれば更新、なければ新規
        $reservations = null;

        if (!empty($id)) {
            //$reservations = Reservations::where('id', $id)->first();
        }
        if (empty($reservations)) {
            //$reservations = new Reservations();

            // 登録ユーザ
            //$reservations->created_id  = Auth::user()->id;
        }

        // 予約内容
        //$reservations->施設ID   = $request->施設ID;
        //$reservations->開始日時 = $request->開始日時;
        //$reservations->終了日時 = $request->終了日時;

        // 承認の要否確認とステータス処理
        if ($this->isApproval($frame_id)) {
            //$reservations->status = 2;
        }

        // 予約データ保存
        //$reservations->save();

        // 登録後は表示用の初期処理を呼ぶ。
        return $this->index($request, $page_id, $frame_id);
    }

    /**
     *  削除処理
     */
    public function delete($request, $page_id, $frame_id, $id)
    {
        // id がある場合、データを削除
        if ($id) {
            // データを削除する。
            //Reservations::where('id', $id)->delete();
        }
        // 削除後は表示用の初期処理を呼ぶ。
        return $this->index($request, $page_id, $frame_id);
    }

   /**
    * 承認
    */
    public function approval($request, $page_id = null, $frame_id = null, $id = null)
    {
        // id で予約データ取得、status を0 に更新

        // 承認後は表示用の初期処理を呼ぶ。
        return $this->index($request, $page_id, $frame_id);
    }

    /**
     * 表示コンテンツ選択画面の表示
     */
    public function listBuckets($request, $page_id, $frame_id, $id = null)
    {
        // Frame データ
        $reservation_frame = null;

        $reservation_frame = Frame::select('frames.*', 'reservations.id as reservations_id', 'reservations.*')
                           ->leftJoin('reservations', 'reservations.bucket_id', '=', 'frames.bucket_id')
                           ->where('frames.id', $frame_id)->first();

        // 施設予約の取得
        $query = Reservations::query();
        $query->select(
            'reservations.id',
            'reservations.bucket_id',
            'reservations.reservation_name',
            'reservations.calendar_initial_display_type',
            'reservations.created_at',
            DB::raw('GROUP_CONCAT(reservations_facilities.facility_name SEPARATOR \'\n\') as facility_names'),
        );
        $query->leftjoin('reservations_facilities', function ($join) {
            $join->on('reservations.id', '=', 'reservations_facilities.reservations_id');
        });
        $query->groupBy(
            'reservations.id',
            'reservations.bucket_id',
            'reservations.reservation_name',
            'reservations.calendar_initial_display_type',
            'reservations.created_at',
        );
        $query->orderBy('reservations.created_at', 'desc');
        $reservations = $query->paginate(10);

        // 表示テンプレートを呼び出す。
        return $this->view(
            'reservations_list_buckets',
            [
            'reservation_frame' => $reservation_frame,
            'reservations'      => $reservations,
            ]
        );
    }

    /**
     * 施設予約の新規作成画面の表示
     */
    public function createBuckets($request, $page_id, $frame_id, $id = null, $create_flag = false, $message = null, $errors = null)
    {
        // 設定変更画面を新規登録モードで呼び出す
        $create_flag = true;
        return $this->editBuckets($request, $page_id, $frame_id, $id, $create_flag, $message, $errors);
    }

    /**
     * 施設予約の設定画面の表示
     */
    public function editBuckets($request, $page_id, $frame_id, $reservations_id = null, $create_flag = false, $message = null, $errors = null)
    {
        // セッション初期化などのLaravel 処理
        $request->flash();

        // フレームデータ
        $reservation_frame = $this->getFrame($frame_id);

        // 施設データ
        $reservation = new Reservations();

        // id が渡ってくればid が対象
        if (!empty($reservations_id)) {
            $reservation = Reservations::where('id', $reservations_id)->first();
        }
        // Frame のbucket_id があれば、bucket_id から施設データ取得、なければ、新規作成か選択へ誘導
        elseif (!empty($reservation_frame->bucket_id) && $create_flag == false) {
            $reservation = Reservations::where('bucket_id', $reservation_frame->bucket_id)->first();
        }

        // 表示テンプレートを呼び出す。
        return $this->view(
            'reservations_edit',
            [
            'reservation_frame'  => $reservation_frame,
            'reservation'        => $reservation,
            'create_flag'        => $create_flag,
            'message'            => $message,
            'errors'             => $errors,
            ]
        )->withInput($request->all);
    }

    /**
     *  施設予約の登録・更新処理
     */
    public function saveBuckets($request, $page_id, $frame_id, $reservations_id = null)
    {
        // 項目のエラーチェック
        $validator = Validator::make($request->all(), [
            'reservation_name'  => ['required'],
            'calendar_initial_display_type'  => ['required'],
        ]);
        $validator->setAttributeNames([
            'reservation_name'  => '施設予約名',
            'calendar_initial_display_type'  => '初期表示設定',
        ]);

        // エラーがあった場合は入力画面に戻る。
        $message = null;
        if ($validator->fails()) {
            if (empty($reservations_id)) {
                $create_flag = true;
                return $this->createBuckets($request, $page_id, $frame_id, $reservations_id, $create_flag, $message, $validator->errors());
            } else {
                $create_flag = false;
                return $this->editBuckets($request, $page_id, $frame_id, $reservations_id, $create_flag, $message, $validator->errors());
            }
        }

        // 更新後のメッセージ
        $message = null;

        // 画面から渡ってくるid が空ならバケツと施設を新規登録
        if (empty($request->reservations_id)) {
            // バケツの登録
            $bucket_id = DB::table('buckets')->insertGetId([
                  'bucket_name' => $request->reservation_name,
                  'plugin_name' => 'reservations'
            ]);

            // 施設予約データ新規オブジェクト
            $reservations = new Reservations();
            $reservations->bucket_id = $bucket_id;

            // Frame のBuckets を見て、Buckets が設定されていなければ、作成したものに紐づける。
            // Frame にBuckets が設定されていない ＞ 新規のフレーム＆施設予約作成
            // Frame にBuckets が設定されている ＞ 既存のフレーム＆施設予約更新
            // （表示施設予約選択から遷移してきて、内容だけ更新して、フレームに紐づけないケースもあるため）
            $frame = Frame::where('id', $frame_id)->first();
            if (empty($frame->bucket_id)) {
                // FrameのバケツIDの更新
                $frame = Frame::where('id', $frame_id)->update(['bucket_id' => $bucket_id]);
            }

            $message = '施設予約の設定を追加しました。';
        }
        // id があれば、施設予約を更新
        else {
            // 施設予約データ取得
            $reservations = Reservations::where('id', $request->reservations_id)->first();

            $message = '施設予約の設定を変更しました。';
        }

        // 施設設定
        $reservations->reservation_name = $request->reservation_name;
        $reservations->calendar_initial_display_type = $request->calendar_initial_display_type;

        // データ保存
        $reservations->save();

        if (empty($request->reservations_id)) {
            // 新規登録後は、施設予約選択画面を呼び出す
            return $this->listBuckets($request, $page_id, $frame_id, null);
        } else {
            // 更新後は、設定変更画面を更新モードで呼び出す
            $create_flag = false;
            return $this->editBuckets($request, $page_id, $frame_id, $request->reservations_id, $create_flag, $message);
        }
    }

    /**
     *  コンテンツ削除
     */
    public function destroyBuckets($request, $page_id, $frame_id, $reservations_id)
    {
        // id がある場合、データを削除
        if ($reservations_id) {
            // TODO 子テーブルの削除

            // 施設予約を削除する。
            Reservations::query()->where('id', $reservations_id)->first()->delete();

            // バケツIDの取得のためにFrame を取得(Frame を更新する前に取得しておく)
            $frame = Frame::where('id', $frame_id)->first();

            // FrameのバケツIDの更新
            Frame::where('id', $frame_id)->update(['bucket_id' => null]);

            // backetsの削除
            Buckets::where('id', $frame->bucket_id)->delete();
        }
        // 削除処理はredirect 付のルートで呼ばれて、処理後はページの再表示が行われるため、ここでは何もしない。
    }

    /**
     *  予約削除
     */
    public function destroyBooking($request, $page_id, $frame_id)
    {
        $message = null;
        // id がある場合、データを削除
        if ($request->booking_id) {
            // 予約（子）を削除
            $input_columns = reservations_inputs_columns::query()->where('inputs_id', $request->booking_id)->get();
            foreach ($input_columns as $input_column) {
                $input_column->delete();
            }

            // 予約（親）、施設情報を取得してメッセージ修正
            $input = reservations_inputs::query()->where('id', $request->booking_id)->first();
            $facility = reservations_facilities::query()->where('id', $input->facility_id)->first();
            $message = '予約を削除しました。【場所】' . $facility->facility_name . ' 【日時】' . date_format($input->start_datetime, 'Y年m月d日 H時i分') . ' ～ ' . date_format($input->end_datetime, 'H時i分');

            // 予約（親）を削除
            $input->delete();
        }
        return $this->index($request, $page_id, $frame_id, null, null, $message);
    }

   /**
    * データ紐づけ変更関数
    */
    public function changeBuckets($request, $page_id = null, $frame_id = null, $id = null)
    {
        // FrameのバケツIDの更新
        Frame::where('id', $frame_id)
               ->update(['bucket_id' => $request->select_bucket]);

        // 表示施設予約選択画面を呼ぶ
        return $this->listBuckets($request, $page_id, $frame_id, $id);
    }

    /**
     * 施設の設定画面の表示
     */
    public function editFacilities($request, $page_id, $frame_id, $reservations_id = null, $message = null, $errors = null)
    {
        // --- 基本データの取得
        // 施設予約＆フレームデータ
        $reservation_frame = $this->getFrame($frame_id);

        // 施設データ
        $reservation = new Reservations();

        // id が渡ってくればid が対象
        if (!empty($reservations_id)) {
            $reservation = Reservations::where('id', $reservations_id)->first();
        }
        // Frame のbucket_id があれば、bucket_id から施設データ取得
        elseif (!empty($reservation_frame->bucket_id)) {
            $reservation = Reservations::where('bucket_id', $reservation_frame->bucket_id)->first();
        }

        // 施設予約データがない場合は0をセット
        $reservations_id = empty($reservation) ? null : $reservation->id;

        // --- 画面に値を渡す準備
        $facilities = reservations_facilities::query()->where('reservations_id', $reservations_id)->orderby('display_sequence')->get();

        // 編集画面テンプレートを呼び出す。
        return $this->view(
            'reservations_facilities_edit',
            [
            'reservations_id' => $reservations_id,
            'reservation'     => $reservation,
            'facilities'     => $facilities,
            'message'     => $message,
            'errors'     => $errors,
            ]
        );
    }

    /**
     * 予約項目の設定画面の表示
     */
    public function editColumnDetail($request, $page_id, $frame_id, $reservations_columns_id = null, $message = null, $errors = null)
    {
        if ($errors) {
            // エラーあり：入力値をフラッシュデータとしてセッションへ保存
            $request->flash();
        } else {
            // エラーなし：セッションから入力値を消去
            $request->flush();
        }

        // --- 基本データの取得
        // フレームデータ
        $reservation_frame = $this->getFrame($frame_id);

        // 施設データ
        $reservation = new Reservations();

        // Frame のbucket_id があれば、bucket_id から施設データ取得
        if (!empty($reservation_frame->bucket_id)) {
            $reservation = Reservations::where('bucket_id', $reservation_frame->bucket_id)->first();
        }

        // 施設予約データがない場合は0をセット
        $reservations_id = empty($reservation) ? null : $reservation->id;

        // --- 画面に値を渡す準備
        $column = reservations_columns::query()->where('id', $reservations_columns_id)->first();
        $selects = reservations_columns_selects::query()->where('column_id', $column->id)->orderby('display_sequence')->get();

        // 編集画面テンプレートを呼び出す。
        return $this->view(
            'reservations_columns_select_edit',
            [
            'reservations_id' => $reservations_id,
            'reservation'     => $reservation,
            'column'     => $column,
            'selects'     => $selects,
            'message'     => $message,
            'errors'     => $errors,
            ]
        );
    }

    /**
     * 予約項目の設定画面の表示
     */
    public function editColumns($request, $page_id, $frame_id, $reservations_id = null, $message = null, $errors = null)
    {
        if ($errors) {
            // エラーあり：入力値をフラッシュデータとしてセッションへ保存
            $request->flash();
        } else {
            // エラーなし：セッションから入力値を消去
            $request->flush();
        }

        // --- 基本データの取得
        // 施設予約＆フレームデータ
        $reservation_frame = $this->getFrame($frame_id);

        // 施設データ
        $reservation = new Reservations();

        // id が渡ってくればid が対象
        if (!empty($reservations_id)) {
            $reservation = Reservations::where('id', $reservations_id)->first();
        }
        // Frame のbucket_id があれば、bucket_id から施設データ取得
        elseif (!empty($reservation_frame->bucket_id)) {
            $reservation = Reservations::where('bucket_id', $reservation_frame->bucket_id)->first();
        }

        // 施設予約データがない場合は0をセット
        $reservations_id = empty($reservation) ? null : $reservation->id;

        // 予約項目データ
        $columns = reservations_columns::query()
            ->select(
                'reservations_columns.id',
                'reservations_columns.reservations_id',
                'reservations_columns.column_type',
                'reservations_columns.column_name',
                'reservations_columns.required',
                'reservations_columns.hide_flag',
                'reservations_columns.display_sequence',
                DB::raw('count(reservations_columns_selects.id) as select_count'),
                DB::raw('GROUP_CONCAT(reservations_columns_selects.select_name order by reservations_columns_selects.display_sequence SEPARATOR \',\') as select_names'),
            )
            ->where('reservations_columns.reservations_id', $reservations_id)
            // 予約項目の子データ（選択肢）
            ->leftjoin('reservations_columns_selects', function ($join) {
                $join->on('reservations_columns.id', '=', 'reservations_columns_selects.column_id');
            })
            ->groupby(
                'reservations_columns.id',
                'reservations_columns.reservations_id',
                'reservations_columns.column_type',
                'reservations_columns.column_name',
                'reservations_columns.required',
                'reservations_columns.hide_flag',
                'reservations_columns.display_sequence',
            )
            ->orderby('reservations_columns.display_sequence')
            ->get();

        // 編集画面テンプレートを呼び出す。
        return $this->view(
            'reservations_columns_edit',
            [
            'reservations_id' => $reservations_id,
            'reservation'     => $reservation,
            'columns'     => $columns,
            'message'     => $message,
            'errors'     => $errors,
            ]
        );
    }

    /**
     * 施設の登録
     */
    public function addFacility($request, $page_id, $frame_id)
    {
        // エラーチェック
        $validator = Validator::make($request->all(), [
            'facility_name'  => ['required'],
        ]);
        $validator->setAttributeNames([
            'facility_name'  => '施設名',
        ]);

        $errors = null;
        if ($validator->fails()) {
            // エラーと共に編集画面を呼び出す
            $errors = $validator->errors();
            return $this->editFacilities($request, $page_id, $frame_id, $request->reservations_id, null, $errors);
        }

        // 新規登録時の表示順を設定
        $max_display_sequence = reservations_facilities::query()->where('reservations_id', $request->reservations_id)->max('display_sequence');
        $max_display_sequence = $max_display_sequence ? $max_display_sequence + 1 : 1;

        // 施設の登録処理
        $facility = new reservations_facilities();
        $facility->reservations_id = $request->reservations_id;
        $facility->facility_name = $request->facility_name;
        $facility->display_sequence = $max_display_sequence;
        $facility->save();
        $message = '施設【 '. $request->facility_name .' 】を追加しました。';

        // 編集画面を呼び出す
        return $this->editFacilities($request, $page_id, $frame_id, $request->reservations_id, $message, $errors);
    }

    /**
     *  フレームIDに紐づく施設予約データを取得
     */
    private function getReservation($frame_id)
    {
        $reservation = DB::table('reservations')
            ->select('reservations.*')
            ->join('frames', 'frames.bucket_id', '=', 'reservations.bucket_id')
            ->where('frames.id', '=', $frame_id)
            ->first();

        return $reservation;
    }

    /**
     * 予約詳細項目（選択肢）の登録
     */
    public function addSelect($request, $page_id, $frame_id)
    {
        // エラーチェック
        $validator = Validator::make($request->all(), [
            'select_name'  => ['required'],
        ]);
        $validator->setAttributeNames([
            'select_name'  => '選択肢名',
        ]);

        $errors = null;
        if ($validator->fails()) {
            // エラーと共に編集画面を呼び出す
            $errors = $validator->errors();
            return $this->editColumnDetail($request, $page_id, $frame_id, $request->reservations_id, null, $errors);
        }

        // 新規登録時の表示順を設定
        $max_display_sequence = reservations_columns_selects::query()->where('column_id', $request->column_id)->max('display_sequence');
        $max_display_sequence = $max_display_sequence ? $max_display_sequence + 1 : 1;

        // 施設の登録処理
        $select = new reservations_columns_selects();
        $select->reservations_id = $request->reservations_id;
        $select->column_id = $request->column_id;
        $select->select_name = $request->select_name;
        $select->display_sequence = $max_display_sequence;
        $select->save();
        $message = '予約詳細項目【 '. $request->select_name .' 】を追加しました。';

        // 編集画面を呼び出す
        return $this->editColumnDetail($request, $page_id, $frame_id, $request->column_id, $message, $errors);
    }

    /**
     * 予約項目の登録
     */
    public function addColumn($request, $page_id, $frame_id)
    {
        // エラーチェック
        $validator = Validator::make($request->all(), [
            'column_name'  => ['required'],
            'column_type'  => ['required'],
        ]);
        $validator->setAttributeNames([
            'column_name'  => '予約項目名',
            'column_type'  => '型',
        ]);

        $errors = null;
        if ($validator->fails()) {
            // エラーと共に編集画面を呼び出す
            $errors = $validator->errors();
            return $this->editColumns($request, $page_id, $frame_id, $request->reservations_id, null, $errors);
        }

        // 新規登録時の表示順を設定
        $max_display_sequence = reservations_columns::query()->where('reservations_id', $request->reservations_id)->max('display_sequence');
        $max_display_sequence = $max_display_sequence ? $max_display_sequence + 1 : 1;

        // 施設の登録処理
        $column = new reservations_columns();
        $column->reservations_id = $request->reservations_id;
        $column->column_name = $request->column_name;
        $column->column_type = $request->column_type;
        $column->required = $request->required ? \Required::on : \Required::off;
        $column->display_sequence = $max_display_sequence;
        $column->save();
        $message = '予約項目【 '. $request->column_name .' 】を追加しました。';

        // 編集画面を呼び出す
        return $this->editColumns($request, $page_id, $frame_id, $request->reservations_id, $message, $errors);
    }

    /**
     * 施設の更新
     */
    public function updateFacility($request, $page_id, $frame_id)
    {
        // 明細行から更新対象を抽出する為のnameを取得
        $str_facility_name = "facility_name_"."$request->facility_id";
        $str_hide_flag = "hide_flag_"."$request->facility_id";

        // エラーチェック用に値を詰める
        $request->merge([
            "facility_name" => $request->$str_facility_name,
            "hide_flag" => $request->$str_hide_flag,
        ]);

        // エラーチェック
        $validator = Validator::make($request->all(), [
            'facility_name'  => ['required'],
        ]);
        $validator->setAttributeNames([
            'facility_name'  => '施設名',
        ]);

        $errors = null;
        if ($validator->fails()) {
            // エラーと共に編集画面を呼び出す
            $errors = $validator->errors();
            return $this->editFacilities($request, $page_id, $frame_id, $request->reservations_id, null, $errors);
        }

        // 施設の更新処理
        $facility = reservations_facilities::query()->where('reservations_id', $request->reservations_id)->where('id', $request->facility_id)->first();
        $facility->facility_name = $request->facility_name;
        $facility->hide_flag = $request->hide_flag;
        $facility->save();
        $message = '施設【 '. $request->facility_name .' 】を更新しました。';

        // 編集画面を呼び出す
        return $this->editFacilities($request, $page_id, $frame_id, $request->reservations_id, $message, $errors);
    }

    /**
     * 選択肢の更新
     */
    public function updateSelect($request, $page_id, $frame_id)
    {
        // 明細行から更新対象を抽出する為のnameを取得
        $str_select_name = "select_name_"."$request->select_id";
        $str_hide_flag = "hide_flag_"."$request->select_id";

        // エラーチェック用に値を詰める
        $request->merge([
            "select_name" => $request->$str_select_name,
            "hide_flag" => $request->$str_hide_flag,
        ]);

        // エラーチェック
        $validator = Validator::make($request->all(), [
            'select_name'  => ['required'],
        ]);
        $validator->setAttributeNames([
            'select_name'  => '選択肢名',
        ]);

        $errors = null;
        if ($validator->fails()) {
            // エラーと共に編集画面を呼び出す
            $errors = $validator->errors();
            return $this->editColumnDetail($request, $page_id, $frame_id, $request->column_id, null, $errors);
        }

        // 予約項目の更新処理
        $select = reservations_columns_selects::query()->where('id', $request->select_id)->first();
        $select->select_name = $request->select_name;
        $select->hide_flag = $request->hide_flag;
        $select->save();
        $message = '選択肢【 '. $request->select_name .' 】を更新しました。';

        // 編集画面を呼び出す
        return $this->editColumnDetail($request, $page_id, $frame_id, $request->column_id, $message, $errors);
    }

    /**
     * 予約項目の更新
     */
    public function updateColumn($request, $page_id, $frame_id)
    {
        // 明細行から更新対象を抽出する為のnameを取得
        $str_column_name = "column_name_"."$request->column_id";
        $str_column_type = "column_type_"."$request->column_id";
        $str_required = "required_"."$request->column_id";
        $str_hide_flag = "hide_flag_"."$request->column_id";

        // エラーチェック用に値を詰める
        $request->merge([
            "column_name" => $request->$str_column_name,
            "column_type" => $request->$str_column_type,
            "required" => $request->$str_required,
            "hide_flag" => $request->$str_hide_flag,
        ]);

        // エラーチェック
        $validator = Validator::make($request->all(), [
            'column_name'  => ['required'],
            'column_type'  => ['required'],
        ]);
        $validator->setAttributeNames([
            'column_name'  => '予約項目名',
            'column_type'  => '型',
        ]);

        $errors = null;
        if ($validator->fails()) {
            // エラーと共に編集画面を呼び出す
            $errors = $validator->errors();
            return $this->editColumns($request, $page_id, $frame_id, $request->reservations_id, null, $errors);
        }

        // 予約項目の更新処理
        $column = reservations_columns::query()->where('reservations_id', $request->reservations_id)->where('id', $request->column_id)->first();
        $column->column_name = $request->column_name;
        $column->column_type = $request->column_type;
        $column->required = $request->required ? \Required::on : \Required::off;
        $column->hide_flag = $request->hide_flag;
        $column->save();
        $message = '予約項目【 '. $request->column_name .' 】を更新しました。';

        // 編集画面を呼び出す
        return $this->editColumns($request, $page_id, $frame_id, $request->reservations_id, $message, $errors);
    }

    /**
     * 施設の表示順の更新
     */
    public function updateFacilitySequence($request, $page_id, $frame_id)
    {
        // ボタンが押された行の施設データ
        $target_facility = reservations_facilities::query()
            ->where('reservations_id', $request->reservations_id)
            ->where('id', $request->facility_id)
            ->first();

        // ボタンが押された前（後）の施設データ
        $query = reservations_facilities::query()
            ->where('reservations_id', $request->reservations_id);
        $pair_facility = $request->display_sequence_operation == 'up' ?
            $query->where('display_sequence', '<', $request->display_sequence)->orderby('display_sequence', 'desc')->limit(1)->first() :
            $query->where('display_sequence', '>', $request->display_sequence)->orderby('display_sequence', 'asc')->limit(1)->first();

        // それぞれの表示順を退避
        $target_facility_display_sequence = $target_facility->display_sequence;
        $pair_facility_display_sequence = $pair_facility->display_sequence;

        // 入れ替えて更新
        $target_facility->display_sequence = $pair_facility_display_sequence;
        $target_facility->save();
        $pair_facility->display_sequence = $target_facility_display_sequence;
        $pair_facility->save();

        $message = '施設【 '. $target_facility->facility_name .' 】の表示順を更新しました。';

        // 編集画面を呼び出す
        return $this->editFacilities($request, $page_id, $frame_id, $request->reservations_id, $message, null);
    }

    /**
     * 予約項目の表示順の更新
     */
    public function updateColumnSequence($request, $page_id, $frame_id)
    {
        // ボタンが押された行の施設データ
        $target_column = reservations_columns::query()
            ->where('reservations_id', $request->reservations_id)
            ->where('id', $request->column_id)
            ->first();

        // ボタンが押された前（後）の施設データ
        $query = reservations_columns::query()
            ->where('reservations_id', $request->reservations_id);
        $pair_column = $request->display_sequence_operation == 'up' ?
            $query->where('display_sequence', '<', $request->display_sequence)->orderby('display_sequence', 'desc')->limit(1)->first() :
            $query->where('display_sequence', '>', $request->display_sequence)->orderby('display_sequence', 'asc')->limit(1)->first();

        // それぞれの表示順を退避
        $target_column_display_sequence = $target_column->display_sequence;
        $pair_column_display_sequence = $pair_column->display_sequence;

        // 入れ替えて更新
        $target_column->display_sequence = $pair_column_display_sequence;
        $target_column->save();
        $pair_column->display_sequence = $target_column_display_sequence;
        $pair_column->save();

        $message = '予約項目【 '. $target_column->column_name .' 】の表示順を更新しました。';

        // 編集画面を呼び出す
        return $this->editColumns($request, $page_id, $frame_id, $request->reservations_id, $message, null);
    }

    /**
     * 選択肢の表示順の更新
     */
    public function updateSelectSequence($request, $page_id, $frame_id)
    {
        // ボタンが押された行の施設データ
        $target_select = reservations_columns_selects::query()
            ->where('id', $request->select_id)
            ->first();

        // ボタンが押された前（後）の施設データ
        $query = reservations_columns_selects::query()
            ->where('reservations_id', $request->reservations_id)
            ->where('column_id', $request->column_id);
        $pair_select = $request->display_sequence_operation == 'up' ?
            $query->where('display_sequence', '<', $request->display_sequence)->orderby('display_sequence', 'desc')->limit(1)->first() :
            $query->where('display_sequence', '>', $request->display_sequence)->orderby('display_sequence', 'asc')->limit(1)->first();

        // それぞれの表示順を退避
        $target_select_display_sequence = $target_select->display_sequence;
        $pair_select_display_sequence = $pair_select->display_sequence;

        // 入れ替えて更新
        $target_select->display_sequence = $pair_select_display_sequence;
        $target_select->save();
        $pair_select->display_sequence = $target_select_display_sequence;
        $pair_select->save();

        $message = '選択肢【 '. $target_select->select_name .' 】の表示順を更新しました。';

        // 編集画面を呼び出す
        return $this->editColumnDetail($request, $page_id, $frame_id, $request->column_id, $message, null);
    }
}
