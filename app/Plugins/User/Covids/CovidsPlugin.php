<?php

namespace App\Plugins\User\Covids;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

use File;
use DB;
use Session;
use Storage;

use App\Models\Common\Buckets;
use App\Models\Common\Frame;
use App\Models\User\Covids\Covid;
use App\Models\User\Covids\CovidDailyReport;

use App\Plugins\User\UserPluginBase;

/**
 * 感染症数値集計プラグイン
 *
 * 感染症数値を集計してグラフで表示するプラグイン。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 感染症数値集計プラグイン(covid)
 * @package Contoroller
 */
class CovidsPlugin extends UserPluginBase
{

    /**
     *  関数定義（コアから呼び出す）
     */
    public function getPublicFunctions()
    {
        // 標準関数以外で画面などから呼ばれる関数の定義
        $functions = array();
        $functions['get']  = [
            'getData',
        ];
        $functions['post'] = [
            'change',
            'importData',
            'pullData',
        ];
        return $functions;
    }

    /**
     *  権限定義
     */
    public function declareRole()
    {
        // 権限チェックテーブル
        $role_ckeck_table = array();
        $role_ckeck_table["getData"]        = array('role_arrangement');
        $role_ckeck_table["change"]         = array('role_arrangement');
        $role_ckeck_table["destroyBuckets"] = array('role_arrangement');
        return $role_ckeck_table;
    }

    /* オブジェクト変数 */

    /**
     * CSV とテーブルの項目合わせ
     */
    private $column_names = [
                'FIPS'                => 'fips',
                'Admin2'              => 'admin2',
                'Province/State'      => 'province_state',
                'Province_State'      => 'province_state',
                'Country/Region'      => 'country_region',
                'Country_Region'      => 'country_region',
                'Last Update'         => 'last_update',
                'Last_Update'         => 'last_update',
                'Latitude'            => 'lat',            // 03-01-2020 から
                'Lat'                 => 'lat',
                'Longitude'           => 'long_',          // 03-01-2020 から
                'Long_'               => 'long_',
                'Confirmed'           => 'confirmed',
                'Deaths'              => 'deaths',
                'Recovered'           => 'recovered',
                'Active'              => 'active',
                'Combined_Key'        => 'combined_key',
                'Incidence_Rate'      => 'combined_key',
                'Case-Fatality_Ratio' => 'case_fatality_ratio',
            ];

    /**
     * POSTデータ
     */
    public $post = null;

    /**
     *  Covid データ取得
     */
    private function getCovidFrame($frame_id)
    {
        $covid = Covid::select('covids.*')
                      ->join('frames', function ($join) use ($frame_id) {
                          $join->on('frames.bucket_id', '=', 'covids.bucket_id')
                               ->where('frames.id', '=', $frame_id);
                      })
                      ->first();
        return $covid;
    }

    /* 画面アクション関数 */

    /**
     *  データ初期表示関数
     *  コアがページ表示の際に呼び出す関数
     */
    public function index($request, $page_id, $frame_id)
    {
        // データ取得
        $frame = Frame::find($frame_id);
        $covid = Covid::firstOrNew(['bucket_id' => $frame->bucket_id]);

        // 集計データの日付のリスト
        $covid_report_days = CovidDailyReport::select('target_date')
                                             ->groupBy("target_date")
                                             ->orderBy('target_date', 'DESC')
                                             ->get();

        // 閲覧種類
        $view_type = 'table_daily_confirmed_desc';
        if ($request->filled('view_type')) {
            $view_type = $request->view_type;
        }

        // 対象日付
        $target_date = '';
        if (!$covid_report_days->isEmpty()) {
            $target_date = $covid_report_days->first()->target_date;  // 指定がないとデータのある最後の日
        }
        if ($request->filled('target_date')) {
            $target_date = $request->target_date;
        }

        // データのある最後の日
        $last_date = $covid_report_days->first()->target_date;

        // 表示件数
        $view_count = 5;
        if ($request->filled('view_count')) {
            $view_count = $request->view_count;
        }

        // 詳細データ取得
        if (strpos($view_type, 'graph_') === 0) {
            // グラフ
            $covid_daily_reports = $this->getGraphReports($covid, $view_type, $target_date, $last_date, $view_count);
            $template = 'covids_graph';
        } else {
            // 表
            $covid_daily_reports = $this->getDailyReports($covid, $view_type, $target_date, $view_count);
            $template = 'covids';
        }

        // 表示テンプレートを呼び出す。
        return $this->view(
            $template, [
            'covid' => $covid,
            'covid_daily_reports' => $covid_daily_reports,
            'covid_report_days'   => $covid_report_days,
            'view_type'           => $view_type,
            'target_date'         => $target_date,
            'view_count'          => $view_count,
            ]
        );
    }

    /**
     *  詳細データ取得関数（グラフ）
     */
    private function getGraphReports($covid, $view_type, $target_date, $last_date, $view_count)
    {
        /*
        -- 指定日期間の合計で上位を計算
        SELECT country_region
        FROM covid_daily_reports
        WHERE target_date > '2020-06-09'
        GROUP BY country_region
        ORDER BY SUM(confirmed) DESC
        LIMIT 5

        -- 上位の国で指定日付の期間の数値を取得
        SELECT country_region, target_date, SUM(confirmed) as total_confirmed
        FROM covid_daily_reports
        WHERE country_region IN (
          'US', 'Brazil', 'Russia', 'India', 'United Kingdom'
        )
          AND target_date > '2020-06-09'
        GROUP BY target_date, country_region
        ORDER BY SUM(confirmed) DESC
        */

        // 条件の編集(対象国の絞り込み、対象の詳細データ取得・絞り込みで使用)
        $cond = 'SUM(confirmed)';  // 初期値：感染者推移
        if ($view_type == 'graph_deaths') {
            $cond = 'SUM(deaths)';  // 死亡者推移
        } elseif ($view_type == 'graph_recovered') {
            $cond = 'SUM(recovered)';  // 回復者推移
        } elseif ($view_type == 'graph_active') {
            $cond = 'SUM(active)';  // 感染中推移
        } elseif ($view_type == 'graph_fatality_rate_moment') {
            $cond = 'TRUNCATE(SUM(deaths) / NULLIF(SUM(confirmed),0) * 100 + 0.05, 1)';  // 致死率(計算日)推移グラフ
        } elseif ($view_type == 'graph_fatality_rate_estimation') {
            $cond = 'TRUNCATE(SUM(deaths) / NULLIF((SUM(deaths) + SUM(recovered)),0) * 100 + 0.05, 1)';  // 致死率(予測)推移グラフ
        } elseif ($view_type == 'graph_deaths_estimation') {
            $cond = 'TRUNCATE(SUM(confirmed) * SUM(deaths) / NULLIF((SUM(deaths) + SUM(recovered)),0), 0)';  // 死亡者数(予測)推移グラフ
        } elseif ($view_type == 'graph_active_rate') {
            $cond = 'TRUNCATE(SUM(active) / NULLIF(SUM(confirmed),0) * 100 + 0.05, 1)';  // Active率推移グラフ
        }

        // 対象の国取得
        $country_recs = CovidDailyReport::select('country_region')
                                        ->where('target_date', $last_date)
                                        ->groupBy("country_region")
                                        ->orderByRaw($cond . ' DESC')
                                        ->limit($view_count)
                                        ->get();
        $countries = $country_recs->pluck('country_region');

        // 対象日付が空なら処理しない。
        if (empty($target_date)) {
            return array();
        }

        // 日付クラスに設定して日数計算
        $target_date_obj = new \DateTime($target_date);
        $last_date_obj = new \DateTime($last_date);
        $date_diff = $target_date_obj->diff($last_date_obj);

        // 対象の日付配列生成
        $target_dates = array();

        // 日付ループ
        for ($i = 0; $i < $date_diff->days + 1; $i++) {
            $target_dates[] = date('Y-m-d', strtotime('+' . $i . 'day', strtotime($target_date)));
        }

        // 最終的に画面で使用する配列を[日付][国]で作成する。
        // 詳細レコードはSQL で取得した後、日付、国のキーを見てデータにセットする。
        // それにより、詳細データの日付や国が抜けているレコードがあっても、結果がずれないようにする。
        $graph_table = array();

        // ヘッダー
        foreach ($countries as $country) {
            $graph_table['国'][] = $country;
        }
        // データエリア
        foreach ($target_dates as $target_date_item) {
            foreach ($countries as $country) {
                $graph_table[$target_date_item][$country] = 0;
            }
        }

        // 対象の詳細データ取得
        $raw_select = "country_region, target_date, ";
        $raw_select .= $cond . " as total_count ";
        $covid_daily_reports_query = CovidDailyReport::select(DB::raw($raw_select))
                                                     ->whereIn('country_region', $countries)
                                                     ->where('target_date', '>=', $target_date)
                                                     ->groupBy("target_date")
                                                     ->groupBy("country_region")
                                                     ->orderBy('target_date')
                                                     ->orderByRaw($cond . ' DESC');

        $covid_daily_reports = $covid_daily_reports_query->get();
        //Log::debug($covid_daily_reports);

        // あらかじめ、[日付][国]の配列を作成して、そこに値を入れていくことで、順番が保証される。
        foreach ($covid_daily_reports as $covid_daily_report) {
            if (array_key_exists($covid_daily_report->target_date, $graph_table) &&
                array_key_exists($covid_daily_report->country_region, $graph_table[$covid_daily_report->target_date])) {
                if (empty($covid_daily_report->total_count)) {
                    $graph_table[$covid_daily_report->target_date][$covid_daily_report->country_region] = 0;
                } else {
                    $graph_table[$covid_daily_report->target_date][$covid_daily_report->country_region] = $covid_daily_report->total_count;
                }
            }
        }
        //Log::debug($graph_table);

        return $graph_table;
    }

    /**
     *  詳細データ取得関数
     */
    private function getDailyReports($covid, $view_type, $target_date, $view_count)
    {
        // 集計データの取得
        $raw_select = "country_region, ";
        $raw_select .= "SUM(confirmed) as total_confirmed, ";
        $raw_select .= "SUM(deaths) as total_deaths, ";
        $raw_select .= "SUM(recovered) as total_recovered, ";
        $raw_select .= "SUM(active) as total_active, ";
        $raw_select .= "TRUNCATE(SUM(deaths) / NULLIF(SUM(confirmed),0) * 100 + 0.05, 1) as case_fatality_rate_moment, ";
        $raw_select .= "TRUNCATE(SUM(deaths) / NULLIF((SUM(deaths) + SUM(recovered)),0) * 100 + 0.05, 1) as case_fatality_rate_estimation, ";
        $raw_select .= "TRUNCATE(SUM(confirmed) * SUM(deaths) / NULLIF((SUM(deaths) + SUM(recovered)),0), 0) as deaths_estimation, ";
        $raw_select .= "TRUNCATE(SUM(active) / NULLIF(SUM(confirmed),0) * 100 + 0.05, 1) as active_rate ";

        $covid_daily_reports_query = CovidDailyReport::select(DB::raw($raw_select))
                                                     ->where('covid_id', $covid->id)
                                                     ->where('target_date', $target_date)
                                                     ->groupBy("target_date")
                                                     ->groupBy("country_region");

        // ソート
        if ($view_type == "table_daily_confirmed_desc") {
            $covid_daily_reports_query->orderByRaw('SUM(confirmed) DESC');
        } elseif ($view_type == "table_daily_confirmed_asc") {
            $covid_daily_reports_query->orderByRaw('SUM(confirmed) ASC');
        } elseif ($view_type == "table_daily_deaths_desc") {
            $covid_daily_reports_query->orderByRaw('SUM(deaths) DESC');
        } elseif ($view_type == "table_daily_deaths_asc") {
            $covid_daily_reports_query->orderByRaw('SUM(deaths) ASC');
        } elseif ($view_type == "table_daily_recovered_desc") {
            $covid_daily_reports_query->orderByRaw('SUM(recovered) DESC');
        } elseif ($view_type == "table_daily_recovered_asc") {
            $covid_daily_reports_query->orderByRaw('SUM(recovered) ASC');
        } elseif ($view_type == "table_daily_active_desc") {
            $covid_daily_reports_query->orderByRaw('SUM(active) DESC');
        } elseif ($view_type == "table_daily_active_asc") {
            $covid_daily_reports_query->orderByRaw('SUM(active) ASC');
        } elseif ($view_type == "table_daily_fatality_rate_moment_desc") {
            $covid_daily_reports_query->orderByRaw('TRUNCATE(SUM(deaths) / NULLIF(SUM(confirmed),0) * 100 + 0.05, 1) DESC');
        } elseif ($view_type == "table_daily_fatality_rate_estimation_desc") {
            $covid_daily_reports_query->orderByRaw('TRUNCATE(SUM(deaths) / NULLIF((SUM(deaths) + SUM(recovered)),0) * 100 + 0.05, 1) DESC');
        } elseif ($view_type == "table_daily_deaths_estimation_desc") {
            $covid_daily_reports_query->orderByRaw('TRUNCATE(SUM(confirmed) * SUM(deaths) / NULLIF((SUM(deaths) + SUM(recovered)),0), 0) DESC');
        } elseif ($view_type == "table_daily_active_rate_desc") {
            $covid_daily_reports_query->orderByRaw('TRUNCATE(SUM(active) / NULLIF(SUM(confirmed),0) * 100 + 0.05, 1) DESC');
        }

        // 第2ソート（国/地域）
        $covid_daily_reports_query->orderBy('country_region');

        // 表示件数
        if ($view_count != "all") {
            $covid_daily_reports_query->limit($view_count);
        }

        // get ＆ return
        $covid_daily_reports = $covid_daily_reports_query->get();
        return $covid_daily_reports;
    }

    /**
     * データセット新規作成画面
     */
    public function createBuckets($request, $page_id, $frame_id)
    {
        // 新規作成フラグを付けてデータセット設定変更画面を呼ぶ
        $create_flag = true;
        return $this->editBuckets($request, $page_id, $frame_id, $create_flag);
    }

    /**
     * データセット設定変更画面の表示
     */
    public function editBuckets($request, $page_id, $frame_id, $create_flag = false)
    {
        // データセット定義
        // 新規作成の場合は、空。変更の場合は配置されているフレームから引っ張ってくる。
        if ($create_flag) {
            $covid = new Covid();
        } else {
            $covid = $this->getCovidFrame($frame_id);
        }

        // 表示テンプレートを呼び出す。
        return $this->view(
            'edit_covid', [
            'covid'=> $covid,
            ]
        )->withInput($request->all);
    }

    /**
     *  データセット登録処理
     */
    public function saveBuckets($request, $page_id, $frame_id)
    {
        // デフォルトでチェック
        $validator_values['covids_name'] = ['required'];
        $validator_values['source_base_url'] = ['required'];

        $validator_attributes['covids_name'] = 'データセット名';
        $validator_attributes['source_base_url'] = 'データの基本URL';

        // 項目のエラーチェック
        $validator = Validator::make($request->all(), $validator_values);
        $validator->setAttributeNames($validator_attributes);

        // エラーがあった場合は入力画面に戻る。
        $message = null;
        if ($validator->fails()) {
            if ($request->filled('covid_id')) {
                return $this->editBuckets($request, $page_id, $frame_id, true)->withErrors($validator);
            } else {
                return $this->createBuckets($request, $page_id, $frame_id)->withErrors($validator);
            }
        }

        // Covid データの確認
        $covid = Covid::findOrNew($request->covid_id);

        // バケツデータ更新 or 追加
        $buckets = Buckets::updateOrCreate(
            ['id' => $covid->bucket_id],
            [
             'bucket_name' => $request->covids_name,
             'plugin_name' => 'covid',
            ]
        );

        // FrameのバケツIDの更新
        Frame::where('id', $frame_id)
                  ->update(['bucket_id' => $buckets->id]);

        // Covid データ更新 or 追加
        $covid = Covid::updateOrCreate(
            ['id' => $request->covid_id],
            ['bucket_id' => $buckets->id,
             'covids_name' => $request->covids_name,
             'source_base_url' => $request->source_base_url]
        );

        $this->cc_massage = 'Covid 設定を保存しました。';

        // Covid 変更画面を開く
        return $this->editBuckets($request, $page_id, $frame_id);
    }

    /**
     *  URL からデータのインポート
     */
    public function getData($request, $page_id, $frame_id)
    {
        // PHP のタイムアウトの変更
        //set_time_limit(3600);

        // フレームとCovid 定義の取得
        $frame = Frame::find($frame_id);
        $covid = Covid::firstOrNew(['bucket_id' => $frame->bucket_id]);

        // システム日付
        $start_date = date('Y-m-d');

        // CSV の確認
        $csv_last_date = '';
        $csv_next_date = '';
        $paths = File::glob(storage_path() . '/app/plugins/covids/' . $covid->id . '/*');
        if (!empty($paths)) {
            rsort($paths);
            $csv_last_date_mdy = pathinfo(basename($paths[0]))['filename'];
            $csv_last_date = date('Y-m-d', strtotime(str_replace('-', '/', $csv_last_date_mdy)));
            $csv_next_date = date('Y-m-d', strtotime('+1 day', strtotime($csv_last_date)));
        }

        // 集計データがあれば、その日の次の
        $covid_report_last_day = CovidDailyReport::select('target_date')
                                                 ->orderBy('target_date', 'DESC')
                                                 ->first();
        if (!empty($covid_report_last_day)) {
            $start_date = date('Y-m-d', strtotime('+1 day', strtotime($covid_report_last_day->target_date)));
        }

        // 画面で指定があった場合
        if ($request->has('start_date')) {
            $start_date = $request->start_date;
        }

        // 表示テンプレートを呼び出す。
        return $this->view(
            'get_data', [
            'covid'         => $covid,
            'start_date'    => $start_date,
            'csv_last_date' => $csv_last_date,
            'csv_next_date' => $csv_next_date,
            ]
        );
    }

   /**
    * データ削除関数
    */
    public function destroyBuckets($request, $page_id, $frame_id, $covid_id)
    {
        // covid_id がある場合、コンテンツを削除
        if ($covid_id) {
            // 削除のために Covids データ取得（以後、bucket_id はここから使用することで、Covids データがあるもので処理を保証）
            $covid = Covid::find($covid_id);

            // Covids 詳細データ
            $content = CovidDailyReport::where('covid_id', $covid->id)->delete();

            // bucket 削除
            Buckets::where('id', $covid->bucket_id)->delete();

            // CSV ファイルの削除
            Storage::deleteDirectory('plugins/covids/' . $covid->id);

            // Covids データ
            $covid->delete();
        }
        return;
    }

   /**
    * データ紐づけ変更関数
    */
    public function change($request, $page_id = null, $frame_id = null, $id = null)
    {
        // FrameのバケツIDの更新
        Frame::where('id', $frame_id)
               ->update(['bucket_id' => $request->select_bucket]);
        return;
    }

   /**
    * CSV データ取得
    */
    public function pullData($request, $page_id, $frame_id)
    {
        // フレームに紐づくcovid データの取得
        $covid = $this->getCovidFrame($frame_id);

        // 日付の指定チェック
        $csv_next_date = '';
        if (!$request->filled('csv_next_date')) {
            $this->cc_massage = '日付を指定してください。';
            return $this->getData($request, $page_id, $frame_id);
        }
        $csv_next_date = $request->csv_next_date;

        // 日付フォーマットを合わせて今日までを取得
        $today = date('Y-m-d');

        // 日付クラスに設定して日数計算
        $csv_next_date_obj = new \DateTime($csv_next_date);
        $today_obj = new \DateTime($today);
        $date_diff = $csv_next_date_obj->diff($today_obj);

        // 日付ループ
        $target_date = $csv_next_date;
        for ($i = 0; $i < $date_diff->days + 1; $i++) {
            $target_date = date('Y-m-d', strtotime('+' . $i . ' day', strtotime($csv_next_date)));
            //echo $target_date . "<br />";

            // ジョンズホプキンス大のCSV ファイル名の日付フォーマットである 月-日-年 に変更する。
            $csv_date = date('m-d-Y', strtotime(str_replace('-', '/', $target_date)));

            // データURL
            //$request_url = "https://raw.githubusercontent.com/CSSEGISandData/COVID-19/master/csse_covid_19_data/csse_covid_19_daily_reports/" . $csv_date . ".csv";
            $request_url = $covid->source_base_url . $csv_date . ".csv";

            // Github からデータ取得（HTTP レスポンスが gzip 圧縮されている）
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $request_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_ENCODING, "gzip");
            //リクエストヘッダ出力設定
            curl_setopt($ch, CURLINFO_HEADER_OUT, true);

            // データ取得実行
            $http_str = curl_exec($ch);

            // HTTPヘッダ取得
            $http_header = curl_getinfo($ch);
            if (empty($http_header) || !array_key_exists('http_code', $http_header) || $http_header['http_code'] != 200) {
                // データが取得できなかったため、スルー。
                break;
            }

            // ファイルに保存
            Storage::put('plugins/covids/' . $covid->id . '/' . $csv_date . '.csv', $http_str);
        }
        return $this->getData($request, $page_id, $frame_id);
    }

   /**
    * データ取り込み
    */
    public function importData($request, $page_id, $frame_id)
    {
        // 日付
        $start_date = null;
        if ($request->has('start_date')) {
            $start_date = $request->start_date;
        } else {
            $start_date = date('Y-m-d');  // 指定がなければ今日
        }
        //$start_date = '01-22-2020';

        // 日付フォーマットを合わせて今日までを取得
        $today = date('Y-m-d');

        // 日付クラスに設定して日数計算
        $start_date_obj = new \DateTime($start_date);
        $today_obj = new \DateTime($today);
        $date_diff = $start_date_obj->diff($today_obj);

        // 日付ループ
        $target_date = $start_date;
        for ($i = 0; $i < $date_diff->days + 1; $i++) {
            $target_date = date('Y-m-d', strtotime('+' . $i . ' day', strtotime($start_date)));
            //echo $target_date . "<br />";

            // 取り込み済みの日付はスルーする(hourly バッチで処理するイメージのため)
            $covid_daily_report_target_date = CovidDailyReport::where('target_date', $target_date)->first();
            if (!empty($covid_daily_report_target_date)) {
                continue;
            }

            // 日を指定してデータを取り込み
            $this->pullDateData($request, $page_id, $frame_id, $target_date);
        }

        return $this->getData($request, $page_id, $frame_id);
    }

   /**
    * データ取得
    */
    public function pullDateData($request, $page_id, $frame_id, $target_date)
    {
        // フレームに紐づくcovid データの取得
        $covid = $this->getCovidFrame($frame_id);

        // ジョンズホプキンス大のCSV ファイル名の日付フォーマットである 月-日-年 に変更する。
        $csv_date = date('m-d-Y', strtotime(str_replace('-', '/', $target_date)));

        // データファイル名
        $file_name = $csv_date . ".csv";

        // データ取得実行
        if (!Storage::exists('plugins/covids/' . $covid->id . '/' . $file_name)) {
            return;
        }

        $csv_str = Storage::get('plugins/covids/' . $covid->id . '/' . $file_name);

        // 一度、該当日付のデータを削除して取り込みなおす。
        CovidDailyReport::where('target_date', $target_date)->delete();

        // CSV 処理
        // str_getcsv は改行をうまく処理しなかったので、行にばらすのはexplode で実施
        $csv_lines = explode("\n", $csv_str);

        // CSV1行目（DBカラム名に編集する）
        $csv_header = null;

        // CSV 行の処理
        foreach ($csv_lines as $csv_line) {
            if (empty(trim($csv_line))) {
                continue;
            }

            // ヘッダでUTF8 のbom 付のデータが来たので、bom 削除
            $csv_line = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $csv_line);

            if (empty($csv_header)) {
                $csv_header_cols = str_getcsv($csv_line);
                foreach ($csv_header_cols as &$csv_header_col) {
                    if (array_key_exists($csv_header_col, $this->column_names)) {
                        $csv_header_col = $this->column_names[$csv_header_col];
                    }
                }
                $csv_header = $csv_header_cols;
                continue;
            }

            // 日毎のデータレコードのインスタンス作成
            $covid_daily_report = new CovidDailyReport();
            $covid_daily_report->covid_id = $covid->id;
            $covid_daily_report->target_date = $target_date;

            // 登録するカラムの代入
            $csv_body_cols = str_getcsv($csv_line);
            $index = 0;
            foreach ($csv_body_cols as $col_index => $csv_body_col) {
                $covid_daily_report->setAttribute($csv_header[$col_index], empty($csv_body_col) ? null : $csv_body_col);

                $index++;
            }
            $covid_daily_report->save();
        }
        return;
    }
}
