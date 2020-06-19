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
        $role_ckeck_table["getData"]   = array('role_arrangement');
        $role_ckeck_table["change"]   = array('role_arrangement');
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

    /**
     *  データ取得
     */
    private function getDailyReports($frame_id)
    {
        // buckets_id
        $buckets_id = null;
        if (!empty($this->buckets)) {
            $buckets_id = $this->buckets->id;
        }

        // Bucketsに応じたデータを返す。

        $covid_daily_reports = 
            CovidDailyReport::select(
                DB::raw("country_region, sum(confirmed) as sum_confirmed, sum(deaths) as sum_deaths")
            )
            ->groupBy("country_region")
            ->get();

        return $covid_daily_reports;
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

        // 対象日付
        $target_date = '';
        if (!$covid_report_days->isEmpty()) {
            $target_date = $covid_report_days->first()->target_date;  // 指定がないとデータのある最後の日
        }
        if ($request->filled('target_date')) {
            $target_date = $request->target_date;
        }

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

        $covid_daily_reports = CovidDailyReport::select(DB::raw($raw_select))
                                               ->where('covid_id', $covid->id)
                                               ->where('target_date', $target_date)
                                               ->groupBy("target_date")
                                               ->groupBy("country_region")
                                               ->orderByRaw('SUM(confirmed) DESC')
                                               ->orderBy('country_region')
//                                               ->having('case_fatality_rate_estimation', '<', 50)
                                               ->limit(5)
                                               ->get();

        // 表示テンプレートを呼び出す。
        return $this->view(
            'covids', [
            'covid' => $covid,
            'covid_daily_reports' => $covid_daily_reports,
            'covid_report_days'   => $covid_report_days,
            'target_date'         => $target_date,
            ]
        );
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
        }
        else {
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
     * データ編集用表示関数
     * コアが編集画面表示の際に呼び出す関数
     */
    public function edit($request, $page_id, $frame_id, $id = null)
    {
        // データ取得
        $contents = $this->getFrameContents($frame_id);

        // データがない場合は、新規登録用画面
        if (empty($contents)) {
            // 新規登録画面を呼び出す
            return $this->view(
                'contents_create', [
                ]
            );
        } else {
            // 編集画面テンプレートを呼び出す。
            return $this->view(
                'contents_edit', [
                'contents' => $contents,
                ]
            );
        }
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
        $paths = File::glob(storage_path() . '/app/plugins/covids/*');
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
     *  データ詳細表示関数
     *  コアがデータ削除の確認用に呼び出す関数
     */
    public function show($request, $page_id, $frame_id, $id = null)
    {
        // 権限チェック
        // 固定記事プラグインの特別処理。削除のための表示であり、フレーム画面のため、個別に権限チェックする。
        if ($this->can('frames.delete')) {
            return $this->view_error(403);
        }

        // データ取得
        $contents = $this->getFrameContents($frame_id);

        // データの存在確認をして、画面を切り替える
        if (empty($contents)) {
            // データなしの表示テンプレートを呼び出す。
            return $this->view(
                'contents_edit_nodata', [
                'contents' => null,
                ]
            );
        }

        // 表示テンプレートを呼び出す。
        return $this->view(
            'contents_show', [
            'contents' => $contents,
            ]
        );
    }

   /**
    * データ新規登録関数
    */
    public function store($request, $page_id = null, $frame_id = null, $id = null, $status = 0)
    {
        // バケツがまだ登録されていなかったら登録する。
        if (empty($this->buckets)) {
            $bucket_id = DB::table('buckets')->insertGetId([
                  'bucket_name' => '無題',
                  'plugin_name' => 'contents'
            ]);
        } else {
            $bucket_id = $this->buckets['id'];
        }

        // コンテンツデータの登録
        $contents = new Contents;
        $contents->created_id   = Auth::user()->id;
        $contents->bucket_id    = $bucket_id;
        $contents->content_text = $request->contents;

        // 一時保存(status が 1 になる。)
        if ($status == 1) {
            $contents->status = 1;
        } elseif ($this->isApproval($frame_id)) {
            // 承認フラグ(要承認の場合はstatus が 2 になる。)
            $contents->status = 2;
        } else {
            $contents->status = 0;
        }

        $contents->save();

        // FrameのバケツIDの更新
        Frame::where('id', $frame_id)
                  ->update(['bucket_id' => $bucket_id]);

        return;
    }

   /**
    * データ更新（確定）関数
    */
    public function update($request, $page_id = null, $frame_id = null, $id = null)
    {
        // 新しいレコードの登録（旧レコードのコピー＆内容の入れ替え）
        $oldrow = Contents::find($id);

        // 新しいレコードの登録（旧レコードのコピー＆内容の入れ替え）
        $newrow = $oldrow->replicate();
        $newrow->content_text = $request->contents;

        // 承認フラグ(要承認の場合はstatus が2 になる。)
        if ($this->isApproval($frame_id)) {
            $newrow->status = 2;
        } else {
            $newrow->status = 0;
        }

        // 旧レコードのstatus 更新(Activeなもの(status:0)は、status:9 に更新。他はそのまま。)ただし、承認待ちレコード作成時は対象外
        if ($newrow->status != 2) {
            Contents::where('bucket_id', $oldrow->bucket_id)->where('status', 0)->update(['status' => 9]);
        }
        //Contents::where('id', $oldrow->id)->update(['status' => 9]);

        // 変更のデータ保存
        $newrow->save();

        return;
    }

   /**
    * データ一時保存関数
    */
    public function temporarysave($request, $page_id = null, $frame_id = null, $id = null)
    {
        // 新規で一時保存しようとしたときは id、レコードがまだない。
        if (empty($id)) {
            $status = 1;
            $this->store($request, $page_id, $frame_id, $id, $status);
        } else {
            // 旧データ取得
            $oldrow = Contents::find($id);

            // 旧レコードが表示でなければ、履歴に更新（表示を履歴に更新すると、画面に表示されなくなる）
// 過去のステータスも残す方式にする。
//            if ($oldrow->status != 0) {
//                Contents::where('id', $id)->update(['status' => 9]);
//            }

            // 新しいレコードの登録（旧レコードのコピー＆内容の入れ替え）
            $newrow = $oldrow->replicate();
            $newrow->content_text = $request->contents;
            $newrow->status = 1; //（一時保存）
            $newrow->save();
        }
        return;
    }

   /**
    * 承認
    */
    public function approval($request, $page_id = null, $frame_id = null, $id = null)
    {
        // 新しいレコードの登録（旧レコードのコピー＆内容の入れ替え）
        $oldrow = Contents::find($id);

        // 旧レコードのstatus 更新(Activeなもの(status:0)は、status:9 に更新。他はそのまま。)
        Contents::where('bucket_id', $oldrow->bucket_id)->where('status', 0)->update(['status' => 9]);

        // 新しいレコードの登録（旧レコードのコピー＆内容の入れ替え）
        $newrow = $oldrow->replicate();
        $newrow->status = 0;
        $newrow->save();

        return;
    }

   /**
    * データ削除関数
    */
    public function delete($request, $page_id = null, $frame_id = null, $id = null)
    {
        // id がある場合、コンテンツを削除
        if ($id) {
            // Contents データ
            $content = Contents::where('id', $id)->first();

            // フレームも同時に削除するがチェックされていたらフレームを削除する。
            if ($request->frame_delete_flag == "1") {
                Frame::destroy($frame_id);
            }

            // 論理削除のため、コンテンツデータを status:9 に変更する。バケツデータは削除しない。
// 過去のステータスも残す方式にする。
//            Contents::where('id', $id)->update(['status' => 9]);

            // 削除ユーザの更新
            Contents::where('bucket_id', $content->bucket_id)->update(['deleted_id' => Auth::user()->id, 'deleted_name' => Auth::user()->name]);

            // 同じbucket_id のものを削除
            Contents::where('bucket_id', $content->bucket_id)->delete();
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
            Storage::put('plugins/covids/' . $csv_date . '.csv', $http_str);
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
        if (!Storage::exists('plugins/covids/' . $file_name)) {
            return;
        }

        $csv_str = Storage::get('plugins/covids/' . $file_name);

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

            // 追加項目の計算
//            if (!empty($covid_daily_report->deaths)) {
//                $covid_daily_report->case_fatality_rate_moment = $covid_daily_report->deaths / $covid_daily_report->confirmed;
//            }

            $covid_daily_report->save();
        }
        //return $this->getData($request, $page_id, $frame_id);
        return;
    }
}
