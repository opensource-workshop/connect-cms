<?php

namespace App\Plugins\User\Counters;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

use App\Models\Common\Buckets;
use App\Models\Common\Frame;
use App\Models\User\Counters\Counter;
use App\Models\User\Counters\CounterFrame;
use App\Models\User\Counters\CounterCount;

use App\Plugins\User\UserPluginBase;
use App\Enums\CounterDesignType;
use App\Enums\CsvCharacterCode;
use App\Utilities\Csv\CsvUtils;

/**
 * カウンター・プラグイン
 *
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category カウンター・プラグイン
 * @package Controller
 */
class CountersPlugin extends UserPluginBase
{
    /* オブジェクト変数 */

    /* コアから呼び出す関数 */

    /**
     * 関数定義（コアから呼び出す）
     */
    public function getPublicFunctions()
    {
        // 標準関数以外で画面などから呼ばれる関数の定義
        $functions = array();
        $functions['get']  = ['listCounters'];
        $functions['post'] = [];
        return $functions;
    }

    /**
     * 権限定義
     */
    public function declareRole()
    {
        // 権限チェックテーブル
        $role_check_table = [];
        $role_check_table["listCounters"] = ['frames.edit'];

        return $role_check_table;
    }

    /**
     * 編集画面の最初のタブ（コアから呼び出す）
     *
     * スーパークラスをオーバーライド
     */
    public function getFirstFrameEditAction()
    {
        return "editBuckets";
    }

    /**
     * POST取得関数（コアから呼び出す）
     * コアがPOSTチェックの際に呼び出す関数
     */
    // public function getPost($id)
    // {
    // }

    /* private関数 */

    /**
     * プラグインのフレーム
     */
    private function getPluginFrame($frame_id)
    {
        return CounterFrame::firstOrNew(['frame_id' => $frame_id]);
    }

    /**
     * プラグインのバケツ取得関数
     */
    private function getPluginBucket($bucket_id)
    {
        // プラグインのメインデータを取得する。
        return Counter::firstOrNew(['bucket_id' => $bucket_id]);
    }

    /* スタティック関数 */

    /**
     * 新着情報用メソッド
     */
    // public static function getWhatsnewArgs()
    // {
    //     // 戻り値('sql_method'、'link_pattern'、'link_base')
    //     $return = [];
    //     return $return;
    // }

    /**
     * 検索用メソッド
     */
    // public static function getSearchArgs($search_keyword)
    // {
    //     $return = [];
    //     return $return;
    // }

    /* 画面アクション関数 */

    /**
     * データ初期表示関数
     * コアがページ表示の際に呼び出す関数
     */
    public function index($request, $page_id, $frame_id)
    {
        // プラグインのフレームデータ
        $plugin_frame = $this->getPluginFrame($frame_id);

        $today_count = new CounterCount();

        // バケツ設定あり
        if (isset($this->frame) && $this->frame->bucket_id) {
            $counter = Counter::where('bucket_id', $this->frame->bucket_id)->first();

            if ($counter) {
                // 今日のカウント取得、なければ作成
                $today_count = CounterCount::getCountOrCreate($counter->id);

                // botチェック
                $is_bot = false;
                $ua = $request->header('User-Agent');
                $bots = [
                    'bot',
                    'spider',
                    'crawler',
                    'Linguee',
                    'proximic',
                    'GrapeshotCrawler',
                    'Mappy',
                    'MegaIndex',
                    'ltx71',
                    'integralads',
                    'Yandex',
                    'Y!',               // Yahoo!JAPAN
                    'Slurp',            // yahoo
                    'ichiro',           // goo
                    'goo_vsearch',      // goo
                    'gooblogsearch',    // goo
                    'netEstate',
                    'Yeti',             // Naver
                    'Daum',
                    'Seekport',
                    'Qwantify',
                    'GoogleImageProxy', // google
                    'QQBrowser',
                    'ManicTime',
                    'Hatena',
                    'PocketImageCache',
                    'Feedly',
                    'Tiny Tiny RSS',
                    'Barkrowler',
                    'SISTRIX Crawler',
                    'woorankreview',
                    'MegaIndex',
                    'Megalodon',
                    'Steeler',
                    'dataxu',
                    'ias-sg',
                    'go-resty',
                    'python-requests',
                    'meg',
                    'Scrapy',
                ];

                foreach ($bots as $bot) {
                    // 大文字小文字を区別せず ユーザーエージェントに bot が含まれているかチェック
                    if (strpos($ua, $bot) !== false) {
                        // bot
                        $is_bot = true;
                    }
                }

                if (! $is_bot) {
                    // セッションを利用
                    // セッション保持期間はデフォルト2時間（config/session.phpの'lifetime'参照）
                    $counter_histories = session('counter_histories', '');
                    $counter_histories_array = explode(':', $counter_histories);

                    if (! in_array($counter->id, $counter_histories_array)) {
                        // カウントアップ
                        $today_count->day_count++;
                        $today_count->total_count++;
                        $today_count->save();

                        // session = カウンターid & 区切り文字
                        $counter_histories = $counter_histories . ':' . $counter->id;
                        // 先頭の':'を削除
                        $counter_histories = ltrim($counter_histories, ':');

                        // カウントしたカウンターIDを記録
                        session(['counter_histories' => $counter_histories]);
                    }
                }
            }

            // 表示テンプレートを呼び出す。
            return $this->view('index', [
                'plugin_frame' => $plugin_frame,
                'counter_count' => $today_count,
            ]);
        } else {
            // バケツ空テンプレートを呼び出す。
            return $this->view('empty_bucket');
        }
    }

    /**
     * プラグインのバケツ選択表示関数
     */
    public function listBuckets($request, $page_id, $frame_id, $id = null)
    {
        // $plugin_buckets = Counter::orderBy('created_at', 'desc')->paginate(10);
        $plugin_buckets = Counter::
                select(
                    'counters.id',
                    'counters.bucket_id',
                    'counters.created_at',
                    'counters.name',
                    DB::raw('max(counter_counts.total_count) as total_count')
                    // DB::raw('max(counter_counts.counted_at) as counted_at')
                )
                ->leftJoin('counter_counts', 'counter_counts.counter_id', '=', 'counters.id')
                ->groupBy(
                    'counters.id',
                    'counters.bucket_id',
                    'counters.created_at',
                    'counters.name'
                )
                ->orderBy('created_at', 'desc')
                ->paginate(10);

        if ($plugin_buckets->isEmpty()) {
            // バケツ空テンプレートを呼び出す。
            return $this->view('empty_bucket_setting');
        }

        // 表示テンプレートを呼び出す。
        return $this->view('list_buckets', [
            'plugin_buckets' => $plugin_buckets,
        ]);
    }

    /**
     * バケツ新規作成画面
     */
    public function createBuckets($request, $page_id, $frame_id)
    {
        // 処理的には編集画面を呼ぶ
        return $this->editBuckets($request, $page_id, $frame_id);
    }

    /**
     * フレーム表示設定画面の表示
     */
    public function editView($request, $page_id, $frame_id)
    {
        // 表示中のバケツデータ
        $counter = $this->getPluginBucket($this->getBucketId());

        if (empty($counter->id)) {
            // バケツ空テンプレートを呼び出す。
            return $this->view('empty_bucket_setting');
        }

        // 表示テンプレートを呼び出す。
        return $this->view('frame', [
            // 表示中のバケツデータ
            'counter' => $counter,
            'counter_frame' => $this->getPluginFrame($frame_id),
            'counter_count' => CounterCount::getCountOrCreate($counter->id),
        ]);
    }

    /**
     * フレーム表示設定の保存
     */
    public function saveView($request, $page_id, $frame_id, $counter_id)
    {
        // フレームごとの表示設定の更新
        $counter_frame = CounterFrame::updateOrCreate(
            ['frame_id' => $frame_id],
            [
                'design_type' => $request->design_type,
                'use_total_count'  => (int)$request->use_total_count,
                'use_today_count' => (int)$request->use_today_count,
                'use_yesterday_count' => (int)$request->use_yesterday_count,
                'total_count_title' => $request->total_count_title,
                'today_count_title' => $request->today_count_title,
                'yesterday_count_title' => $request->yesterday_count_title,
                'total_count_after' => $request->total_count_after,
                'today_count_after' => $request->today_count_after,
                'yesterday_count_after' => $request->yesterday_count_after,
            ],
        );

        return;
    }

    /**
     * バケツ設定変更画面の表示
     */
    public function editBuckets($request, $page_id, $frame_id)
    {
        // コアがbucket_id なしで呼び出してくるため、bucket_id は frame_id から探す。
        if ($this->action == 'createBuckets') {
            $bucket_id = null;
        } else {
            $bucket_id = $this->getBucketId();
        }

        // 表示中のバケツデータ
        $counter = $this->getPluginBucket($bucket_id);

        if (empty($counter->id) && $this->action != 'createBuckets') {
            // バケツ空テンプレートを呼び出す。
            return $this->view('empty_bucket_setting');
        }

        // 表示テンプレートを呼び出す。
        return $this->view('bucket', [
            'counter' => $counter,
        ]);
    }

    /**
     * バケツ登録処理
     */
    public function saveBuckets($request, $page_id, $frame_id, $bucket_id = null)
    {
        // 項目のエラーチェック
        $validator = Validator::make($request->all(), [
            'name' => ['required'],
            'initial_count' => ['nullable', 'numeric'],
        ]);
        $validator->setAttributeNames([
            'name' => 'カウンター名',
            'initial_count' => '初期カウント数',
        ]);

        // エラーがあった場合は入力画面に戻る。
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // バケツの取得。なければ登録。
        $bucket = Buckets::updateOrCreate(
            ['id' => $bucket_id],
            ['bucket_name' => $request->name, 'plugin_name' => 'counters'],
        );

        // フレームにバケツの紐づけ
        $frame = Frame::find($frame_id)->update(['bucket_id' => $bucket->id]);

        // プラグインバケツを取得(なければ新規オブジェクト)
        // プラグインバケツにデータを設定して保存
        $counter = $this->getPluginBucket($bucket->id);
        $counter->name = $request->name;
        $counter->save();

        // プラグインフレームが無ければ作成
        // bugfix: 新規作成時に表示設定が初期化されるバグ修正
        // $counter_frame = CounterFrame::updateOrCreate(
        $counter_frame = CounterFrame::firstOrCreate(
            ['frame_id' => $frame_id],
            [
                'frame_id' => $frame_id,
                // 項目名初期値
                'design_type' => CounterDesignType::numeric,
                'total_count_title' => '累計',
                'today_count_title' => '今日',
                'yesterday_count_title' => '昨日',
            ]
        );

        // 登録時
        if (is_null($bucket_id)) {
            $counter_count = CounterCount::create([
                'counter_id' => $counter->id,
                'counted_at' => now()->format('Y-m-d'),
                'day_count' => (int)$request->initial_count ?? 0,
                'total_count' => (int)$request->initial_count ?? 0,
            ]);
        }

        // 登録後はリダイレクトして編集ページを開く。
        return new Collection(['redirect_path' => url('/') . "/plugin/counters/editBuckets/" . $page_id . "/" . $frame_id . "/" . $bucket->id . "#frame-" . $frame_id]);
    }

    /**
     * 削除処理
     */
    public function destroyBuckets($request, $page_id, $frame_id, $counter_id)
    {
        // deleted_id, deleted_nameを自動セットするため、複数件削除する時はdestroy()を利用する。

        // プラグインバケツの取得
        $counter = Counter::find($counter_id);
        if (empty($counter)) {
            return;
        }

        // カウントデータ削除
        // see) https://readouble.com/laravel/5.5/ja/collections.html#method-pluck
        $counter_count_ids = CounterCount::where('counter_id', $counter->id)->pluck('id');
        CounterCount::destroy($counter_count_ids);

        // FrameのバケツIDの更新
        Frame::where('bucket_id', $counter->bucket_id)->update(['bucket_id' => null]);

        // delete: バケツ削除時に表示設定は消さない. 今後フレーム削除時にプラグイン側で追加処理ができるようになったら counter_frame を削除する
        // プラグインフレームデータの削除(deleted_id を記録するために1回読んでから削除)
        // $counter_frame = CounterFrame::where('frame_id', $frame_id)->first();
        // $counter_frame->delete();

        // バケツ削除
        Buckets::destroy($counter->bucket_id);

        // プラグインデータ削除
        $counter->delete();
    }

    /**
     * データ紐づけ変更関数
     */
    public function changeBuckets($request, $page_id, $frame_id)
    {
        // FrameのバケツIDの更新
        Frame::where('id', $frame_id)->update(['bucket_id' => $request->select_bucket]);

        // delete: バケツ切替時、表示設定のフレームIDは同じため、更新不要
        // // Counters の特定
        // $plugin_bucket = $this->getPluginBucket($request->select_bucket);

        // // フレームごとの表示設定の更新
        // $counter_frame = $this->getPluginFrame($frame_id);
        // $counter_frame->counter_id = $plugin_bucket->id;
        // $counter_frame->frame_id = $frame_id;
        // $counter_frame->save();
    }

    /**
     * データベースデータダウンロード
     */
    public function downloadCsv($request, $page_id, $frame_id, $id)
    {
        // カラム
        $columns = [
            'counted_at' => 'カウント日',
            'total_count' => '累計カウント',
            'day_count' => '当日カウント',
        ];

        // 返却用配列
        $csv_array = array();

        // 見出し行
        foreach ($columns as $columnKey => $column) {
            $csv_array[0][$columnKey] = $column;
        }

        // カウントデータを取得
        $counter_counts = CounterCount::where('counter_id', $id)
                ->orderBy('counted_at', 'asc')
                ->get();

        // 行数
        $csv_line_no = 1;

        // データ
        foreach ($counter_counts as $counter_count) {
            $csv_line = [];
            foreach ($columns as $columnKey => $column) {
                if ($columnKey == 'counted_at') {
                    // 日付はフォーマットを整えて出力
                    $csv_line[$columnKey] = $counter_count->$columnKey->format('Y/m/d');
                } else {
                    $csv_line[$columnKey] = $counter_count->$columnKey;
                }
            }

            $csv_array[$csv_line_no] = $csv_line;
            $csv_line_no++;
        }

        // カウントデータを取得
        $counter = Counter::firstOrNew(['id' => $id]);

        // レスポンス版
        // $filename = 'counter_counts.csv';
        $filename = $counter->name . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        // データ
        $csv_data = '';
        foreach ($csv_array as $csv_line) {
            foreach ($csv_line as $csv_col) {
                $csv_data .= '"' . $csv_col . '",';
            }
            // 末尾カンマを削除
            $csv_data = substr($csv_data, 0, -1);
            $csv_data .= "\n";
        }

        // Log::debug(var_export($request->character_code, true));

        // 文字コード変換
        if ($request->character_code == CsvCharacterCode::utf_8) {
            $csv_data = mb_convert_encoding($csv_data, CsvCharacterCode::utf_8);
            // UTF-8のBOMコードを追加する(UTF-8 BOM付きにするとExcelで文字化けしない)
            $csv_data = CsvUtils::addUtf8Bom($csv_data);
        } else {
            $csv_data = mb_convert_encoding($csv_data, CsvCharacterCode::sjis_win);
        }

        return response()->make($csv_data, 200, $headers);
    }

    /**
     * プラグインのバケツ選択表示関数
     */
    public function listCounters($request, $page_id, $frame_id, $id = null)
    {
        // 表示中のバケツデータ
        $counter = $this->getPluginBucket($this->getBucketId());

        $counter = new Counter();
        if (!empty($id)) {
            // id が渡ってくれば id が対象
            $counter = Counter::where('id', $id)->first();
        } else {
            // 表示中のバケツデータ
            $counter = $this->getPluginBucket($this->getBucketId());
        }

        if (empty($counter->id)) {
            // バケツ空テンプレートを呼び出す。
            return $this->view('empty_bucket_setting');
        }


        // １ヵ月想定で30件を 日付降順 で取得
        $counter_counts = CounterCount::where('counter_id', $counter->id)
                ->orderBy('counted_at', 'desc')->paginate(30);

        // 表示テンプレートを呼び出す。
        return $this->view('list_counters', [
            'counter_counts' => $counter_counts,
            'counter' => $counter,
        ]);
    }
}
