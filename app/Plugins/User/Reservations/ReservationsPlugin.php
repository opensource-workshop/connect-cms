<?php

namespace App\Plugins\User\Reservations;

// use Carbon\Carbon;
use App\Models\Common\ConnectCarbon;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

use App\Models\Common\Buckets;
use App\Models\Common\Frame;
use App\Models\User\Reservations\Reservation;
use App\Models\User\Reservations\ReservationsCategory;
use App\Models\User\Reservations\ReservationsChoiceCategory;
use App\Models\User\Reservations\ReservationsColumn;
use App\Models\User\Reservations\ReservationsColumnsSelect;
use App\Models\User\Reservations\ReservationsFacility;
use App\Models\User\Reservations\ReservationsInput;
use App\Models\User\Reservations\ReservationsInputsColumn;

use App\Plugins\User\UserPluginBase;

use App\Enums\NoticeEmbeddedTag;
use App\Enums\NotShowType;
use App\Enums\Required;
use App\Enums\ReservationCalendarDisplayType;
use App\Enums\ReservationColumnType;
use App\Enums\ShowType;
use App\Enums\StatusType;

/**
 * 施設予約プラグイン
 *
 * 施設予約の特例処理：承認待ちの予約は他の人も見える。詳細は見せない。
 *
 * @author 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 施設予約プラグイン
 * @package Controller
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
            'choiceFacilities',
            'showBooking',
            'showBookingJson',
            'editBooking',
        ];
        $functions['post'] = [
            'updateChoiceFacilities',
            'editBooking',
            'saveBooking',
            'approvalBooking',
            'destroyBooking',
        ];
        return $functions;
    }

    /**
     * 追加の権限定義（コアから呼び出す）
     */
    public function declareRole()
    {
        // 権限チェックテーブル
        $role_check_table = [];
        $role_check_table["choiceFacilities"]       = ['buckets.editColumn'];
        $role_check_table["updateChoiceFacilities"] = ['buckets.saveColumn'];

        // posts.create, posts.deleteの自分の登録データかはDBカラムに created_id 必要。
        $role_check_table["editBooking"]            = ['posts.create', 'posts.update'];
        $role_check_table["saveBooking"]            = ['posts.create', 'posts.update'];
        $role_check_table["approvalBooking"]        = ['posts.approval'];
        $role_check_table["destroyBooking"]         = ['posts.delete'];

        return $role_check_table;
    }

    /**
     * メール送信で使用するメソッド
     */
    public function useBucketMailMethods()
    {
        return ['notice', 'approval', 'approved'];
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
     * POST取得関数（コアから呼び出す）
     * コアがPOSTチェックの際に呼び出す関数
     */
    public function getPost($id, $action = null)
    {
        if (is_null($action)) {
            // プラグイン内からの呼び出しを想定。処理を通す。
        } elseif (in_array($action, ['editBooking', 'saveBooking', 'destroyBooking'])) {
            // コアから呼び出し。posts.update|posts.deleteの権限チェックを指定したアクションは、処理を通す。
        } else {
            // それ以外のアクションは null で返す。
            return null;
        }

        // 一度読んでいれば、そのPOSTを再利用する。
        if (!empty($this->post)) {
            return $this->post;
        }

        // POST を取得する。(登録データ行の取得)
        $this->post = ReservationsInput::
            select(
                'reservations_inputs.*',
                'reservations_facilities.facility_name',
                'reservations_facilities.columns_set_id'
            )
            ->join('reservations_facilities', function ($join) {
                $join->on('reservations_inputs.facility_id', '=', 'reservations_facilities.id')
                    ->whereNull('reservations_facilities.deleted_at');
            })
            ->where(function ($query) {
                // 権限によって表示する記事を絞る
                $query = $this->appendAuthWhereBase($query, 'reservations_inputs');
            })
            ->firstOrNew(['reservations_inputs.id' => $id]);

        return $this->post;
    }

    /* private関数 */

    /**
     *  紐づく施設予約ID とフレームデータの取得
     */
    private function getReservationsFrame($frame_id)
    {
        // Frame と紐づく施設データを取得
        $frame = Frame::select('frames.*', 'reservations.id as reservations_id', 'reservations.*')
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
        return Frame::where('frames.id', $frame_id)->first();
    }

    /* スタティック関数 */

    // delete: 未実装のメソッド
    /**
     * 新着情報用メソッド
     */
    // public static function getWhatsnewArgs()
    // {

    //     // 戻り値('sql_method'、'link_pattern'、'link_base')

    //     $return[] = array();
    //     //$return[] = Reservation::where('日付など')
    //     //                        ->get(;
    //     //$return[] = 'show_page_frame_post';
    //     //$return[] = '/plugin/reservations/show';

    //     return $return;
    // }

    /* 画面アクション関数 */

    /**
     * 予約追加処理
     */
    public function saveBooking($request, $page_id, $frame_id, $booking_id = null)
    {
        $target_ymd = $request->target_date;
        // URLパラメータチェック
        $year = (int)substr($target_ymd, 0, 4);
        $month = (int)substr($target_ymd, 4, 2);
        $day = (int)substr($target_ymd, 6, 2);
        if (!checkdate($month, $day, $year)) {
            return $this->view_error("404_inframe", null, '日時パラメータ不正(' . $year . '/' . $month . '/' . $day . ')');
        }

        // バリデーション用の配列を生成（基本項目）
        $validationArray = [
            'start_datetime' => ['required'],
            'end_datetime' => ['required', 'after:start_datetime'],
        ];
        $attributeArray = [
            'start_datetime' => '開始時間',
            'end_datetime' => '終了時間',
        ];

        // バリデーション用の配列を生成（可変項目）
        $required_columns = ReservationsColumn::where('reservations_id', $request->reservations_id)
            ->where('hide_flag', NotShowType::show)
            ->where('required', Required::on)
            ->get();
        foreach ($required_columns as $column) {
            $key_str = 'columns_value.' . $column->id;
            $validationArray[$key_str] = ['required'];
            $attributeArray[$key_str] = $column->column_name;
        }

        // バリデーション定義
        $validator = Validator::make($request->all(), $validationArray);
        $validator->setAttributeNames($attributeArray);

        // バリデーション実施、エラー時は予約画面へ戻る
        if ($validator->fails()) {
            // return $this->editBooking($request, $page_id, $frame_id, $booking_id, $validator->errors());
            return back()->withErrors($validator)->withInput();
        }

        // 施設データ
        $facility = ReservationsFacility::where('id', $request->facility_id)->first();

        // 予約ヘッダ 登録 ※予約IDがある場合は更新
        $reservations_inputs = $booking_id ?
            ReservationsInput::where('id', $booking_id)->first() :
            new ReservationsInput();

        // 新規登録の判定のために、保存する前のレコードを退避しておく。
        $before_reservations_inputs = clone $reservations_inputs;

        // 承認の要否確認とステータス処理
        if ($this->isApproval()) {
            $reservations_inputs->status = StatusType::approval_pending;  // 承認待ち
            $str_mode = $booking_id ? '予約の変更申請をしました。' : '予約の登録申請をしました。';
        } else {
            $reservations_inputs->status = StatusType::active;  // 公開
            $str_mode = $booking_id ? '予約を更新しました。' : '予約を登録しました。';
        }

        // 新規登録時のみの登録項目
        if (!$booking_id) {
            $reservations_inputs->reservations_id = $request->reservations_id;
            $reservations_inputs->facility_id = $request->facility_id;
        }
        $reservations_inputs->start_datetime = new ConnectCarbon($target_ymd . ' ' . $request->start_datetime . ':00');
        $reservations_inputs->end_datetime = new ConnectCarbon($target_ymd . ' ' . $request->end_datetime . ':00');
        $reservations_inputs->save();

        // 項目IDを取得
        $keys = array_keys($request->columns_value);
        foreach ($keys as $key) {
            // 予約明細 更新レコード取得
            $reservations_inputs_columns = ReservationsInputsColumn::where('reservations_id', $request->reservations_id)
                    ->where('inputs_id', $reservations_inputs->id)
                    ->where('column_id', $key)
                    ->first();

            // 更新レコードが取得できなかったらnew
            if (!$reservations_inputs_columns) {
                $reservations_inputs_columns = new ReservationsInputsColumn();
                // 新規登録時のみの登録項目
                $reservations_inputs_columns->reservations_id = $request->reservations_id;
                $reservations_inputs_columns->inputs_id = $reservations_inputs->id;
                $reservations_inputs_columns->column_id = $key;
            }
            $reservations_inputs_columns->value = $request->columns_value[$key];

            $reservations_inputs_columns->save();
        }
        // $str_mode = $request->booking_id ? '更新' : '登録';
        // $message = '予約を' . $str_mode . 'しました。【場所】' . $facility->facility_name . ' 【日時】' . date_format($reservations_inputs->start_datetime, 'Y年m月d日 H時i分') . ' ～ ' . date_format($reservations_inputs->end_datetime, 'H時i分');
        $request->flash_message = $str_mode . '【場所】' . $facility->facility_name . ' 【日時】' . date_format($reservations_inputs->start_datetime, 'Y年m月d日 H時i分') . ' ～ ' . date_format($reservations_inputs->end_datetime, 'H時i分');

        // titleカラムが無いため、プラグイン独自でセット
        $overwrite_notice_embedded_tags = [NoticeEmbeddedTag::title => $this->getTitle($reservations_inputs)];

        // メール送信 引数(レコードを表すモデルオブジェクト, 保存前のレコード, 詳細表示メソッド, 上書き埋め込みタグ)
        $this->sendPostNotice($reservations_inputs, $before_reservations_inputs, 'showBooking', $overwrite_notice_embedded_tags);

        // 登録後はカレンダー表示
        // return $this->index($request, $page_id, $frame_id, null, null, $message);
        return collect(['redirect_path' => url($this->page->permanent_link)]);
    }

    /**
     * 予約追加画面の表示
     */
    public function editBooking($request, $page_id, $frame_id, $input_id = null)
    {
        $booking = null;

        // if ($request->booking_id) {
        if ($input_id) {

            /**
             * 予約の更新モード
             */

            // 予約データ
            // $booking = ReservationsInput::where('id', $request->booking_id)->first();
            $booking = ReservationsInput::where('id', $input_id)->first();

            // 施設予約データ
            $reservation = Reservation::where('id', $booking->reservations_id)->first();

            // 施設データ
            $facility = ReservationsFacility::where('id', $booking->facility_id)->first();

            // 予約項目データ（予約入力値付）
            $columns = ReservationsColumn::
                select(
                    'reservations_columns.*',
                    'reservations_inputs_columns.value',
                )
                ->leftJoin('reservations_inputs_columns', function ($join) use ($booking) {
                    $join->on('reservations_inputs_columns.column_id', '=', 'reservations_columns.id');
                    $join->where('reservations_inputs_columns.inputs_id', '=', $booking->id);
                })
                ->where('reservations_columns.reservations_id', $booking->reservations_id)
                ->where('reservations_columns.hide_flag', NotShowType::show)
                ->orderBy('reservations_columns.display_sequence')
                ->get();

            // 予約項目データの内、選択肢が指定されていた場合の選択肢データ
            $selects = ReservationsColumnsSelect::where('reservations_id', $booking->reservations_id)
                ->where('hide_flag', NotShowType::show)
                ->orderBy('id', 'asc')
                ->orderBy('display_sequence', 'asc')
                ->get();

            $target_date = new ConnectCarbon($booking->start_datetime);
        } else {
            /**
             * 予約の新規登録モード
             */
            // パラメータチェック. saveの入力エラーで戻ってきた時はoldに値が入ってる。
            $target_ymd = old('target_date', $request->target_date);
            $year = (int)substr($target_ymd, 0, 4);
            $month = (int)substr($target_ymd, 4, 2);
            $day = (int)substr($target_ymd, 6, 2);
            if (!checkdate($month, $day, $year)) {
                return $this->view_error("404_inframe", null, '日時パラメータ不正(' . $year . '/' . $month . '/' . $day . ')');
            }

            // 施設予約データ
            $reservation = Reservation::where('id', old('reservations_id', $request->reservations_id))->first();

            // 施設データ
            $facility = ReservationsFacility::where('id', old('facility_id', $request->facility_id))->first();

            // 予約項目データ
            // $columns = ReservationsColumn::where('reservations_id', $request->reservations_id)->whereNull('hide_flag')->orderBy('display_sequence')->get();
            // $columns = $this->getReservationsColumns($request->reservations_id);
            $columns = $this->getReservationsColumns($facility->columns_set_id);

            // 予約項目データの内、選択肢が指定されていた場合の選択肢データ
            // $selects = ReservationsColumnsSelect::where('reservations_id', $request->reservations_id)
            $selects = ReservationsColumnsSelect::where('columns_set_id', $facility->columns_set_id)
                ->where('hide_flag', NotShowType::show)
                ->orderBy('id', 'asc')
                ->orderBy('display_sequence', 'asc')
                ->get();

            $target_date = new ConnectCarbon($target_ymd);
        }

        return $this->view('edit_booking', [
            'target_date' => $target_date,
            'reservation' => $reservation,
            'facility' => $facility,
            'columns' => $columns,
            'selects' => $selects,
            'booking' => $booking,
        ]);
    }

    /**
     * 予約の詳細表示 URL版
     */
    public function showBooking($request, $page_id, $frame_id, $input_id = null, $is_json = false)
    {
        // 登録データ行の取得
        $inputs = $this->getReservationsInput($input_id);
        // データがあることを確認
        if (empty($inputs->id)) {
            return;
        }

        // カラムの取得
        $columns = $this->getReservationsColumns($inputs->columns_set_id);
        // データがあることを確認
        if (empty($columns)) {
            return;
        }

        // データ詳細の取得
        $inputs_columns = $this->getReservationsInputsColumns($input_id);

        // 選択肢
        $selects = ReservationsColumnsSelect::where('reservations_id', $inputs->reservations_id)->orderBy('id', 'asc')->orderBy('display_sequence', 'asc')->get();

        if ($is_json) {

            // 表示値をセット
            $inputs->reservation_date_display = $inputs->displayDate();
            $inputs->reservation_time_display = $inputs->start_datetime->format('H:i') . ' ~ ' . $inputs->end_datetime->format('H:i');

            foreach ($columns as &$column) {
                $obj = $inputs_columns->firstWhere('column_id', $column->id);

                // カラムの表示値をセット
                $column->display_value = $this->getColumnValue($inputs, $column, $obj);
            }

            // JSON
            return [
                'columns' => $columns,
                // inputにすると値があってもnullになるため、$inputsのままでいく
                'inputs' => $inputs,
                'inputs_columns' => $inputs_columns,
                'selects' => $selects,
            ];
        } else {
            // 詳細画面を呼び出す。
            return $this->view('show_booking', [
                'columns' => $columns,
                // inputにすると値があってもnullになるため、$inputsのままでいく
                'inputs' => $inputs,
                'inputs_columns' => $inputs_columns,
                'selects' => $selects,
            ]);
        }
    }

    /**
     * 予約の詳細表示 JSON版
     */
    public function showBookingJson($request, $page_id, $frame_id, $input_id = null)
    {
        $is_json = true;
        return $this->showBooking($request, $page_id, $frame_id, $input_id, $is_json);
    }

    /**
     * 登録データ行の取得
     */
    private function getReservationsInput($id)
    {
        return $this->getPost($id);
    }

    /**
     * カラムの取得
     */
    private function getReservationsColumns($id)
    {
        // return ReservationsColumn::where('reservations_id', $id)->where('hide_flag', NotShowType::show)->orderBy('display_sequence')->get();
        return ReservationsColumn::where('columns_set_id', $id)->where('hide_flag', NotShowType::show)->orderBy('display_sequence')->get();
    }

    /**
     * データ詳細の取得
     */
    private function getReservationsInputsColumns($id)
    {
        $inputs_columns = ReservationsInputsColumn::
            select(
                'reservations_inputs_columns.*',
                'reservations_columns.column_type',
                'reservations_columns.column_name',
                'reservations_columns.title_flag'
            )
            ->leftJoin('reservations_columns', function ($join) {
                $join->on('reservations_columns.id', '=', 'reservations_inputs_columns.column_id')
                    ->where('reservations_columns.hide_flag', NotShowType::show)
                    ->whereNull('reservations_columns.deleted_at');
            })
            ->where('reservations_inputs_columns.inputs_id', $id)
            ->orderBy('reservations_inputs_columns.inputs_id', 'asc')
            ->orderBy('reservations_inputs_columns.column_id', 'asc')
            ->get();

        return $inputs_columns;
    }

    /**
     * 予約の承認処理
     */
    public function approvalBooking($request, $page_id, $frame_id, $input_id = null)
    {
        // 登録データ行の取得
        $reservations_inputs = $this->getReservationsInput($input_id);
        // データがあることを確認
        if (empty($reservations_inputs->id)) {
            return;
        }

        // 承認済みの判定のために、保存する前のレコードを退避しておく。
        $before_reservations_inputs = clone $reservations_inputs;

        // 更新されたら、行レコードの updated_at を更新したいので、update()
        $reservations_inputs->status = StatusType::active;  // 公開
        $reservations_inputs->update();

        // メール送信 引数(レコードを表すモデルオブジェクト, 保存前のレコード, 詳細表示メソッド)
        $this->sendPostNotice($reservations_inputs, $before_reservations_inputs, 'showBooking');

        // 登録後は画面側の指定により、リダイレクトして表示画面を開く。
        return;
    }

    /**
     * データ初期表示関数
     * コアがページ表示の際に呼び出す関数
     */
    public function index($request, $page_id, $frame_id, $view_format = null, $carbon_target_date = null)
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
        $reservations = Reservation::where('id', $reservations_frame->reservations_id)->first();

        // 施設データ
        // $facilities = ReservationsFacility::where('reservations_id', $reservations_frame->reservations_id)
        //     ->where('hide_flag', NotShowType::show)
        //     ->orderBy('display_sequence')
        //     ->get();
        $facilities = ReservationsChoiceCategory::
            select('reservations_facilities.*')
            ->leftJoin('reservations_facilities', function ($join) {
                $join->on('reservations_facilities.reservations_categories_id', '=', 'reservations_choice_categories.reservations_categories_id')
                    ->where('reservations_facilities.hide_flag', NotShowType::show)
                    ->whereNull('reservations_facilities.deleted_at');
            })
            ->where('reservations_choice_categories.reservations_id', $reservations_frame->reservations_id)
            ->where('reservations_choice_categories.view_flag', ShowType::show)
            ->orderBy('reservations_choice_categories.display_sequence', 'asc')
            ->orderBy('reservations_facilities.display_sequence', 'asc')
            ->get();

        // 予約項目データの内、選択肢が指定されていた場合に選択肢データが登録済みかチェック
        // $isExistSelect = true;
        // $filtered_columns = $columns->filter(function ($column) {
        //     // 選択肢が設定可能なデータ型のみ抽出
        //     return $column->column_type == ReservationColumnType::radio;
        // });
        // foreach ($filtered_columns as $column) {
        //     $filtered_selects = $selects->filter(function ($select) use ($column) {
        //         return $column->id == $select->column_id;
        //     });
        //     if ($filtered_selects->isEmpty()) {
        //         $isExistSelect = false;
        //     }
        // }

        // 対象日時未設定（初期表示）の場合は現在日時をセット
        if (empty($carbon_target_date)) {
            $carbon_target_date = ConnectCarbon::today();
        }

        /**
         * カレンダー表示データの生成
         */
        $dates = [];
        $search_start_date = null;
        $search_end_date = null;

        if ($view_format == ReservationCalendarDisplayType::month) {

            /**
             * 月表示用のデータ
             */
            $firstDay = new ConnectCarbon("$carbon_target_date->year-$carbon_target_date->month-01");
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
         *   calendar_cell['date'] : ConnectCarbon日付データ
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
            // $bookingHeaders = ReservationsInput::where('reservations_id', $reservations->id)
            $bookingHeaders = ReservationsInput::where('facility_id', $facility->id)
                ->whereBetween('start_datetime', [$search_start_date, $search_end_date])
                ->orderBy('start_datetime')
                ->get();

            // 予約項目データ
            $columns = $this->getReservationsColumns($facility->columns_set_id);

            foreach ($dates as $date) {
                $calendar_cell = null;
                // セルの日付に日付データを追加
                $calendar_cell['date'] = $date;
                // 日付データと予約データを突き合わせて該当日に予約データを付加
                foreach ($bookingHeaders as $bookingHeader) {
                    if ($date->format('Ymd') == $bookingHeader->start_datetime->format('Ymd')) {
                        // セルの予約配列に予約データを追加
                        $booking = null;
                        $booking['booking_details'] = ReservationsInputsColumn::
                            select(
                                'reservations_inputs_columns.*',
                                'reservations_columns.title_flag'
                            )
                            ->leftJoin('reservations_columns', function ($join) {
                                $join->on('reservations_inputs_columns.column_id', '=', 'reservations_columns.id');
                            })
                            // ->where('reservations_inputs_columns.reservations_id', $reservations->id)
                            ->where('reservations_inputs_columns.inputs_id', $bookingHeader->id)
                            ->orderBy('reservations_inputs_columns.column_id')
                            ->get();

                        // タイトル設定
                        $bookingHeader->title = $this->getTitle($bookingHeader, $columns, $booking['booking_details']);

                        $booking['booking_header'] = $bookingHeader;

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

        // 必要なデータ揃っているか確認
        // フレームに紐づいた施設予約親データが存在すること
        if (isset($this->frame) && $this->frame->bucket_id &&
            // 施設データが存在すること
            !$facilities->isEmpty()) {

            // 予約項目データが存在すること
            // !$columns->isEmpty()  &&
            // 予約項目で選択肢が指定されていた場合に選択肢データが存在すること
            // $isExistSelect) {

            // $time = microtime(true) - $time_start;  //debug用
            // dd($time . '秒');  //debug用
            // dd($calendars);  //debug用
            return $this->view('reservations_calendar_common', [
                'view_format' => $view_format,
                'carbon_target_date' => $carbon_target_date,
                'reservations' => $reservations,
                'facilities' => $facilities,
                'calendars' => $calendars,
            ]);
        } else {

            // バケツ等なし
            return $this->view('empty_bucket', [
                'facilities' => $facilities,
                // 'isExistSelect' => $isExistSelect,
            ]);
        }
    }

    /**
     * タイトル取得
     */
    private function getTitle($input, $columns = null, $inputs_columns = null)
    {
        // 入力行データ
        if (is_null($input)) {
            return '';
        }

        // カラム
        if (is_null($columns)) {
            $columns = $this->getReservationsColumns($input->columns_set_id);
        }

        // title_flagのカラム1件に絞る
        $column = $columns->firstWhere('title_flag', '1');
        if (is_null($column)) {
            return '';
        }

        // カラム入力値
        if (is_null($inputs_columns)) {
            $inputs_columns = $this->getReservationsInputsColumns($input->id);
        }

        // title_flagのカラム1件に絞る
        $obj = $inputs_columns->firstWhere('title_flag', '1');

        // カラムの値取得
        $value = $this->getColumnValue($input, $column, $obj);

        return $value;
    }

    /**
     * カラムの値取得
     */
    private function getColumnValue($input, $column = null, $inputs_column = null)
    {
        // 項目の型で処理を分ける。
        if ($column->column_type == ReservationColumnType::radio) {
            // ラジオ型
            if ($inputs_column) {
                // ラジオボタン項目の場合、valueにはreservations_columns_selectsテーブルのIDが入っているので、該当の選択肢データを取得して選択肢名をセットする
                $filtered_select = ReservationsColumnsSelect::where('reservations_id', $inputs_column->reservations_id)
                    ->where('column_id', $inputs_column->column_id)
                    ->where('id', $inputs_column->value)
                    ->first();

                $value = $filtered_select ? $filtered_select->select_name : '';
            } else {
                $value = '';
            }
        } elseif ($column->column_type == ReservationColumnType::created) {
            // 登録日型
            $value = $input->created_at->format('Y-n-j H:i:s');
        } elseif ($column->column_type == ReservationColumnType::updated) {
            // 更新日型
            $value = $input->updated_at->format('Y-n-j H:i:s');
        } elseif ($column->column_type == ReservationColumnType::created_name) {
            // 登録者型
            $value = $input->created_name;
        } elseif ($column->column_type == ReservationColumnType::updated_name) {
            // 更新者型
            $value = $input->updated_name;
        } else {
            // その他の型
            $value = $inputs_column ? $inputs_column->value : "";
        }

        return $value;
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
        $carbon_target_date = new ConnectCarbon("$target_ymd");
        return $this->index($request, $page_id, $frame_id, ReservationCalendarDisplayType::week, $carbon_target_date);
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
        $carbon_target_date = new ConnectCarbon("$year-$month-01");
        return $this->index($request, $page_id, $frame_id, ReservationCalendarDisplayType::month, $carbon_target_date);
    }

    /**
     * 表示コンテンツ選択画面の表示
     */
    public function listBuckets($request, $page_id, $frame_id, $id = null)
    {
        // 施設予約＆フレームデータ
        $reservations_frame = $this->getReservationsFrame($frame_id);

        // 施設予約の取得
        $query = Reservation::query();
        $query->select(
            'reservations.id',
            'reservations.bucket_id',
            'reservations.reservation_name',
            'reservations.calendar_initial_display_type',
            'reservations.created_at',
            DB::raw('GROUP_CONCAT(reservations_facilities.facility_name SEPARATOR \'\n\') as facility_names'),
        );
        $query->leftjoin('reservations_facilities', function ($join) {
            $join->on('reservations.id', '=', 'reservations_facilities.reservations_id')
                ->whereNull('reservations_facilities.deleted_at');
        });
        $query->groupBy(
            'reservations.id',
            'reservations.bucket_id',
            'reservations.reservation_name',
            'reservations.calendar_initial_display_type',
            'reservations.created_at',
        );
        $query->orderBy('reservations.created_at', 'desc');
        $reservations = $query->paginate(10, ["*"], "frame_{$frame_id}_page");

        // 表示テンプレートを呼び出す。
        return $this->view('reservations_list_buckets', [
            'reservations_frame' => $reservations_frame,
            'reservations' => $reservations,
        ]);
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
        $reservation = new Reservation();

        if (!empty($reservations_id)) {
            // id が渡ってくればid が対象
            $reservation = Reservation::where('id', $reservations_id)->first();
        } elseif (!empty($reservation_frame->bucket_id) && $create_flag == false) {
            // Frame のbucket_id があれば、bucket_id から施設データ取得、なければ、新規作成か選択へ誘導
            $reservation = Reservation::where('bucket_id', $reservation_frame->bucket_id)->first();
        }
        $reservation = $reservation ?? new Reservation();

        if (!$reservation_frame->bucket_id) {
            // バケツ空テンプレートを呼び出す。
            return $this->commonView('empty_bucket_setting');
        }

        // 表示テンプレートを呼び出す。
        return $this->view('reservations_edit_buckets', [
            'reservation_frame'  => $reservation_frame,
            'reservation'        => $reservation,
            'create_flag'        => $create_flag,
            'message'            => $message,
            'errors'             => $errors,
        ])->withInput($request->all);
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
            if (empty($request->reservations_id)) {
                $create_flag = true;
                return $this->createBuckets($request, $page_id, $frame_id, $request->reservations_id, $create_flag, $message, $validator->errors());
            } else {
                $create_flag = false;
                return $this->editBuckets($request, $page_id, $frame_id, $request->reservations_id, $create_flag, $message, $validator->errors());
            }
        }

        // 更新後のメッセージ
        $message = null;

        if (empty($request->reservations_id)) {
            // 画面から渡ってくるid が空ならバケツと施設を新規登録
            // バケツの登録
            $bucket_id = Buckets::insertGetId([
                'bucket_name' => $request->reservation_name,
                'plugin_name' => 'reservations'
            ]);

            // 施設予約データ新規オブジェクト
            $reservations = new Reservation();
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
        } else {
            // id があれば、施設予約を更新
            // 施設予約データ取得
            $reservations = Reservation::where('id', $request->reservations_id)->first();

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
            Reservation::where('id', $reservations_id)->first()->delete();

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
     * 予約削除
     */
    public function destroyBooking($request, $page_id, $frame_id, $input_id)
    {
        $message = null;
        // id がある場合、データを削除
        // if ($request->booking_id) {
        if ($input_id) {
            // 予約（子）を削除
            // $input_columns = ReservationsInputsColumn::where('inputs_id', $request->booking_id)->get();
            $input_columns = ReservationsInputsColumn::where('inputs_id', $input_id)->get();
            foreach ($input_columns as $input_column) {
                $input_column->delete();
            }

            // 予約（親）、施設情報を取得してメッセージ修正
            // $input = ReservationsInput::where('id', $request->booking_id)->first();
            $input = ReservationsInput::where('id', $input_id)->first();
            $facility = ReservationsFacility::where('id', $input->facility_id)->first();
            $message = '予約を削除しました。【場所】' . $facility->facility_name . ' 【日時】' . date_format($input->start_datetime, 'Y年m月d日 H時i分') . ' ～ ' . date_format($input->end_datetime, 'H時i分');

            // メール送信
            $this->sendDeleteNotice($input, 'showBooking', $message);

            session()->flash('flash_message', $message);

            // 予約（親）を削除
            $input->delete();
        }
        // 削除処理はredirect 付のルートで呼ばれて、処理後はページの再表示が行われるため、ここでは何もしない。
        // return $this->index($request, $page_id, $frame_id, null, null, $message);
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
     * 施設カテゴリ選択の設定画面の表示
     */
    public function choiceFacilities($request, $page_id, $frame_id, $reservations_id = null)
    {
        // --- 基本データの取得
        // 施設予約＆フレームデータ
        $reservation_frame = $this->getFrame($frame_id);

        // 施設データ
        if ($reservations_id) {
            // id が渡ってくればid が対象
            $reservation = Reservation::where('id', $reservations_id)->first();
        } elseif ($reservation_frame->bucket_id) {
            // Frame のbucket_id があれば、bucket_id から施設データ取得
            $reservation = Reservation::where('bucket_id', $reservation_frame->bucket_id)->first();
        }
        $reservation ?? new Reservation();

        if (!$reservation->id) {
            // バケツ空テンプレートを呼び出す。
            return $this->commonView('empty_bucket_setting');
        }

        // 施設カテゴリと施設カテゴリ選択
        $reservations_categories = ReservationsCategory::
            select(
                'reservations_categories.*',
                'reservations_choice_categories.view_flag',
                'reservations_choice_categories.display_sequence as choice_display_sequence'
            )
            ->leftJoin('reservations_choice_categories', function ($join) use ($reservation) {
                $join->on('reservations_choice_categories.reservations_categories_id', '=', 'reservations_categories.id')
                    ->where('reservations_choice_categories.reservations_id', $reservation->id)
                    ->whereNull('reservations_choice_categories.deleted_at');
            })
            ->orderBy('reservations_choice_categories.display_sequence', 'asc')
            ->orderBy('reservations_categories.display_sequence', 'asc')
            ->get();

        $facilities = ReservationsFacility::where('hide_flag', NotShowType::show)->get();

        foreach ($reservations_categories as $reservations_category) {
            // （初期登録時を想定）reservations_choice_categoriesの表示順が空なので、施設カテゴリの表示順を初期値にセット
            if (is_null($reservations_category->choice_display_sequence)) {
                $reservations_category->choice_display_sequence = $reservations_category->display_sequence;
            }

            // 施設名をセット
            $reservations_category->facilities_name = $facilities->where('reservations_categories_id', $reservations_category->id)
                ->pluck('facility_name')->implode(',');
        }

        // 編集画面テンプレートを呼び出す。
        return $this->view('choice_facilities', [
            'reservation' => $reservation,
            'reservations_categories'  => $reservations_categories,
        ]);
    }

    /**
     * 施設カテゴリ選択の更新
     */
    public function updateChoiceFacilities($request, $page_id, $frame_id)
    {
        /* エラーチェック
        ------------------------------------ */

        $rules = [];

        // エラーチェックの項目名
        $setAttributeNames = [];

        // 共通項目 のidに値が入っていたら、行の他の項目も必須
        if (!empty($request->reservations_category_id)) {
            foreach ($request->reservations_category_id as $category_id) {
                // 項目のエラーチェック
                $rules['choice_display_sequence.'.$category_id] = ['required'];

                $setAttributeNames['choice_display_sequence.'.$category_id] = '表示順';
            }
        }

        // 項目のエラーチェック
        $validator = Validator::make($request->all(), $rules);
        $validator->setAttributeNames($setAttributeNames);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // 施設予約＆フレームデータ
        $reservation_frame = $this->getReservationsFrame($frame_id);

        /* 表示フラグ更新(共通カテゴリ)
        ------------------------------------ */
        if (!empty($request->reservations_category_id)) {
            foreach ($request->reservations_category_id as $reservations_category_id) {
                // FAQプラグインのカテゴリー使用テーブルになければ追加、あれば更新
                ReservationsChoiceCategory::updateOrCreate(
                    [
                        'reservations_id' => $reservation_frame->reservations_id,
                        'reservations_categories_id' => $reservations_category_id,
                    ],
                    [
                        'view_flag' => $request->view_flag[$reservations_category_id] == ShowType::show ? ShowType::show : ShowType::not_show,
                        'display_sequence' => $request->choice_display_sequence[$reservations_category_id],
                    ]
                );
            }
        }

        return redirect()->back()->with('flash_message', '変更しました。');
    }
}
