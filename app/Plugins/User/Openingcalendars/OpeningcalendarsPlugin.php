<?php

namespace App\Plugins\User\Openingcalendars;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

use Carbon\Carbon;

use DB;

use App\Models\Common\Buckets;
use App\Models\Common\Frame;
use App\Models\Common\Uploads;
use App\Models\User\Openingcalendars\Openingcalendars;
use App\Models\User\Openingcalendars\OpeningcalendarsDays;
use App\Models\User\Openingcalendars\OpeningcalendarsMonths;
use App\Models\User\Openingcalendars\OpeningcalendarsPatterns;

use App\Plugins\User\UserPluginBase;
use App\Traits\ConnectCommonTrait;

/**
 * 開館カレンダー・プラグイン
 *
 * 図書館などの開館予定を表示するプラグイン。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コンテンツプラグイン
 * @package Contoroller
 */
class OpeningcalendarsPlugin extends UserPluginBase
{
    use ConnectCommonTrait;

    /* オブジェクト変数 */

    /* コアから呼び出す関数 */

    /**
     *  関数定義（コアから呼び出す）
     */
    public function getPublicFunctions()
    {
        // 標準関数以外で画面などから呼ばれる関数の定義
        $functions = array();
        $functions['get']  = ['listPatterns', 'editYearschedule', 'sendYearschedule'];
        $functions['post'] = ['edit', 'savePatterns', 'deletePatterns', 'saveYearschedule'];
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

    /* private関数 */

    /**
     *  紐づく開館カレンダーID とフレームデータの取得
     */
    private function getOpeningcalendarFrame($frame_id)
    {
        // Frame データ
        $frame = DB::table('frames')
                 ->select('frames.*',
                          'openingcalendars.id as openingcalendars_id',
                          'openingcalendars.openingcalendar_name',
                          'openingcalendars.openingcalendar_sub_name',
                          'openingcalendars.month_format',
                          'openingcalendars.week_format',
                          'openingcalendars.view_before_month',
                          'openingcalendars.view_after_month',
                          'openingcalendars.yearschedule_uploads_id',
                          'openingcalendars.yearschedule_link_text',
                          'openingcalendars.smooth_scroll',
                          'uploads.client_original_name'
                         )
                 ->leftJoin('openingcalendars', 'openingcalendars.bucket_id', '=', 'frames.bucket_id')
                 ->leftJoin('uploads', 'uploads.id', '=', 'openingcalendars.yearschedule_uploads_id')
                 ->where('frames.id', $frame_id)
                 ->first();
        return $frame;
    }

    /**
     * 開館カレンダー取得
     */
    private function getOpeningcalendars($frame_id)
    {
        // 開館カレンダー
        return $openingcalendar = DB::table('frames')
                 ->select('openingcalendars.*')
                 ->join('buckets', 'buckets.id', '=', 'frames.bucket_id')
                 ->join('openingcalendars', 'openingcalendars.bucket_id', '=', 'buckets.id')
                 ->where('frames.id', $frame_id)
                 ->first();

    }

    /**
     *  カレンダーの取得
     */
    private function getCalendarDates($view_ym)
    {
        $dateStr = $view_ym . "-01";
        $date = new Carbon($dateStr);

        // カレンダーの前月分となる左上の隙間用のデータを入れるためずらす
        $date->subDay($date->dayOfWeek);

        // 同上。右下の隙間のための計算。
        $count = 31 + $date->dayOfWeek;
        $count = ceil($count / 7) * 7;
        $dates = [];

        for ($i = 0; $i < $count; $i++, $date->addDay()) {
            // copyしないと全部同じオブジェクトを入れてしまうことになる
            $dates[] = $date->copy();
        }
        return $dates;
    }


    /* 画面アクション関数 */

    /**
     *  データ初期表示関数
     *  コアがページ表示の際に呼び出す関数
     */
    public function index($request, $page_id, $frame_id)
    {
        // 当月（初期表示）
        $view_ym = date("Y-m");
        $view_ym_str = date("F / Y");

        // Frame データ
        $openingcalendar_frame = $this->getOpeningcalendarFrame($frame_id);

        // パターン取得(画面で2列に分けて表示したいので、配列にして2列に分割、画面へ渡す)
        $openingcalendars_patterns = OpeningcalendarsPatterns::where('openingcalendars_id', '=', $openingcalendar_frame->openingcalendars_id)
                                                             ->orderBy("display_sequence", "asc")
                                                             ->get();
        $patterns_array = array();
        $patterns = array();
        foreach($openingcalendars_patterns as $openingcalendars_pattern) {
            $patterns_array[$openingcalendars_pattern->id] = $openingcalendars_pattern;
            $patterns[$openingcalendars_pattern->id] = $openingcalendars_pattern->color;
        }
        $patterns_chunks = array_chunk($patterns_array, 2);
        //print_r($patterns);

        // 過去・未来の表示年月
        $view_before_ym = date("Y-m", strtotime(" - " . $openingcalendar_frame->view_before_month . " month"));
        $view_after_ym = date("Y-m", strtotime(" + " . $openingcalendar_frame->view_after_month . " month"));

        // リクエストパラメータ方式を検討したが、STOP。同じページに複数の開館カレンダーを配置する＆それぞれがデータの登録状況が異なるため。

        //// 表示候補（過去の表示月数、未来の表示月数の範囲で年月取得）
        //$opening_date_ym_rec = OpeningcalendarsDays::select(DB::raw('substr(opening_date, 1, 7) as opening_date_ym'))
        //                                             ->groupBy(DB::raw('substr(opening_date, 1, 7)'))
        //                                             ->havingRaw('opening_date_ym >= ?', [$view_before_ym])
        //                                             ->havingRaw('opening_date_ym <= ?', [$view_after_ym])
        //                                             ->orderBy('opening_date', 'asc')
        //                                             ->get();

        //// 表示年月
        //if ($request->view_ym) {
        //
        //    // 表示候補にない場合は当月
        //    $view_ym = date("Y-m");
        //    foreach($opening_date_ym_rec as $opening_date_ym) {
        //        if ($opening_date_ym->opening_date_ym == $request->view_ym) {
        //            $view_ym = $request->view_ym;
        //        }
        //    }
        //}
        //else {
        //    $view_ym = date("Y-m");
        //}

        // 表示データ（過去の表示月数、未来の表示月数の範囲で年月取得）
        $view_after_ym_t = date("Y-m-t", strtotime($view_after_ym . "-01"));
        $opening_date_ym_rec = OpeningcalendarsDays::where('openingcalendars_id', '=', $openingcalendar_frame->openingcalendars_id)
                                                   ->where('opening_date', '>=', $view_before_ym . '-01')
                                                   ->where('opening_date', '<=', $view_after_ym_t)
                                                   ->orderBy('opening_date', 'asc')
                                                   ->get();
        // 配列に詰めなおし[年-月][日][プラン] ＆ 月の配列も生成[年-月]
        $view_days = array();
        $view_months = array();
        $view_months_patterns = array();
        foreach($opening_date_ym_rec as $opening_date_ym) {
            $view_days[substr($opening_date_ym->opening_date, 0, 7)][substr($opening_date_ym->opening_date, 8, 2)] = $opening_date_ym->openingcalendars_patterns_id;
            $view_months[substr($opening_date_ym->opening_date, 0, 7)] = array("data-prev" => null, "data-next" => null);
            $view_months_patterns[substr($opening_date_ym->opening_date, 0, 7)][$opening_date_ym->openingcalendars_patterns_id] = $patterns_array[$opening_date_ym->openingcalendars_patterns_id];
        }

        // 月ごとのパターンをソート
        foreach($view_months_patterns as &$view_months_pattern) {
            ksort($view_months_pattern);
            $view_months_pattern = array_chunk($view_months_pattern, 2);
        }

        //print_r($view_days);
        //print_r($view_months);
        //print_r($view_months_patterns);

        // 前、次の制御。それぞれ最後の一つ前にフラグon
        $count = count($view_months);
        $view_months2 = array();
        $default_disabled = array("prev" => null, "next" => null);
        $i = 0;

        // データがない場合は、前、次ボタンもOFF
        if (empty($view_months)) {
            $default_disabled = array("prev" => "off", "next" => "off");
        }
        foreach($view_months as $view_month => $view_month_value) {
            $i++;
            if ($i == 2) {
                $view_month_value["data-prev"] = "off";
            }
            if ($i == ($count - 1)) {
                $view_month_value["data-next"] = "off";
            }
            $view_month_value["data-prevmonth"] = date('F / Y', strtotime($view_month . "-01 - 1 month"));
            $view_month_value["data-nextmonth"] = date('F / Y', strtotime($view_month . "-01 + 1 month"));

            $view_months2[$view_month] = $view_month_value;

            // ボタンの初期disabled（前月がなければprev をoff、次月がなければnext をoff）
            if ($i == 1 && $view_month == $view_ym) {
                $default_disabled["prev"] = "off";
            }
            if ($i == $count && $view_month == $view_ym) {
                $default_disabled["next"] = "off";
            }
        }


        // カレンダー取得
        $calendars = array();
        foreach($view_months as $view_month => $value) {
            //$calendars[$view_month]['days'] = $this->getCalendarDates($view_month);
            $calendars[$view_month] = $this->getCalendarDates($view_month);
        }
        //print_r($calendars);

        $dates = $this->getCalendarDates($view_ym);
        //print_r($dates);

        // 月のコメント
        $months = OpeningcalendarsMonths::where('openingcalendars_id', $openingcalendar_frame->openingcalendars_id)
                                        ->where('month', '>=', $view_before_ym)
                                        ->where('month', '<=', $view_after_ym)
                                        ->orderBy('month', 'asc')
                                        ->get();
        foreach($months as $month) {
            $view_months2[$month->month]['comments'] = $month->comments;
        }
        //print_r($view_months2);


        // 表示テンプレートを呼び出す。
        return $this->view(
            'openingcalendars', [
            'openingcalendar_frame' => $openingcalendar_frame,
            'dates'                 => $dates,
            'view_ym'               => $view_ym,
            'view_ym_str'           => $view_ym_str,
            'view_days'             => $view_days,
            'calendars'             => $calendars,
            'patterns'              => $patterns,
            'patterns_chunks'       => $patterns_chunks,
            'view_months'           => $view_months2,
            'view_months_patterns'  => $view_months_patterns,
            'default_disabled'      => $default_disabled,
//            'months'                => $months,
        ]);
    }

    /**
     * カレンダー編集画面
     */
    public function edit($request, $page_id, $frame_id, $id = null, $errors = null)
    {
        // セッション初期化などのLaravel 処理。
        $request->flash();

        // Frame データ
        $openingcalendar_frame = $this->getOpeningcalendarFrame($frame_id);

        // パターンデータ
        $patterns = OpeningcalendarsPatterns::where("openingcalendars_id", $openingcalendar_frame->openingcalendars_id)
                                            ->orderBy('display_sequence', 'asc')
                                            ->get();

        // 編集する月
        if ($request->edit_ym) {
            $edit_ym = $request->edit_ym;
        }
        else {
            $edit_ym = date('Y-m');
        }

        // 編集候補（一番古いデータの年から、次の年まで）
        $opening_date_y_rec = OpeningcalendarsDays::select(DB::raw('substr(opening_date, 1, 4) as opening_date_y'))
                                                    ->where('openingcalendars_id', '=', $openingcalendar_frame->openingcalendars_id)
                                                    ->groupBy(DB::raw('substr(opening_date, 1, 4)'))
                                                    ->orderBy('opening_date_y', 'asc')
                                                    ->first();
        if (empty($opening_date_y_rec)) {
            $from_y = date('Y');
        }
        else {
            $from_y = $opening_date_y_rec->opening_date_y;
        }
        $select_ym_asc = array();
        for($i = $from_y; $i <= date('Y', strtotime('+1 year')); $i++) {
            for($j = 1; $j <= 12; $j++) {
                $select_ym_asc[sprintf('%04d', $i) . "-" . sprintf('%02d', $j)] = false;
            }
        }
        $select_ym = array_reverse($select_ym_asc);
        //print_r($select_ym);

        // 選択肢で既存データがあることを表すためのデータ確認
        $opening_date_yms = OpeningcalendarsDays::select(DB::raw('substr(opening_date, 1, 7) as opening_date_ym'))
                                                  ->where('openingcalendars_id', '=', $openingcalendar_frame->openingcalendars_id)
                                                  ->groupBy(DB::raw('substr(opening_date, 1, 7)'))
                                                  ->orderBy('opening_date_ym', 'asc')
                                                  ->get();
        foreach($opening_date_yms as $opening_date_ym) {
            $select_ym[$opening_date_ym->opening_date_ym] = true;
        }
        //print_r($select_ym);

        // 月の開館データ
        $openingcalendars_days = OpeningcalendarsDays::where('opening_date', 'LIKE', '%'.$edit_ym.'%')
                                                     ->where('openingcalendars_id', '=', $openingcalendar_frame->openingcalendars_id)
                                                     ->orderBy('opening_date', 'asc')
                                                     ->get();
        //print_r($openingcalendars_days);

        // 編集する月の配列
        $edit_days = array();
        $week_names = array();
        for($i = 1; $i <= date('t', strtotime($edit_ym . '-01')); $i++) {
            $edit_days[$i] = null;
            $week_names[$i] = $this->getWeekJp($edit_ym . '-' . sprintf('%02d', $i));
        }
        //print_r($week_names);

        // 月の開館データをマージ
        foreach($openingcalendars_days as $openingcalendars_day) {
            $edit_days[date('j', strtotime($openingcalendars_day->opening_date))] = $openingcalendars_day;
        }

        // 月のコメント
        $months = OpeningcalendarsMonths::where('month', $edit_ym)
                                        ->where('openingcalendars_id', $openingcalendar_frame->openingcalendars_id)
                                        ->first();
        if (empty($months)) {
            $months = new OpeningcalendarsMonths();
        }

        // 変更画面を呼び出す。(blade でold を使用するため、withInput 使用)
        return $this->view(
            'openingcalendars_edit', [
            'openingcalendar_frame' => $openingcalendar_frame,
            'edit_days'             => $edit_days,
            'patterns'              => $patterns,
            'edit_ym'               => $edit_ym,
            'select_ym'             => $select_ym,
            'week_names'            => $week_names,
            'months'                => $months,
        ])->withInput($request->all);
    }

    /**
     *  開館カレンダー記事登録処理
     */
    public function save($request, $page_id, $frame_id, $blogs_posts_id = null)
    {

        // 対象年月
        $target_ym = $request->target_ym;

        // Frame データ
        $openingcalendar_frame = $this->getOpeningcalendarFrame($frame_id);

        // あれば更新なければ追加
        if ($request->openingcalendars) {
            foreach($request->openingcalendars as $day => $patterns_id) {

                $date = $target_ym . '-' . sprintf('%02d', $day);

                OpeningcalendarsDays::updateOrCreate(['opening_date'                 => $date,
                                                      'openingcalendars_id'          => $openingcalendar_frame->openingcalendars_id],
                                                     ['openingcalendars_id'          => $openingcalendar_frame->openingcalendars_id,
                                                      'opening_date'                 => $date,
                                                      'openingcalendars_patterns_id' => $patterns_id,
                                                     ]);
            }
        }

        // 月のコメント
        if ($request->comments) {
            OpeningcalendarsMonths::updateOrCreate(['month'               => $target_ym,
                                                    'openingcalendars_id' => $openingcalendar_frame->openingcalendars_id],
                                                   ['openingcalendars_id' => $openingcalendar_frame->openingcalendars_id,
                                                    'comments'            => $request->comments]);
        }

        // 登録後は編集画面を呼ぶ。
        return $this->edit($request, $page_id, $frame_id);
    }

    /**
     *  削除処理
     */
    public function delete($request, $page_id, $frame_id, $id)
    {
    }

    /**
     * データ選択表示関数
     */
    public function listBuckets($request, $page_id, $frame_id, $id = null)
    {
        // Frame データ
        $openingcalendar_frame = $this->getOpeningcalendarFrame($frame_id);

        // データ取得（1ページの表示件数指定）
        $openingcalendars = Openingcalendars::orderBy('created_at', 'desc')
                            ->paginate(10);

        // 表示テンプレートを呼び出す。
        return $this->view(
            'openingcalendars_list_buckets', [
            'openingcalendar_frame' => $openingcalendar_frame,
            'openingcalendars'      => $openingcalendars,
        ]);
    }

    /**
     * 開館カレンダー新規作成画面
     */
    public function createBuckets($request, $page_id, $frame_id, $id = null, $create_flag = false, $message = null, $errors = null)
    {
        // 新規作成フラグを付けて開館カレンダー設定変更画面を呼ぶ
        $create_flag = true;
        return $this->editBuckets($request, $page_id, $frame_id, $id, $create_flag, $message, $errors);
    }

    /**
     * 開館カレンダー設定変更画面の表示
     */
    public function editBuckets($request, $page_id, $frame_id, $id = null, $create_flag = false, $message = null, $errors = null)
    {
        // セッション初期化などのLaravel 処理。
        $request->flash();

        // 開館カレンダー＆フレームデータ
        $openingcalendar_frame = $this->getOpeningcalendarFrame($frame_id);

        // 開館カレンダーデータ
        $openingcalendar = new Openingcalendars();

        // id が渡ってくればid が対象
        if (!empty($id)) {
            $openingcalendar = Openingcalendars::where('id', $id)->first();
        }
        // Frame のbucket_id があれば、bucket_id から開館カレンダーデータ取得、なければ、新規作成か選択へ誘導
        else if (!empty($openingcalendar_frame->bucket_id) && $create_flag == false) {
            $openingcalendar = Openingcalendars::where('bucket_id', $openingcalendar_frame->bucket_id)->first();

            // 開館カレンダーデータ
            if (empty($openingcalendar)) {
                $openingcalendar = new Openingcalendars();
            }
        }

        // 表示テンプレートを呼び出す。
        return $this->view(
            'openingcalendars_edit_openingcalendar', [
            'openingcalendar_frame' => $openingcalendar_frame,
            'openingcalendar'       => $openingcalendar,
            'create_flag'           => $create_flag,
            'message'               => $message,
            'errors'                => $errors,
        ])->withInput($request->all);
    }

    /**
     *  開館カレンダー登録処理
     */
    public function saveBuckets($request, $page_id, $frame_id, $id = null)
    {
        // 項目のエラーチェック
        $validator = Validator::make($request->all(), [
            'openingcalendar_name'     => ['required'],
            'openingcalendar_sub_name' => ['required'],
            'month_format'             => ['required'],
            'week_format'              => ['required'],
        ]);
        $validator->setAttributeNames([
            'openingcalendar_name'     => '開館カレンダー名',
            'openingcalendar_sub_name' => '開館カレンダー名（副題）',
            'month_format'             => '月の表示形式',
            'week_format'              => '週の表示形式',
        ]);

        // エラーがあった場合は入力画面に戻る。
        $message = null;
        if ($validator->fails()) {

            if (empty($id)) {
                $create_flag = true;
                return $this->createBuckets($request, $page_id, $frame_id, $id, $create_flag, $message, $validator->errors());
            }
            else  {
                $create_flag = false;
                return $this->editBuckets($request, $page_id, $frame_id, $id, $create_flag, $message, $validator->errors());
            }
        }

        // 更新後のメッセージ
        $message = null;

        // 画面から渡ってくるopeningcalendars_id が空ならバケツと開館カレンダーを新規登録
        if (empty($request->openingcalendars_id)) {

            // バケツの登録
            $bucket_id = DB::table('buckets')->insertGetId([
                  'bucket_name' => '無題',
                  'plugin_name' => 'openingcalendars'
            ]);

            // 開館カレンダーデータ新規オブジェクト
            $openingcalendars = new Openingcalendars();
            $openingcalendars->bucket_id = $bucket_id;

            // Frame のBuckets を見て、Buckets が設定されていなければ、作成したものに紐づける。
            // Frame にBuckets が設定されていない ＞ 新規のフレーム＆開館カレンダー作成
            // Frame にBuckets が設定されている ＞ 既存のフレーム＆開館カレンダー更新
            // （表示開館カレンダー選択から遷移してきて、内容だけ更新して、フレームに紐づけないケースもあるため）
            $frame = Frame::where('id', $frame_id)->first();
            if (empty($frame->bucket_id)) {

                // FrameのバケツIDの更新
                $frame = Frame::where('id', $frame_id)->update(['bucket_id' => $bucket_id]);
            }

            $message = '開館カレンダー設定を追加しました。';
        }
        // openingcalendars_id があれば、開館カレンダーを更新
        else {

            // 開館カレンダーデータ取得
            $openingcalendars = Openingcalendars::where('id', $request->openingcalendars_id)->first();

            $message = '開館カレンダー設定を変更しました。';
        }

        // 開館カレンダー設定
        $openingcalendars->openingcalendar_name     = $request->openingcalendar_name;
        $openingcalendars->openingcalendar_sub_name = $request->openingcalendar_sub_name;
        $openingcalendars->month_format             = $request->month_format;
        $openingcalendars->week_format              = $request->week_format;
        $openingcalendars->view_before_month        = $request->view_before_month;
        $openingcalendars->view_after_month         = $request->view_after_month;
        $openingcalendars->smooth_scroll            = $request->smooth_scroll;

        // データ保存
        $openingcalendars->save();

        // 新規作成フラグを付けて開館カレンダー設定変更画面を呼ぶ
        $create_flag = false;
        return $this->editBuckets($request, $page_id, $frame_id, $id, $create_flag, $message);
    }

    /**
     *  削除処理
     */
    public function destroyBuckets($request, $page_id, $frame_id, $blogs_id)
    {
    }

   /**
    * データ紐づけ変更関数
    */
    public function changeBuckets($request, $page_id = null, $frame_id = null, $id = null)
    {
        // FrameのバケツIDの更新
        Frame::where('id', $frame_id)
               ->update(['bucket_id' => $request->select_bucket]);

        // 表示開館カレンダー選択画面を呼ぶ
        return $this->listBuckets($request, $page_id, $frame_id, $id);
    }

    /**
     * パターン表示関数
     */
    public function listPatterns($request, $page_id, $frame_id, $id = null, $errors = null, $create_flag = false)
    {
        // セッション初期化などのLaravel 処理。
        $request->flash();

        // 権限チェック（listPatterns 関数は標準チェックにないので、独自チェック）
        if ($this->can('role_arrangement')) {
            return $this->view_error("403_inframe", null, '関数実行権限がありません。');
        }

        // 開館カレンダー
        $openingcalendar = $this->getOpeningcalendars($frame_id);
        if (empty($openingcalendar)) {
            $openingcalendar = new Openingcalendars;
        }

        // パターンデータ
        $openingcalendars_patterns = DB::table('frames')
                 ->select('frames.*',
                          'openingcalendars_patterns.id as openingcalendars_patterns_id',
                          'openingcalendars_patterns.caption',
                          'openingcalendars_patterns.color',
                          'openingcalendars_patterns.pattern',
                          'openingcalendars_patterns.display_sequence'
                         )
                 ->join('openingcalendars', 'openingcalendars.bucket_id', '=', 'frames.bucket_id')
                 ->join('openingcalendars_patterns', 'openingcalendars_patterns.openingcalendars_id', '=', 'openingcalendars.id')
                 ->where('frames.id', $frame_id)
                 ->orderBy('openingcalendars_patterns.display_sequence', 'asc')
                 ->get();

        // 表示テンプレートを呼び出す。
        return $this->view(
            'openingcalendars_list_patterns', [
            'patterns'            => $openingcalendars_patterns,
            'openingcalendar'     => $openingcalendar,
            'errors'              => $errors,
            'create_flag'         => $create_flag,
        ])->withInput($request->all);
    }

    /**
     *  パターン登録処理
     */
    public function savePatterns($request, $page_id, $frame_id, $id = null)
    {
        // 権限チェック（savePatterns 関数は標準チェックにないので、独自チェック）
        if ($this->can('role_arrangement')) {
            return $this->view_error("403_inframe", null, '関数実行権限がありません。');
        }

        // 追加項目のどれかに値が入っていたら、行の他の項目も必須
        if (!empty($request->add_display_sequence) || !empty($request->add_pattern) || !empty($request->add_color)) {

            // 項目のエラーチェック
            $validator = Validator::make($request->all(), [
                'add_display_sequence' => ['required'],
                'add_pattern'          => ['required'],
                'add_color'            => ['required'],
            ]);
            $validator->setAttributeNames([
                'add_display_sequence' => '追加行の表示順',
                'add_pattern'          => '追加行の開館時間',
                'add_color'            => '追加行の色',
            ]);

            if ($validator->fails()) {
                return $this->listPatterns($request, $page_id, $frame_id, $id, $validator->errors());
            }
        }

        // 既存項目のidに値が入っていたら、行の他の項目も必須
        if (!empty($request->openingcalendars_patterns_id)) {
            foreach($request->openingcalendars_patterns_id as $pattern_id) {

                // 項目のエラーチェック
                $validator = Validator::make($request->all(), [
                    'display_sequence.'.$pattern_id => ['required'],
                    'pattern.'.$pattern_id          => ['required'],
                    'color.'.$pattern_id            => ['required'],
                ]);
                $validator->setAttributeNames([
                    'display_sequence.'.$pattern_id => '表示順',
                    'pattern.'.$pattern_id          => '開館時間',
                    'color.'.$pattern_id            => '色',
                ]);

                if ($validator->fails()) {
                    return $this->listPatterns($request, $page_id, $frame_id, $id, $validator->errors());
                }
            }
        }

        // 開館カレンダー
        $openingcalendar = $this->getOpeningcalendars($frame_id);

        // 追加項目アリ
        if (!empty($request->add_display_sequence)) {
            OpeningcalendarsPatterns::create(['openingcalendars_id' => $openingcalendar->id,
                                              'display_sequence'    => intval($request->add_display_sequence),
                                              'caption'             => $request->add_caption,
                                              'pattern'             => $request->add_pattern,
                                              'color'               => $request->add_color
                                            ]);
        }

        // 既存項目アリ
        if (!empty($request->openingcalendars_patterns_id)) {

            foreach($request->openingcalendars_patterns_id as $openingcalendars_patterns_id) {

                // モデルオブジェクト取得
                $pattern_obj = OpeningcalendarsPatterns::where('id', $openingcalendars_patterns_id)->first();

                // データのセット
                $pattern_obj->caption          = $request->caption[$openingcalendars_patterns_id];
                $pattern_obj->color            = $request->color[$openingcalendars_patterns_id];
                $pattern_obj->pattern          = $request->pattern[$openingcalendars_patterns_id];
                $pattern_obj->display_sequence = $request->display_sequence[$openingcalendars_patterns_id];

                // 保存
                $pattern_obj->save();
            }
        }

        return $this->listPatterns($request, $page_id, $frame_id, $id, null, true);
    }

    /**
     *  パターン削除処理
     */
    public function deletePatterns($request, $page_id, $frame_id, $id = null)
    {
        // 権限チェック（deletePatterns 関数は標準チェックにないので、独自チェック）
        if ($this->can('role_arrangement')) {
            return $this->view_error("403_inframe", null, '関数実行権限がありません。');
        }

        // 削除
        OpeningcalendarsPatterns::where('id', $id)->delete();

        return $this->listPatterns($request, $page_id, $frame_id, $id, null, true);
    }

    /**
     * 年間カレンダーの編集画面
     */
    public function editYearschedule($request, $page_id, $frame_id, $id = null, $errors = null)
    {
        // 権限チェック（deletePatterns 関数は標準チェックにないので、独自チェック）
        if ($this->can('role_article')) {
            return $this->view_error("403_inframe", null, '関数実行権限がありません。');
        }

        // 開館カレンダー＆フレームデータ
        $openingcalendar_frame = $this->getOpeningcalendarFrame($frame_id);


        // 表示テンプレートを呼び出す。
        return $this->view(
            'openingcalendars_edit_yearschedule', [
            'openingcalendar_frame' => $openingcalendar_frame,
            'errors'                => $errors,
        ]);
    }

    /**
     *  年間カレンダー登録処理
     *
     * @return view
     */
    public function saveYearschedule($request, $page_id, $frame_id, $id = null)
    {
        // 権限チェック（deletePatterns 関数は標準チェックにないので、独自チェック）
        if ($this->can('role_article')) {
            return $this->view_error("403_inframe", null, '関数実行権限がありません。');
        }

        // 開館カレンダー＆フレームデータ
        $openingcalendar_frame = $this->getOpeningcalendarFrame($frame_id);

        // 年間カレンダーがアップロードされた。
        if ($request->hasFile('yearschedule_pdf')) {

            // PDFファイルチェック
            $validator = Validator::make($request->all(), [
                'yearschedule_pdf' => [
                    'required',
                    'file',
                    'mimes:pdf',
                    'mimetypes:application/pdf',
                ],
            ]);
            $validator->setAttributeNames([
                'yearschedule_pdf' => '年間カレンダーPDF',
            ]);
            if ($validator->fails()) {
                return ( $this->editYearschedule($request, $page_id, $frame_id, null, $validator->errors()->all()) );
            }

            // uploads テーブルに情報追加、ファイルのid を取得する
            $upload = Uploads::create([
                'client_original_name' => $request->file('yearschedule_pdf')->getClientOriginalName(),
                'mimetype'             => $request->file('yearschedule_pdf')->getClientMimeType(),
                'extension'            => $request->file('yearschedule_pdf')->getClientOriginalExtension(),
                'size'                 => $request->file('yearschedule_pdf')->getClientSize(),
                'plugin_name'          => 'openingcalendars',
             ]);

            // DBに情報保存
            Openingcalendars::where('id', $openingcalendar_frame->openingcalendars_id)
                            ->update(['yearschedule_uploads_id' => $upload->id, 'yearschedule_link_text' => $request->yearschedule_link_text]);

            // PDFファイル保存
            $directory = $this->getDirectory($upload->id);
            $upload_path = $request->file('yearschedule_pdf')->storeAs($directory, $upload->id . '.' . $request->file('yearschedule_pdf')->getClientOriginalExtension());

            //$path = $request->file('yearschedule_pdf')->storeAs('plugins/openingcalendars', $openingcalendar_frame->openingcalendars_id . '.pdf');
        }
        // 年間カレンダーがアップロードされなかった。
        else {
            // DBに情報保存
            Openingcalendars::where('id', $openingcalendar_frame->openingcalendars_id)
                            ->update(['yearschedule_link_text' => $request->yearschedule_link_text]);
        }

        // 年間カレンダーの削除。
        if ($request->has('delete_yearschedule_pdf') && $request->delete_yearschedule_pdf == '1') {

            // ファイル削除
            $directory = $this->getDirectory($openingcalendar_frame->yearschedule_uploads_id);
            Storage::delete($directory . '/' . $openingcalendar_frame->yearschedule_uploads_id . '.pdf');

            // uploads テーブルの情報削除
            Uploads::where('id', $openingcalendar_frame->yearschedule_uploads_id)->delete();

            // DBに情報保存
            Openingcalendars::where('id', $openingcalendar_frame->openingcalendars_id)
                            ->update(['yearschedule_uploads_id'=> null]);

        }

        // アップロード画面に戻る
        return $this->editYearschedule($request, $page_id, $frame_id, $id);
    }

    /**
     *  年間カレンダーPDF送出
     *
     */
    public function sendYearschedule($request, $page_id, $frame_id, $id = null)
    {

        // 開館カレンダー＆フレームデータ
        $openingcalendar_frame = $this->getOpeningcalendarFrame($frame_id);

        // ファイルを返す(PDFの場合はinline)
        $content_disposition = '';
        //return response()
        //         ->file( storage_path('app/plugins/openingcalendars/') . $openingcalendar_frame->openingcalendars_id . '.pdf');

        echo response()->file( storage_path('app/plugins/openingcalendars/') . $openingcalendar_frame->openingcalendars_id . '.pdf');
        exit;
    }
}
