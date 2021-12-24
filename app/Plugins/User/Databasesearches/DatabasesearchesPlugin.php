<?php

namespace App\Plugins\User\Databasesearches;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

use App\Models\Common\Buckets;
use App\Models\Common\Frame;
use App\Models\User\Databases\DatabasesInputCols;
use App\Models\User\Databasesearches\Databasesearches;

use App\Plugins\User\UserPluginBase;
use App\Plugins\User\Databases\DatabasesTool;

use App\Enums\DatabaseSearcherSortFlag;

use function Psy\debug;

/**
 * データベース検索プラグイン
 *
 * データベースの検索方法を持つプラグイン
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category データベース検索プラグイン
 * @package Controller
 */
class DatabasesearchesPlugin extends UserPluginBase
{
    /* オブジェクト変数 */

    /**
     * POST チェックに使用する getPost() 関数を使うか
     */
    public $use_getpost = false;

    /* コアから呼び出す関数 */

    /**
     *  編集画面の最初のタブ
     *
     *  スーパークラスをオーバーライド
     */
    public function getFirstFrameEditAction()
    {
        return "editBuckets";
    }

    /**
     *  関数定義（コアから呼び出す）
     */
    public function getPublicFunctions()
    {
        // 標準関数以外で画面などから呼ばれる関数の定義
        $functions = array();
        $functions['get']  = ['index'];
        $functions['post'] = ['index', 'change'];
        return $functions;
    }

    /**
     *  権限定義
     */
    public function declareRole()
    {
        // 権限チェックテーブル
        $role_check_table = array();
        $role_check_table["change"]      = array('frames.change');
        return $role_check_table;
    }

    /* 画面アクション関数 */

    /**
     *  初期表示取得関数
     *
     *  条件が複数あれば、条件毎にSQL 発行
     *  取得した行のID が全条件にあるものを対象とする。<- データがカラムごとに縦持ちなので。
     *  全条件でヒットした行ID で必要データ再取得 <- ページネートのため。
     *
     *  (ex.) 当日：0315 結果：LineID = 1 のみ対象
     *                         条件1,    条件2
     *  LineID, Column, Value, FromDate, ToDate
     *       1,   From,  0301,       ○          ：LineID = 1 は条件全てにMatch
     *       1,     To,  0331,         , ○      ：
     *       2,   From,  0401,                   ：LineID = 2 は条件2 のみMatch
     *       2,     To,  0431,         , ○      ：
     *       3,   From,  0201,       ○          ：LineID = 3 は条件1 のみMatch
     *       3,     To,  0314,                   ：
     *
     * @return view
     */
    public function index($request, $page_id, $frame_id)
    {
        // フレームデータ
        $frames = Frame::find($frame_id);

        // データベース検索設定データ
        if ($frames->bucket_id) {
            $databasesearches = Databasesearches::where('bucket_id', $frames->bucket_id)->first();
        } else {
            $databasesearches = new Databasesearches();
        }

        // データベース検索設定がまだの場合の対応
        if (empty($databasesearches->condition)) {
            $condition_str = json_decode('{}'); // 空のJSONオブジェクト
        } else {
            $condition_str = json_decode($databasesearches->condition);
        }

        // 複数条件に対応するため、一旦、条件を配列にする。
        if (is_array($condition_str)) {
            $conditions = $condition_str;
        } else {
            $conditions = array($condition_str);
        }

        // 全ての「カラム」と「表示設定の絞り込み条件」の取得
        $columns = DatabasesTool::getDatabasesColumnsAndFilterSearchAll();
        // フレーム（データベース指定）
        if ($databasesearches->frame_select == 1 && $databasesearches->target_frame_ids) {
            $columns->whereIn('frames.id', explode(',', $databasesearches->target_frame_ids));
        }
        $columns = $columns->get();

        // 権限によって非表示columのdatabases_columns_id配列を取得する（各データベースの項目毎で権限によって非表示）
        $hide_columns_ids = (new DatabasesTool())->getHideColumnsIds($columns, 'list_detail_display_flag');
        // Log::debug(var_export($columns->get()->toArray(), true));
        // var_dump($hide_columns_ids);
        // dd($hide_columns_ids);

        // 各データベースのフレームの表示設定
        $databases_frames_settings = DatabasesTool::getDatabasesFramesSettings($columns);

        // 登録データ行の取得 --->

        // JSON 形式
        //  [name] => エリア
        //  [where] => { ALL | PART | FRONT | REAR | GT | LT | GE | LE }
        //  [request] => (ex.)area
        //  [request_default] => { 文字列 (ex.)北海道 | TODAY-MD }

        // 条件の例
        // {"name":"From月日","where":"LE","request_default":"TODAY-MD"}

        // 複数の条件の例
        // [{"name":"From月日","where":"LE","request_default":"TODAY-MD"},{"name":"To月日","where":"GE","request_default":"TODAY-MD"}]

        // 条件毎にループ
        foreach ($conditions as $condition) {
            // 検索Query 組み立て
            $inputs_query = DatabasesInputCols::select('databases_inputs_id', 'frames.id as frames_id', 'frames.page_id')
                                                ->join('databases_columns', 'databases_columns.id', '=', 'databases_input_cols.databases_columns_id')
                                                ->join('databases', 'databases.id', '=', 'databases_columns.databases_id')
                                                ->join('frames', 'frames.bucket_id', '=', 'databases.bucket_id');

            // フレーム（データベース指定）
            if ($databasesearches->frame_select == 1 && $databasesearches->target_frame_ids) {
                $inputs_query->whereIn('frames.id', explode(',', $databasesearches->target_frame_ids));
            }

            // 全データベースの検索キーワードの絞り込み と カラムの絞り込み (各データベースのフレームの表示設定)
            $inputs_query = DatabasesTool::appendSearchKeywordAndSearchColumnsAllDb(
                'databases_inputs_id',
                $inputs_query,
                $databases_frames_settings,
                $hide_columns_ids
            );

            // カラム指定
            if (property_exists($condition, 'name') && $condition->name) {
                if ($condition->name == 'ALL') {
                    // name が ALL を指定されていたら、カラムを特定しない。
                } else {
                    $inputs_query->where('databases_columns.column_name', $condition->name);
                }
            }

            // 検索キーワードの取得
            $request_keyword = '';
            if (property_exists($condition, 'request') && $request->has($condition->request)) {
                $request_keyword = $request->get($condition->request);

                // リクエスト項目に検索キーワードが含まれていたら、セッションに保持する。（空でも保持＝クリアの意味）
                $request->session()->put($condition->request, $request_keyword);
            } elseif (property_exists($condition, 'request') && $request->session()->get($condition->request)) {
                // セッションに検索キーワードが含まれていたら使用する。
                $request_keyword = $request->session()->get($condition->request);
            }

            // 検索キーワードのデフォルト(検索キーワードが空だった場合に使用する)
            if (empty($request_keyword) && property_exists($condition, 'request_default')) {
                if ($condition->request_default == 'TODAY-MD') {
                    $request_keyword = date('md');
                } else {
                    $request_keyword = $condition->request_default;
                }
            }

            // 検索方法
            if (!property_exists($condition, 'where') || empty($condition->where)) {
                // where が空なら、条件指定しない
            } elseif ($condition->where == 'ALL') {
                $inputs_query->where('value', $request_keyword);
            } elseif ($condition->where == 'PART') {
                $inputs_query->where('value', 'like', '%' . $request_keyword . '%');
            } elseif ($condition->where == 'FRONT') {
                $inputs_query->where('value', 'like', $request_keyword . '%');
            } elseif ($condition->where == 'REAR') {
                $inputs_query->where('value', 'like', '%' . $request_keyword);
            } elseif ($condition->where == 'GT') {
                $inputs_query->where('value', '>', $request_keyword);
            } elseif ($condition->where == 'LT') {
                $inputs_query->where('value', '<', $request_keyword);
            } elseif ($condition->where == 'GE') {
                $inputs_query->where('value', '>=', $request_keyword);
            } elseif ($condition->where == 'LE') {
                $inputs_query->where('value', '<=', $request_keyword);
            }

            // 行ID 取得のためのグルーピング
            $inputs_query->groupBy('databases_inputs_id')
                        ->groupBy('frames.id')
                        ->groupBy('frames.page_id')
                        // bugfix: 入力データ1件の項目内で更新日がずれると重複を起こすため、updated_atのgroupBy不要。
                        //->groupBy('databases_input_cols.updated_at')
                        ;

            // データ取得
            //$inputs_ids_array[] = $inputs_query->get();
            $inputs_ids_array[] = $inputs_query->get()->pluck('databases_inputs_id')->all();
            // Log::debug(var_export($inputs_query->get()->toArray(), true));
        }

        // 条件毎の結果の結合
        if (count($inputs_ids_array) > 1) {
            $inputs_ids_marge = call_user_func_array("array_intersect", $inputs_ids_array);
        } else {
            $inputs_ids_marge = $inputs_ids_array[0];
        }

        // 条件全てに合致した行ID を元に、再度データ取得（ここでページネート指定する）
        $inputs_ids
            = DatabasesInputCols::select('databases_inputs_id', 'frames.id as frames_id', 'frames.page_id', 'databases.databases_name')
                                ->join('databases_columns', 'databases_columns.id', '=', 'databases_input_cols.databases_columns_id')
                                ->join('databases', 'databases.id', '=', 'databases_columns.databases_id')
                                ->join('frames', 'frames.bucket_id', '=', 'databases.bucket_id')
                                ->join('databases_inputs', 'databases_inputs.id', '=', 'databases_input_cols.databases_inputs_id')
                                ->whereIn('databases_inputs_id', $inputs_ids_marge)
                                ->groupBy('databases_inputs_id')
                                ->groupBy('frames.id')
                                ->groupBy('frames.page_id')
                                ->groupBy('databases.databases_name');
                                // bugfix: 入力データ1件の項目内で更新日がずれると重複を起こすため、updated_atのgroupBy不要。
                                // ->groupBy('databases_input_cols.updated_at');
                                // move: 少し下に移動
                                // ->orderBy('databases_input_cols.updated_at', 'desc')
                                // ->paginate($databasesearches->view_count, ["*"], "frame_{$frame_id}_page");

        // bugfix: フレーム指定の場合、同じデータベースを複数フレームで指定した場合の対応漏れ
        // フレーム（データベース指定）
        if ($databasesearches->frame_select == 1 && $databasesearches->target_frame_ids) {
            $inputs_ids->whereIn('frames.id', explode(',', $databasesearches->target_frame_ids));
        }

        // 並び替え条件指定
        switch( $databasesearches->sort_flag ) {
            case DatabaseSearcherSortFlag::created_asc:
                $inputs_ids->orderBy( 'databases_inputs.created_at', 'asc' );
                break;
            case DatabaseSearcherSortFlag::created_desc:
                $inputs_ids->orderBy( 'databases_inputs.created_at', 'desc' );
                break;
            case DatabaseSearcherSortFlag::updated_asc:
                $inputs_ids->orderBy( 'databases_inputs.updated_at', 'asc' );
                break;
            case DatabaseSearcherSortFlag::updated_desc:
                $inputs_ids->orderBy( 'databases_inputs.updated_at', 'desc' );
                break;
            case DatabaseSearcherSortFlag::posted_asc:
                $inputs_ids->orderBy( 'databases_inputs.posted_at', 'asc' );
                break;
            case DatabaseSearcherSortFlag::posted_desc:
                $inputs_ids->orderBy( 'databases_inputs.posted_at', 'desc' );
                break;
            case DatabaseSearcherSortFlag::display_asc:
                $inputs_ids->orderBy( 'databases_inputs.display_sequence', 'asc' );
                break;
            case DatabaseSearcherSortFlag::display_desc:
                $inputs_ids->orderBy( 'databases_inputs.display_sequence', 'desc' );
                break;
        }

        $inputs_ids = $inputs_ids->paginate($databasesearches->view_count, ["*"], "frame_{$frame_id}_page");
        // Log::debug(var_export($inputs_ids->toArray(), true));
        // Log::debug(var_export($inputs_ids_marge, true));

        // 登録データ詳細の取得
        $input_cols = DatabasesInputCols::select('databases_input_cols.*', 'databases_columns.column_name', 'uploads.client_original_name', 'databases_columns.column_type')
                                        ->join('databases_columns', 'databases_columns.id', '=', 'databases_input_cols.databases_columns_id')
                                        ->leftJoin('uploads', 'uploads.id', '=', 'databases_input_cols.value')
                                        ->whereIn('databases_inputs_id', $inputs_ids->pluck('databases_inputs_id'))
                                        // 画面は input_cols をモトに表示している。ここで hide_columns_ids でNotInしてるため、権限によって非表示columは表示されない
                                        ->whereNotIn('databases_columns_id', $hide_columns_ids)
                                        ->orderBy('databases_inputs_id', 'asc')
                                        ->orderBy('databases_columns_id', 'asc')
                                        ->get();
        // Log::debug(var_export($input_cols->toArray(), true));
        // var_dump($hide_columns_ids);

        // 画面へ
        return $this->view('databasesearches', [
            'page_id'          => $page_id,
            'databasesearches' => $databasesearches,
            'inputs_ids'       => $inputs_ids,
            'input_cols'       => $input_cols,
        ]);
    }

    /**
     * 設定変更画面の表示
     */
    public function editBuckets($request, $page_id, $frame_id)
    {
        // セッション初期化などのLaravel 処理。
        $request->flash();

        // フレームデータ
        $frames = Frame::find($frame_id);

        // データベース検索設定データ
        if ($frames->bucket_id) {
            $databasesearches = Databasesearches::where('bucket_id', $frames->bucket_id)->first();
        } else {
            $databasesearches = new Databasesearches();
        }

        // 選択可能なFrame データ
        $target_frames = Frame::select('frames.*', 'pages._lft', 'pages.page_name', 'buckets.bucket_name')
                       ->whereIn('frames.plugin_name', array('databases'))
                       ->leftJoin('buckets', 'frames.bucket_id', '=', 'buckets.id')
                       ->leftJoin('pages', 'frames.page_id', '=', 'pages.id')
                       ->where('disable_searchs', 0)
                       ->orderBy('pages._lft', 'asc')
                       ->get();

        // 表示テンプレートを呼び出す。
        return $this->view(
            'databasesearches_edit_buckets', [
            'frames'                     => $frames,
            'databasesearches'           => $databasesearches,
            'target_frames'              => $target_frames,
            ]
        )->withInput($request->all);
    }

    /**
     *  データベース検索登録処理
     */
    public function saveBuckets($request, $page_id, $frame_id, $id = null)
    {
        // デフォルトでチェック
        $validator_values['databasesearches_name'] = ['required'];
        $validator_values['view_count']            = ['required', 'numeric'];
        $validator_values['view_columns']          = ['required'];

        $validator_attributes['databasesearches_name'] = 'データベース検索名';
        $validator_attributes['view_count']            = '表示件数';
        $validator_attributes['view_columns']          = '表示カラム';

        // 項目のエラーチェック
        $validator = Validator::make($request->all(), $validator_values);
        $validator->setAttributeNames($validator_attributes);

        // エラーがあった場合は入力画面に戻る。
        if ($validator->fails()) {
            return $this->editBuckets($request, $page_id, $frame_id, $id)->withErrors($validator);
        }

        // バケツデータ更新
        $buckets = Buckets::updateOrCreate(
            ['id' => $request->buckets_id],
            [
             'bucket_name' => $request->databasesearches_name,
             'plugin_name' => 'databasesearches',
            ]
        );

        // データベース検索の更新
        Databasesearches::updateOrCreate(
            ['id' => $request->databasesearches_id],
            [
             'bucket_id'             => $buckets->id,
             'databasesearches_name' => $request->databasesearches_name,
             'view_count'            => intval($request->view_count),
             'view_columns'          => $request->view_columns,
             'condition'             => $request->condition,
             'sort_flag'             => $request->sort_flag,
             'frame_select'          => intval($request->frame_select),
             'target_frame_ids'      => empty($request->target_frame_ids) ? "": implode(',', $request->target_frame_ids),
            ]
        );

        // フレームの更新
        Frame::updateOrCreate(
            ['id' => $frame_id],
            [
             'bucket_id'             => $buckets->id,
            ]
        );

        return $this->editBuckets($request, $page_id, $frame_id, $id);
    }

    /**
     * データ紐づけ変更関数
     *
     * changeBuckets と同等. resources\views\plugins\common\edit_datalist.blade.php からPOSTされる。
     * ※ listBuckets の定義がデータベース検索プラグインにないため UserPluginBase のメソッドを使っていた。
     */
    public function change($request, $page_id = null, $frame_id = null, $id = null)
    {
        // FrameのバケツIDの更新
        Frame::where('id', $frame_id)
               ->update(['bucket_id' => $request->select_bucket]);

        return $this->listBuckets($request, $page_id, $frame_id);
    }
}
