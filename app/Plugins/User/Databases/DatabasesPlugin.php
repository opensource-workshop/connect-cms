<?php

namespace App\Plugins\User\Databases;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;

use DB;
use Carbon\Carbon;

use App\Models\Common\Buckets;
use App\Models\Common\Frame;
//use App\Models\Common\Page;
use App\Models\Common\Uploads;
use App\Models\User\Databases\Databases;
use App\Models\User\Databases\DatabasesColumns;
use App\Models\User\Databases\DatabasesColumnsSelects;
use App\Models\User\Databases\DatabasesColumnsRole;
use App\Models\User\Databases\DatabasesFrames;
use App\Models\User\Databases\DatabasesInputs;
use App\Models\User\Databases\DatabasesInputCols;

use App\Rules\CustomVali_AlphaNumForMultiByte;
use App\Rules\CustomVali_CheckWidthForString;
use App\Rules\CustomVali_DatesYm;

use App\Mail\ConnectMail;
use App\Plugins\User\UserPluginBase;

use App\Utilities\csv\SjisToUtf8EncodingFilter;

/**
 * データベース・プラグイン
 *
 * データベースの作成＆データ収集用プラグイン。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @author 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category データベース・プラグイン
 * @package Contoroller
 */
class DatabasesPlugin extends UserPluginBase
{
    /* オブジェクト変数 */

    /* コアから呼び出す関数 */

    /**
     * 追加の関数定義（コアから呼び出す）
     */
    public function getPublicFunctions()
    {
        // 標準関数以外で画面などから呼ばれる関数の定義
        $functions = array();
        $functions['get']  = [
            'editColumnDetail',
            'detail',
            'input',
            'editView',
            'search',
        ];
        $functions['post'] = [
            'index',
            'detail',
            'input',
            'cancel',
            'updateColumn',
            'updateColumnSequence',
            'updateColumnDetail',
            'addSelect',
            'addPref',
            'updateSelect',
            'updateSelectSequence',
            'deleteSelect',
            'saveView',
            'search',
        ];
        return $functions;
    }

    /**
     * 追加の権限定義（コアから呼び出す）
     */
    public function declareRole()
    {
        // 標準権限以外で設定画面などから呼ばれる権限の定義
        // 標準権限は右記で定義 config/cc_role.php
        //
        // 権限チェックテーブル
        $role_ckeck_table = array();
        $role_ckeck_table["input"]                = array('posts.create', 'posts.update');
        $role_ckeck_table["publicConfirm"]        = array('posts.create', 'posts.update');
        $role_ckeck_table["publicStore"]          = array('posts.create', 'posts.update');

        $role_ckeck_table["editColumnDetail"]     = array('buckets.editColumn');
        $role_ckeck_table["updateColumn"]         = array('buckets.editColumn');
        $role_ckeck_table["updateColumnSequence"] = array('buckets.editColumn');
        $role_ckeck_table["updateColumnDetail"]   = array('buckets.editColumn');
        $role_ckeck_table["addSelect"]            = array('buckets.addColumn');
        $role_ckeck_table["addPref"]              = array('buckets.addColumn');
        $role_ckeck_table["updateSelect"]         = array('buckets.editColumn');
        $role_ckeck_table["updateSelectSequence"] = array('buckets.editColumn');
        $role_ckeck_table["deleteSelect"]         = array('buckets.editColumn');
        $role_ckeck_table["deleteColumnsSelects"] = array('buckets.editColumn');
        $role_ckeck_table["editView"]             = array('frames.edit');
        $role_ckeck_table["saveView"]             = array('frames.edit');
        return $role_ckeck_table;
    }

    /**
     *  編集画面の最初のタブ
     *
     *  スーパークラスをオーバーライド
     */
    public function getFirstFrameEditAction()
    {
        // データベースの設定がまだの場合は、データベースの新規作成に遷移する。
        $database = $this->getDatabases($this->frame->id);
        if (empty($database)) {
            return "createBuckets";
        }

        // カラムの設定画面
        return "editColumn";
    }

    /* private関数 */

    /**
     *  データ取得
     */
    private function getDatabases($frame_id)
    {
        // Databases、Frame データ
        $database = DB::table('databases')
            ->select('databases.*')
            ->join('frames', 'frames.bucket_id', '=', 'databases.bucket_id')
            ->where('frames.id', '=', $frame_id)
            ->first();

        return $database;
    }

    /**
     *  カラムデータ取得
     *  ※データベース設定で「登録者にメール送信あり」設定にも関わらず、項目内にメールアドレス型が存在しない場合はリテラル「mail_setting_error」を返す
     */
    private function getDatabasesColumns($database)
    {
        // データベースのカラムデータ
        $databases_columns = [];
        if (!empty($database)) {
            $databases_columns = DatabasesColumns::where('databases_id', $database->id)->orderBy('display_sequence')->get();
            // 2020-08-19: 下記はフォームプラグインの名残。現状データベースではuser_mail_send_flagを使っていないが、DBにカラム存在するため、とりあえずそのままにする
            if ($database->user_mail_send_flag == '1' && empty($databases_columns->where('column_type', \DatabaseColumnType::mail)->first())) {
                return 'mail_setting_error';
            }
        }

        // カラムデータがない場合
        if (empty($databases_columns)) {
            return null;
        }

        return $databases_columns;
    }

    /**
     *  カラムの選択肢用データ取得
     */
    private function getDatabasesColumnsSelects($databases_id)
    {
        // カラムの選択肢用データ
        $databases_columns_selects = DB::table('databases_columns_selects')
                                     ->join('databases_columns', 'databases_columns.id', '=', 'databases_columns_selects.databases_columns_id')
                                     ->join('databases', 'databases.id', '=', 'databases_columns.databases_id')
                                     ->select('databases_columns_selects.*')
                                     ->where('databases.id', '=', $databases_id)
                                     ->orderBy('databases_columns_selects.databases_columns_id', 'asc')
                                     ->orderBy('databases_columns_selects.display_sequence', 'asc')
                                     ->get();
        // カラムID毎に詰めなおし
        $databases_columns_id_select = array();
        $index = 1;
        $before_databases_columns_id = null;
        foreach ($databases_columns_selects as $databases_columns_select) {
            if ($before_databases_columns_id != $databases_columns_select->databases_columns_id) {
                $index = 1;
                $before_databases_columns_id = $databases_columns_select->databases_columns_id;
            }

            $databases_columns_id_select[$databases_columns_select->databases_columns_id][$index]['value'] = $databases_columns_select->value;
            $index++;
        }

        return $databases_columns_id_select;
    }

    /**
     *  紐づくデータベースID とフレームデータの取得
     */
    private function getDatabaseFrame($frame_id)
    {
        // Frame データ
        $frame = DB::table('frames')
                 ->select('frames.*', 'databases.id as databases_id', 'databases_frames.id as databases_frames_id', 'use_search_flag', 'use_select_flag', 'use_sort_flag', 'view_count', 'default_hide', 'view_page_id', 'view_frame_id')
                 ->leftJoin('databases', 'databases.bucket_id', '=', 'frames.bucket_id')
                 ->leftJoin('databases_frames', 'databases_frames.frames_id', '=', 'frames.id')
                 ->where('frames.id', $frame_id)
                 ->first();
        return $frame;
    }

    /**
     *  データ詳細の取得
     */
    private function getDatabasesInputCols($id)
    {
        // データ詳細の取得
        $input_cols = DatabasesInputCols::select('databases_input_cols.*', 'databases_columns.column_type', 'databases_columns.column_name', 'databases_columns.classname', 'uploads.client_original_name')
                                        ->leftJoin('databases_columns', 'databases_columns.id', '=', 'databases_input_cols.databases_columns_id')
                                        ->leftJoin('uploads', 'uploads.id', '=', 'databases_input_cols.value')
                                        ->where('databases_inputs_id', $id)
                                        ->orderBy('databases_inputs_id', 'asc')
                                        ->orderBy('databases_columns_id', 'asc')
                                        ->get();
        return $input_cols;
    }

    /**
     *  ファイル系の詳細データの取得
     */
    private function getUploadsInputCols($inputs_id)
    {
        $records = DatabasesInputCols::select('uploads.*', 'databases_columns.column_type', 'databases_input_cols.databases_columns_id as columns_id', 'databases_input_cols.value')
                                     ->join('databases_columns', 'databases_columns.id', '=', 'databases_input_cols.databases_columns_id')
                                     ->leftJoin('uploads', 'uploads.id', '=', 'databases_input_cols.value')
                                     ->where('databases_inputs_id', $inputs_id)
                                     ->whereIn('databases_columns.column_type', [\DatabaseColumnType::file, \DatabaseColumnType::image, \DatabaseColumnType::video])
                                     ->orderBy('databases_inputs_id', 'asc')
                                     ->orderBy('databases_columns_id', 'asc')
                                     ->get();

        // 後でこのCollection から要素を削除する可能性がある。
        // そのため、カラムを特定できるように、カラムをキーにして詰め替える。
        $uploads = collect();
        foreach ($records as $record) {
            $uploads->put($record->columns_id, $record);
        }

        return $uploads;
    }

    /* 画面アクション関数 */

    /**
     *  データ初期表示関数
     *  コアがページ表示の際に呼び出す関数
     */
    public function index($request, $page_id, $frame_id, $errors = null)
    {
        // セッション初期化などのLaravel 処理。
        $request->flash();

        // リクエストにページが渡ってきたら、セッションに保持しておく。（詳細や更新後に元のページに戻るため）
        // if ($request->has('page')) {
        //     $request->session()->put('page_no.'.$frame_id, $request->page);
        $frame_page = "frame_{$frame_id}_page";
        if ($request->has($frame_page)) {
                $request->session()->put('page_no.'.$frame_id, $request->$frame_page);
        } else {
            // 指定がなければセッションから削除
            $request->session()->forget('page_no.'.$frame_id);
        }

        // Databases、Frame データ
        $database = $this->getDatabases($frame_id);


        $setting_error_messages = null;
        $databases_columns = null;
        $databases_columns_id_select = null;
        if ($database) {
            $databases_columns_id_select = $this->getDatabasesColumnsSelects($database->id);

            /**
             * データベースのカラムデータを取得
             * ※データベース設定で「登録者にメール送信あり」設定にも関わらず、項目内にメールアドレス型が存在しない場合はリテラル「mail_setting_error」が返る
             */
            $databases_columns = $this->getDatabasesColumns($database);

            if ($databases_columns == 'mail_setting_error') {
                // データベース設定で「登録者にメール送信あり」設定にも関わらず、項目内にメールアドレス型が存在しない場合
                $setting_error_messages[] = 'メールアドレス型の項目を設定してください。（データベースの設定「登録者にメール送信する」と関連）';
            } elseif (!$databases_columns) {
                // 項目データがない場合
                $setting_error_messages[] = 'フレームの設定画面から、項目データを作成してください。';
            }
        } else {
            // フレームに紐づくデータベース親データがない場合
            $setting_error_messages[] = 'フレームの設定画面から、使用するデータベースを選択するか、作成してください。';
        }


        //--- 初期表示データ

        if (empty($database)) {
            $databases = null;
            $columns = null;
            $select_columns = null;
            $sort_columns = null;
            $sort_count = 0;
            $group_rows_cols_columns = null;
            $inputs = null;
            $input_cols = null;
        } else {
            // データベースの取得
            $databases = Databases::where('id', $database->id)->first();

            // フレーム毎のデータベース設定の取得
            $databases_frames = DatabasesFrames::where('frames_id', $frame_id)->where('databases_id', $database->id)->first();

            // カラムの取得
            $columns = DatabasesColumns::where('databases_id', $database->id)->orderBy('display_sequence', 'asc')->get();

            // 行グループ・列グループの配列に置き換えたcolumns
            $group_rows_cols_columns = $this->replaceArrayGroupRowsColsColumns($columns, 'list_hide_flag');

            // 登録データ行の取得 --->

            // ソート(セッションがあれば優先。なければ初期値を使用)
            $sort_column_id = '';
            $sort_column_order = '';
            if (session('sort_column_id.'.$frame_id) && session('sort_column_order.'.$frame_id)) {
                $sort_column_id = session('sort_column_id.'.$frame_id);
                $sort_column_order = session('sort_column_order.'.$frame_id);
            } elseif ($databases_frames && $databases_frames->default_sort_flag) {
                $sort_flag = explode('_', $databases_frames->default_sort_flag);
                if (count($sort_flag) == 2) {
                    $sort_column_id = $sort_flag[0];
                    $sort_column_order = $sort_flag[1];
                }
            }

            // ソートなし or ソートするカラムIDが数値じゃない（=入力なしと同じ扱いにする）
            if (empty($sort_column_id) || !ctype_digit($sort_column_id)) {
                $inputs_query = DatabasesInputs::where('databases_id', $database->id);
            } else {
                // ソートあり
                $inputs_query = DatabasesInputs::select('databases_inputs.*', 'databases_input_cols.value')
                                                ->leftjoin('databases_input_cols', function ($join) use ($sort_column_id) {
                                                    $join->on('databases_input_cols.databases_inputs_id', '=', 'databases_inputs.id')
                                                         ->where('databases_input_cols.databases_columns_id', '=', $sort_column_id);
                                                })
                                               ->where('databases_id', $database->id);
            }

            // 権限によって表示する記事を絞る
            $inputs_query = $this->appendAuthWhere($inputs_query, 'databases_inputs');

            // 権限のよって非表示columのdatabases_columns_id配列を取得する
            $hide_columns_ids = $this->getHideColumnsIds($columns, 'list_detail_display_flag');

            // キーワード指定の追加
            if (!empty(session('search_keyword.'.$frame_id))) {
                $inputs_query->whereIn('databases_inputs.id', function ($query) use ($frame_id, $hide_columns_ids) {
                               // 縦持ちのvalue を検索して、行の id を取得。search_flag で対象のカラムを絞る。
                               $query->select('databases_inputs_id')
                                     ->from('databases_input_cols')
                                     ->join('databases_columns', 'databases_columns.id', '=', 'databases_input_cols.databases_columns_id')
                                     ->where('databases_columns.search_flag', 1)
                                     ->whereNotIn('databases_columns.id', $hide_columns_ids)
                                     ->where('value', 'like', '%' . session('search_keyword.'.$frame_id) . '%')
                                     ->groupBy('databases_inputs_id');
                });
            }

            // オプション検索指定の追加
            if ($request->has('search_options') && is_array($request->search_options)) {
                // 指定をばらす
                foreach ($request->search_options as $search_option) {
                    $search_option_parts = explode('|', $search_option);
                    if (count($search_option_parts) != 3) {
                        continue;  // 指定が正しくなければ飛ばす
                    }
                    $option_search_column_obj = $columns->where('column_name', $search_option_parts[0]);
                    if (empty($option_search_column_obj)) {
                        continue;  // 指定が正しくなければ飛ばす
                    }
                    $option_search_column = $option_search_column_obj->first();
                    if (empty($option_search_column)) {
                        continue;  // 指定が正しくなければ飛ばす
                    }
                    if (empty($search_option_parts[1]) || !in_array($search_option_parts[1], ['ALL', 'PART', 'FRONT', 'REAR', 'GT', 'LT', 'GE', 'LE'])) {
                        continue;  // 指定が正しくなければ飛ばす
                    }
                    if (empty($search_option_parts[2])) {
                        continue;  // 指定が正しくなければ飛ばす
                    }

                    // 検索方法
                    $inputs_query->whereIn('databases_inputs.id', function ($query) use ($option_search_column, $search_option_parts) {
                                   // 縦持ちのvalue を検索して、行の id を取得。search_flag で対象のカラムを絞る。
                                   $query->select('databases_inputs_id')
                                         ->from('databases_input_cols')
                                         ->join('databases_columns', 'databases_columns.id', '=', 'databases_input_cols.databases_columns_id')
                                         ->where('databases_input_cols.databases_columns_id', $option_search_column->id);
                        if ($search_option_parts[1] == 'ALL') {
                            $query->where('value', $search_option_parts[2]);
                        } elseif ($search_option_parts[1] == 'PART') {
                            $query->where('value', 'like', '%' . $search_option_parts[2] . '%');
                        } elseif ($search_option_parts[1] == 'FRONT') {
                            $query->where('value', 'like', $search_option_parts[2] . '%');
                        } elseif ($search_option_parts[1] == 'REAR') {
                            $query->where('value', 'like', '%' . $search_option_parts[2]);
                        } elseif ($search_option_parts[1] == 'GT') {
                            $query->where('value', '>', $search_option_parts[2]);
                        } elseif ($search_option_parts[1] == 'LT') {
                            $query->where('value', '<', $search_option_parts[2]);
                        } elseif ($search_option_parts[1] == 'GE') {
                            $query->where('value', '>=', $search_option_parts[2]);
                        } elseif ($search_option_parts[1] == 'LE') {
                            $query->where('value', '<=', $search_option_parts[2]);
                        }
                        $query->groupBy('databases_inputs_id');
                    });
                }
            }

            // 絞り込み指定の追加
            if (!empty(session('search_column.'.$frame_id))) {
                foreach (session('search_column.'.$frame_id) as $search_column) {
                    if ($search_column && $search_column['columns_id'] && $search_column['value']) {
                        $inputs_query->whereIn('databases_inputs.id', function ($query) use ($search_column) {
                               // 縦持ちのvalue を検索して、行の id を取得。column_id で対象のカラムを絞る。
                               $query->select('databases_inputs_id')
                                     ->from('databases_input_cols')
                                     ->join('databases_columns', 'databases_columns.id', '=', 'databases_input_cols.databases_columns_id')
                                     ->where('databases_columns_id', $search_column['columns_id']);

                            if ($search_column['where'] == 'PART') {
                                $query->where('value', 'LIKE', '%' . $search_column['value'] . '%');
                            } else {
                                $query->where('value', $search_column['value']);
                            }
                            $query->groupBy('databases_inputs_id');
                        });
                    }
                }
            }

            // 並べ替え指定があれば、並べ替えする項目をSELECT する。
            // if ($sort_column_id == 'random' && $sort_column_order == 'session') {
            //     $inputs_query->inRandomOrder(session('sort_seed.'.$frame_id));
            // } elseif ($sort_column_id == 'random' && $sort_column_order == 'every') {
            //     $inputs_query->inRandomOrder();
            // } elseif ($sort_column_id == 'created' && $sort_column_order == 'asc') {
            //     $inputs_query->orderBy('databases_inputs.created_at', 'asc');
            // } elseif ($sort_column_id == 'created' && $sort_column_order == 'desc') {
            //     $inputs_query->orderBy('databases_inputs.created_at', 'desc');
            // } elseif ($sort_column_id == 'updated' && $sort_column_order == 'asc') {
            //     $inputs_query->orderBy('databases_inputs.updated_at', 'asc');
            // } elseif ($sort_column_id == 'updated' && $sort_column_order == 'desc') {
            //     $inputs_query->orderBy('databases_inputs.updated_at', 'desc');
            if ($sort_column_id == \DatabaseSortFlag::random && $sort_column_order == \DatabaseSortFlag::order_session) {
                $inputs_query->inRandomOrder(session('sort_seed.'.$frame_id));
            } elseif ($sort_column_id == \DatabaseSortFlag::random && $sort_column_order == \DatabaseSortFlag::order_every) {
                $inputs_query->inRandomOrder();
            } elseif ($sort_column_id == \DatabaseSortFlag::created && $sort_column_order == \DatabaseSortFlag::order_asc) {
                $inputs_query->orderBy('databases_inputs.created_at', 'asc');
            } elseif ($sort_column_id == \DatabaseSortFlag::created && $sort_column_order == \DatabaseSortFlag::order_desc) {
                $inputs_query->orderBy('databases_inputs.created_at', 'desc');
            } elseif ($sort_column_id == \DatabaseSortFlag::updated && $sort_column_order == \DatabaseSortFlag::order_asc) {
                $inputs_query->orderBy('databases_inputs.updated_at', 'asc');
            } elseif ($sort_column_id == \DatabaseSortFlag::updated && $sort_column_order == \DatabaseSortFlag::order_desc) {
                $inputs_query->orderBy('databases_inputs.updated_at', 'desc');
            } elseif ($sort_column_id == \DatabaseSortFlag::posted && $sort_column_order == \DatabaseSortFlag::order_asc) {
                $inputs_query->orderBy('databases_inputs.posted_at', 'asc');
            } elseif ($sort_column_id == \DatabaseSortFlag::posted && $sort_column_order == \DatabaseSortFlag::order_desc) {
                $inputs_query->orderBy('databases_inputs.posted_at', 'desc');
            } elseif ($sort_column_id && ctype_digit($sort_column_id) && $sort_column_order == \DatabaseSortFlag::order_asc) {
                $inputs_query->orderBy('databases_input_cols.value', 'asc');
            } elseif ($sort_column_id && ctype_digit($sort_column_id) && $sort_column_order == \DatabaseSortFlag::order_desc) {
                $inputs_query->orderBy('databases_input_cols.value', 'desc');
            }
            $inputs_query->orderBy('databases_inputs.id', 'asc');

            // データ取得
            $get_count = 10;
            if ($databases_frames) {
                $get_count = $databases_frames->view_count;
            }
            $inputs = $inputs_query->paginate($get_count, ["*"], "frame_{$frame_id}_page");
            // <--- 登録データ行の取得

            // 登録データ詳細の取得
            $input_cols = DatabasesInputCols::select('databases_input_cols.*', 'uploads.client_original_name')
                                            ->leftJoin('uploads', 'uploads.id', '=', 'databases_input_cols.value')
                                            ->whereIn('databases_inputs_id', $inputs->pluck('id'))
                                            ->orderBy('databases_inputs_id', 'asc')->orderBy('databases_columns_id', 'asc')
                                            ->get();

            // カラム選択肢の取得
            $columns_selects = DatabasesColumnsSelects::whereIn('databases_columns_id', $columns->pluck('id'))->orderBy('display_sequence', 'asc')->get();

            // 絞り込み対象カラム
            $select_columns = $columns->where('select_flag', 1)
                                        ->whereNotIn('id', $hide_columns_ids);

            // 並び順対象カラム
            if ($databases_frames && $databases_frames->isUseSortFlag()) {
                // {{-- 1:昇順＆降順、2:昇順のみ、3:降順のみ --}}
                $sort_columns = $columns->whereIn('sort_flag', [1, 2, 3])
                                        ->whereNotIn('id', $hide_columns_ids);
                $sort_count = $sort_columns->count();
            } else {
                $sort_columns = null;
                $sort_count = 0;
            }
        }

        //--- 表示設定（フレーム設定）データ

        // データベース＆フレームデータ
        $database_frame = $this->getDatabaseFrame($frame_id);

        // 初期表示を隠す判定
        $default_hide_list = false;
        if (($database_frame && $database_frame->default_hide == 1 && $request->isMethod('get') && !$request->page)) {
            $default_hide_list = true;
        }

        // 表示テンプレートを呼び出す。
        return $this->view(
            'databases',
            [
                'request'  => $request,
                'frame_id' => $frame_id,
                'database' => $database,
                'databases_columns' => $databases_columns,
                'databases_columns_id_select' => $databases_columns_id_select,
                'errors' => $errors,
                'setting_error_messages' => $setting_error_messages,

                'databases'        => $databases,
                'database_frame'   => $database_frame,
                'databases_frames' => empty($databases_frames) ? new DatabasesFrames() : $databases_frames,
                'columns'          => $columns,
                'group_rows_cols_columns' => $group_rows_cols_columns,
                'select_columns'   => $select_columns,
                'sort_columns'     => $sort_columns,
                'sort_count'       => $sort_count,
                'inputs'           => $inputs,
                'input_cols'       => $input_cols,
                'columns_selects'  => isset($columns_selects) ? $columns_selects : null,
                'default_hide_list' => $default_hide_list,
            ]
        )->withInput($request->all);
    }

    /**
     * 行グループ・列グループの配列に置き換えたcolumns
     */
    private function replaceArrayGroupRowsColsColumns($databases_columns, $hide_flag_column_name = 'list_hide_flag')
    {
        if (empty($databases_columns)) {
            return [];
        }

        // 行グループ・列グループの配列に置き換えたcolumns
        $group_rows_cols_columns = [];
        $group_rows_cols_null_columns = [];

        // 権限のよって非表示columのdatabases_columns_id配列を取得する
        $hide_columns_ids = $this->getHideColumnsIds($databases_columns, 'list_detail_display_flag');

        // 表示しないcolumnは、group_rows_cols_columnsに含まない。
        //
        // 一覧に表示する (list_hide_flag=0)
        // 詳細に表示する (detail_hide_flag=0)
        $disp_databases_columns = $databases_columns->where($hide_flag_column_name, 0)
                                                    ->whereNotIn('id', $hide_columns_ids);

        foreach ($disp_databases_columns as $databases_column) {
            if (is_null($databases_column->row_group) && is_null($databases_column->column_group)) {
                // 行グループ・列グループどっちも設定なし
                //
                // row_group = null & column_group = nullは1行として扱うため、
                // $group_rows_cols_columns[row_group = 連番][column_group = ''で固定][columns_key = 0 で固定] とする
                // ※ arrayの配列keyにnullをセットすると、keyは''になるため、''をkeyに使用してます。
                $group_cols_columns = null;                         // 初期化
                $group_cols_columns[''][0] = $databases_column;     // column_group = ''としてセット

                // bugfix: row_groupを連番[]でセットすると、0,1,2と続くため、row_group =1とかあると、row_group =nullがそのグループに含まれてしまうので
                // 行グループ・列グループどっちも設定なしグループは、お尻にくっつけるよう見直し
                // $group_rows_cols_columns[] = $group_cols_columns;   // row_groupは連番にするため、[]を使用
                $group_rows_cols_null_columns[] = $group_cols_columns;   // row_groupは連番にするため、[]を使用
            } else {
                // 行グループ・列グループどっちか設定あり
                $group_rows_cols_columns[$databases_column->row_group][$databases_column->column_group][] = $databases_column;
            }
        }
        // Log::debug(var_export($group_rows_cols_columns, true));

        // 行を数値でソート
        ksort($group_rows_cols_columns, SORT_NUMERIC);
        foreach ($group_rows_cols_columns as &$group_row_column) {
            // 列を数値でソート
            ksort($group_row_column, SORT_NUMERIC);
        }

        // 行グループ・列グループどっちも設定なし配列を、設定あり配列のお尻に追加し直す
        foreach ($group_rows_cols_null_columns as $group_rows_cols_null_column) {
            $group_rows_cols_columns[] = $group_rows_cols_null_column;
        }

        return $group_rows_cols_columns;
    }

    /**
     * 権限のよって登録・編集の非表示columnsを取り除く
     */
    private function removeRegistEditHideColumns($databases_columns)
    {
        if (empty($databases_columns)) {
            // 登録・編集用のメソッドのため、基本ここに入ってくることはない
            return [];
        }

        // 権限のよって非表示columのdatabases_columns_id配列を取得する
        $hide_columns_ids = $this->getHideColumnsIds($databases_columns, 'regist_edit_display_flag');

        // 表示しないcolumnは、group_rows_cols_columnsに含まない。
        $disp_databases_columns = $databases_columns->whereNotIn('id', $hide_columns_ids);

        return $disp_databases_columns;
    }

    /**
     * 権限のよって非表示columのdatabases_columns_id配列を取得する
     * $display_flag_column_name = regist_edit_display_flag|list_detail_display_flag
     */
    private function getHideColumnsIds($databases_columns, $display_flag_column_name = 'list_detail_display_flag')
    {
        if (empty($databases_columns)) {
            return [];
        }

        // Log::debug('[' . __METHOD__ . '] ' . __FILE__ . ' (line ' . __LINE__ . ')');
        // Log::debug('role_article_admin: '.var_export($this->isCan('role_article_admin'), true));
        // Log::debug('role_arrangement: '.var_export($this->isCan('role_arrangement'), true));
        // Log::debug('role_article: '.var_export($this->isCan('role_article'), true));
        // Log::debug('role_approval: '.var_export($this->isCan('role_approval'), true));
        // Log::debug('role_reporter: '.var_export($this->isCan('role_reporter'), true));

        $databases_hide_columns_ids = [];

        foreach ($databases_columns as $databases_column) {
            if ($this->isCan('role_article_admin')) {
                // コンテンツ管理者のユーザは、必ず当カラムを表示します。
                continue;
            }

            // 権限で表示カラムを制御
            if (!$databases_column->role_display_control_flag) {
                // 制御しない表示カラムはスルー
                continue;
            }

            // 権限で表示カラムを制御する場合、一度に非表示扱いにする
            // 該当権限で表示フラグをゲットできたら、array keyを指定して非表示扱いから取り除く
            $databases_hide_columns_ids[$databases_column->id] = $databases_column->id;

            // カラムの表示権限データ取得
            $databases_columns_roles = $databases_column->databasesColumnsRoles;

            if (Auth::user()) {
                // ログイン済み
                foreach ($databases_columns_roles as $databases_columns_role) {
                    if ($this->isCan('role_article') &&
                            $databases_columns_role->role_name == \DatabaseColumnRoleName::role_article &&
                            $databases_columns_role->$display_flag_column_name == 1) {
                        // Log::debug('[' . __METHOD__ . '] ' . __FILE__ . ' (line ' . __LINE__ . ')');
                        // Log::debug(var_export('モデレータ', true));

                        // モデレータ権限あり & モデレータ表示のcolumn
                        // 非表示扱いから取り除く(=表示する)
                        unset($databases_hide_columns_ids[$databases_columns_role->databases_columns_id]);
                        continue 2;
                    } elseif ($this->isCan('role_reporter') &&
                            $databases_columns_role->role_name == \DatabaseColumnRoleName::role_reporter &&
                            $databases_columns_role->$display_flag_column_name == 1) {
                        // Log::debug('[' . __METHOD__ . '] ' . __FILE__ . ' (line ' . __LINE__ . ')');
                        // Log::debug(var_export('編集者権限', true));

                        // 編集者権限あり & 編集者表示のcolumn
                        // 非表示扱いから取り除く(=表示する)
                        unset($databases_hide_columns_ids[$databases_columns_role->databases_columns_id]);
                        continue 2;
                    } elseif (!$this->isCan('role_arrangement') &&
                            !$this->isCan('role_article') &&
                            !$this->isCan('role_approval') &&
                            !$this->isCan('role_reporter') &&
                            $databases_columns_role->role_name == \DatabaseColumnRoleName::no_role &&
                            $databases_columns_role->$display_flag_column_name == 1) {
                        // Log::debug('[' . __METHOD__ . '] ' . __FILE__ . ' (line ' . __LINE__ . ')');
                        // Log::debug(var_export('権限なし', true));

                        // 権限なし(プラグイン管理者・モデレータ・承認者・編集者のいずれの権限も付いていない)
                        // & 権限なし表示のcolumn
                        // 非表示扱いから取り除く(=表示する)
                        unset($databases_hide_columns_ids[$databases_columns_role->databases_columns_id]);
                        continue 2;
                    }
                }
            } else {
                // 未ログイン
                foreach ($databases_columns_roles as $databases_columns_role) {
                    // 未ログインで非表示のcolumnは、取り除く
                    if ($databases_columns_role->role_name == \DatabaseColumnRoleName::not_login &&
                            $databases_columns_role->$display_flag_column_name == 1) {
                        // Log::debug('[' . __METHOD__ . '] ' . __FILE__ . ' (line ' . __LINE__ . ')');
                        // Log::debug(var_export('未ログイン', true));

                        // 非表示扱いから取り除く(=表示する)
                        unset($databases_hide_columns_ids[$databases_columns_role->databases_columns_id]);
                        continue 2;
                    }
                }
            }
        }
        return $databases_hide_columns_ids;
    }

    /**
     *  データ検索関数
     *  コアがページ表示の際に呼び出す関数
     */
    public function search($request, $page_id, $frame_id)
    {
        // POST されたときは、新しい絞り込み条件が設定された。ということになるので、セッションの書き換え
        if ($request->isMethod('post')) {
            // キーワード
            session(['search_keyword.'.$frame_id => $request->search_keyword]);

            // 絞り込み
            session(['search_column.'.$frame_id => $request->search_column]);

            // オプション検索
            session(['search_options.'.$frame_id => $request->search_options]);

            // ランダム読み込みのための Seed をセッション中に作っておく
            if (empty(session('sort_seed.'.$frame_id))) {
                session(['sort_seed.'.$frame_id => rand()]);
            }

            // 並べ替え
            $sort_column_parts = explode('_', $request->sort_column);
            if (count($sort_column_parts) == 1) {
                session(['sort_column_id.'.$frame_id    => $sort_column_parts[0]]);
                session(['sort_column_order.'.$frame_id => '']);
            } elseif (count($sort_column_parts) == 2) {
                session(['sort_column_id.'.$frame_id    => $sort_column_parts[0]]);
                session(['sort_column_order.'.$frame_id => $sort_column_parts[1]]);
            } else {
                session(['sort_column_id.'.$frame_id    => '']);
                session(['sort_column_order.'.$frame_id => '']);
            }
        }
        return $this->index($request, $page_id, $frame_id);
    }

    /**
     *  データ詳細表示関数
     */
    public function detail($request, $page_id, $frame_id, $id, $mode = null)
    {
        // Databases、Frame データ
        $database = $this->getDatabases($frame_id);

        // 登録データ行の取得
        // $inputs = DatabasesInputs::where('id', $id)->first();
        $inputs = $this->getDatabasesInputs($id);

        // データがあることを確認
        if (empty($inputs)) {
            return;
        }

        // カラムの取得
        $columns = DatabasesColumns::where('databases_id', $inputs->databases_id)->orderBy('display_sequence', 'asc')->get();

        // データがあることを確認
        if (empty($columns)) {
            return;
        }

        // 行グループ・列グループの配列に置き換えたcolumns
        $group_rows_cols_columns = $this->replaceArrayGroupRowsColsColumns($columns, 'detail_hide_flag');

        // データ詳細の取得
        $input_cols = $this->getDatabasesInputCols($id);

        // 表示する画面
        if ($mode == 'edit') {
            $blade = 'databases_edit';
        } else {
            $blade = 'databases_detail';
        }

        // 表示テンプレートを呼び出す。
        return $this->view(
            $blade,
            [
                'frame_id'   => $frame_id,
                'database'   => $database,
                'columns'    => $columns,
                'group_rows_cols_columns' => $group_rows_cols_columns,
                // inputにすると値があってもnullになるため、$inputsのままでいく
                'inputs'     => $inputs,
                'input_cols' => $input_cols,
            ]
        )->withInput($request->all);
    }

    /**
     *  新規記事画面
     */
    public function input($request, $page_id, $frame_id, $id = null, $errors = null)
    {
        // セッション初期化などのLaravel 処理。
        $request->flash();

        // Databases、Frame データ
        $database = $this->getDatabases($frame_id);

        // データベースのカラムデータ
        $databases_columns = $this->getDatabasesColumns($database);

        // 権限のよって登録・編集の非表示columnsを取り除く
        $databases_columns = $this->removeRegistEditHideColumns($databases_columns);

        // カラムの選択肢用データ
        $databases_columns_id_select = null;
        if ($database) {
            $databases_columns_id_select = $this->getDatabasesColumnsSelects($database->id);
        }

        // データ詳細の取得
        if (empty($id)) {
            // idなし=登録時
            $input_cols = null;
            $inputs = new DatabasesInputs();
            $inputs->posted_at = date('Y-m-d H:i:00');
        } else {
            // idあり=編集時
            // 登録データ行の取得
            $inputs = $this->getDatabasesInputs($id);

            // データがあることを確認
            if (empty($inputs)) {
                return;
            }

            // 権限チェック
            if ($this->can('posts.update', $inputs, $this->frame->plugin_name, $this->buckets)) {
                return $this->view_error(403);
            }

            // データ詳細の取得
            $input_cols = $this->getDatabasesInputCols($id);
        }

        // 表示テンプレートを呼び出す。
        return $this->view(
            'databases_input',
            [
                'request'  => $request,
                'frame_id' => $frame_id,
                'id'       => $id,
                'database' => $database,
                'databases_columns' => $databases_columns,
                'databases_columns_id_select' => $databases_columns_id_select,
                'input_cols'  => $input_cols,
                'inputs'      => $inputs,
                'errors'      => $errors,
            ]
        )->withInput($request->all);
    }

    /**
     * （再帰関数）入力値の前後をトリムする
     *
     * @param $request
     * @return void
     */
    private static function trimInput($value)
    {
        if (is_array($value)) {
            // 渡されたパラメータが配列の場合（radioやcheckbox等）の場合を想定
            $value = array_map(['self', 'trimInput'], $value);
        } elseif (is_string($value)) {
            $value = preg_replace('/(^\s+)|(\s+$)/u', '', $value);
        }

        return $value;
    }

    /**
     * セットすべきバリデータールールが存在する場合、受け取った配列にセットして返す
     *
     * @param [array] $validator_array 二次元配列
     * @param [App\Models\User\Databases\DatabasesColumns] $databases_column
     * @param Request $request
     * @return void
     */
    // private function getValidatorRule($validator_array, $databases_column, $request)
    private function getValidatorRule($validator_array, $databases_column)
    {
        // 登録日型・更新日型・公開日型は入力表示しないため、バリデータチェックしない
        if ($databases_column->column_type == \DatabaseColumnType::created ||
                $databases_column->column_type == \DatabaseColumnType::updated ||
                $databases_column->column_type == \DatabaseColumnType::posted) {
            return $validator_array;
        }

        $validator_rule = null;
        // 必須チェック
        if ($databases_column->required) {
            $validator_rule[] = 'required';
        }
        // メールアドレスチェック
        if ($databases_column->column_type == \DatabaseColumnType::mail) {
            $validator_rule[] = 'email';
            if ($databases_column->required == 0) {
                $validator_rule[] = 'nullable';
            }
        }
        // 数値チェック
        if ($databases_column->rule_allowed_numeric) {
            // move: 入力値変換はバリデーションと同時に行わないため、移動
            // if ($request->databases_columns_value[$databases_column->id]) {
            //     // 入力値があった場合（マイナスを意図した入力記号はすべて半角に置換する）
            //     $replace_defs = [
            //         'ー' => '-',
            //         '－' => '-',
            //         '―' => '-'
            //     ];
            //     $search = array_keys($replace_defs);
            //     $replace = array_values($replace_defs);

            //     if (is_numeric(
            //         mb_convert_kana(
            //             str_replace(
            //                 $search,
            //                 $replace,
            //                 $request->databases_columns_value[$databases_column->id]
            //             ),
            //             'n'
            //         )
            //     )) {
            //         // 全角→半角変換した結果が数値の場合
            //         $tmp_array = $request->databases_columns_value;
            //         // 全角→半角へ丸める
            //         $tmp_array[$databases_column->id] =
            //             mb_convert_kana(
            //                 str_replace(
            //                     $search,
            //                     $replace,
            //                     $request->databases_columns_value[$databases_column->id]
            //                 ),
            //                 'n'
            //             );
            //         $request->merge([
            //             "databases_columns_value" => $tmp_array,
            //         ]);
            //     } else {
            //         // 全角→半角変換した結果が数値ではない場合
            //         $validator_rule[] = 'numeric';
            //     }
            // }
            $validator_rule[] = 'nullable';
            $validator_rule[] = 'numeric';
        }
        // 英数値チェック
        if ($databases_column->rule_allowed_alpha_numeric) {
            $validator_rule[] = 'nullable';
            $validator_rule[] = new CustomVali_AlphaNumForMultiByte();
        }
        // 最大文字数チェック
        if ($databases_column->rule_word_count) {
            $validator_rule[] = new CustomVali_CheckWidthForString($databases_column->column_name, $databases_column->rule_word_count);
        }
        // 指定桁数チェック
        if ($databases_column->rule_digits_or_less) {
            $validator_rule[] = 'digits:' . $databases_column->rule_digits_or_less;
        }
        // 最大値チェック
        if ($databases_column->rule_max) {
            $validator_rule[] = 'nullable';
            $validator_rule[] = 'numeric';
            $validator_rule[] = 'max:' . $databases_column->rule_max;
        }
        // 最小値チェック
        if ($databases_column->rule_min) {
            $validator_rule[] = 'nullable';
            $validator_rule[] = 'numeric';
            $validator_rule[] = 'min:' . $databases_column->rule_min;
        }
        // ～日以降を許容
        if ($databases_column->rule_date_after_equal) {
            $comparison_date = \Carbon::now()->addDay($databases_column->rule_date_after_equal)->databaseat('Y/m/d');
            $validator_rule[] = 'after_or_equal:' . $comparison_date;
        }
        // 日付チェック
        if ($databases_column->column_type == \DatabaseColumnType::date) {
            $validator_rule[] = 'nullable';
            $validator_rule[] = 'date';
        }
        // 複数年月型（テキスト入力）チェック
        if ($databases_column->column_type == \DatabaseColumnType::dates_ym) {
            $validator_rule[] = 'nullable';
            $validator_rule[] = new CustomVali_DatesYm();
        }
        // バリデータールールをセット
        if ($validator_rule) {
            $validator_array['column']['databases_columns_value.' . $databases_column->id] = $validator_rule;
            $validator_array['message']['databases_columns_value.' . $databases_column->id] = $databases_column->column_name;
        }

        return $validator_array;
    }

    /**
     * 登録時の確認
     */
    public function publicConfirm($request, $page_id, $frame_id, $id = null)
    {
        // Databases、Frame データ
        $database = $this->getDatabases($frame_id);

        // データベースのカラムデータ
        $databases_columns = $this->getDatabasesColumns($database);

        // 権限のよって登録・編集の非表示columnsを取り除く
        $databases_columns = $this->removeRegistEditHideColumns($databases_columns);

        // ファイル系の詳細データ
        $uploads = collect();
        if ($id) {
            $uploads = $this->getUploadsInputCols($id);
        }

        // エラーチェック配列
        $validator_array = array('column' => array(), 'message' => array());

        foreach ($databases_columns as $databases_column) {
            // バリデータールールをセット
            // $validator_array = $this->getValidatorRule($validator_array, $databases_column, $request);
            $validator_array = $this->getValidatorRule($validator_array, $databases_column);
        }

        // 固定項目エリア
        $validator_array['column']['posted_at'] = ['required', 'date_format:Y-m-d H:i'];
        $validator_array['message']['posted_at'] = '公開日時';

        // --- 入力値変換
        // 入力値をトリム
        $request->merge(self::trimInput($request->all()));

        foreach ($databases_columns as $databases_column) {
            // 数値チェック
            if ($databases_column->rule_allowed_numeric) {
                // 入力値があった場合（マイナスを意図した入力記号はすべて半角に置換する）＆ 全角→半角へ丸める
                $tmp_numeric_columns_value = $this->convertNumericAndMinusZenkakuToHankaku($request->databases_columns_value[$databases_column->id]);

                $tmp_array = $request->databases_columns_value;
                $tmp_array[$databases_column->id] = $tmp_numeric_columns_value;
                $request->merge([
                    "databases_columns_value" => $tmp_array,
                ]);
            }
        }

        // 項目のエラーチェック
        $validator = Validator::make($request->all(), $validator_array['column']);
        $validator->setAttributeNames($validator_array['message']);

        // エラーがあった場合は入力画面に戻る。
        // $message = null;
        if ($validator->fails()) {
            // var_dump($validator->errors()->first("posted_at"));
            // Log::debug(var_export($request->posted_at, true));

            return $this->input($request, $page_id, $frame_id, $id, $validator->errors());
        }

        // ファイル関連の変数
        if ($request->has('delete_upload_column_ids')) {
            $delete_upload_column_ids = $request->delete_upload_column_ids;  // 画面で削除のチェックがされたupload_id
        } else {
            $delete_upload_column_ids = array();  // 削除や変更で後で削除するファイルのupload_id
        }

        // ファイル項目を探して保存
        foreach ($databases_columns as $databases_column) {
            if (($databases_column->column_type == \DatabaseColumnType::file)  ||
                ($databases_column->column_type == \DatabaseColumnType::image) ||
                ($databases_column->column_type == \DatabaseColumnType::video)) {
                // ファイル系の処理パターン
                // 新規登録   ＞ アップロードされたことを hasFile で検知
                // 変更の削除 ＞ databases_columns_delete_ids に削除するdatabases_input_cols の id を溜める。項目値も一旦クリア。
                // 変更       ＞ アップロードされたことを hasFile で検知。hasFile で無いなら、元の値を使用。
                //               この時、変更の削除も同時に行われている可能性もある。でも、変更の削除が先に処理するのでOK

                // ファイルのリクエスト名
                $req_filename = 'databases_columns_value.' . $databases_column->id;

                // ファイルがアップロードされた。
                if ($request->hasFile($req_filename)) {
                    // ファイルチェック


                    // uploads テーブルに情報追加、ファイルのid を取得する
                    $upload = Uploads::create([
                        'client_original_name' => $request->file($req_filename)->getClientOriginalName(),
                        'mimetype'             => $request->file($req_filename)->getClientMimeType(),
                        'extension'            => $request->file($req_filename)->getClientOriginalExtension(),
                        'size'                 => $request->file($req_filename)->getClientSize(),
                        'plugin_name'          => 'databasess',
                        'page_id'              => $page_id,
                        'temporary_flag'       => 1,
                        'created_id'           => Auth::user()->id,
                    ]);

                    // ファイル保存
                    $directory = $this->getDirectory($upload->id);
                    $upload_path = $request->file($req_filename)->storeAs($directory, $upload->id . '.' . $request->file($req_filename)->getClientOriginalExtension());

                    // 項目とファイルID の関連保持
                    $upload->column_type = $databases_column->column_type;
                    $upload->columns_id = $databases_column->id;

                    // 確定時に削除するファイル（変更前にファイルが添付されていたら）
                    if ($uploads->has($databases_column->id) && isset($uploads->get($databases_column->id)->value)) {
                        $delete_upload_column_ids[$databases_column->id] = $databases_column->id;
                    }

                    // ここで、put でキー指定でセットすることで、紐づくファイル情報が変更される。
                    $uploads->put($databases_column->id, $upload);
                } else {
                    // ファイルがアップロードされていない
                    // アップロードされていなくて、削除指示があった場合は、$uploads から消す。
                    if (array_key_exists($databases_column->id, $delete_upload_column_ids)) {
                        $uploads->forget($databases_column->id);
                    }
                }
            }
        }
        //print_r($delete_upload_column_ids);
        //print_r($uploads);
        // 表示テンプレートを呼び出す。
        return $this->view(
            'databases_confirm', [
            'request'                  => $request,
            'frame_id'                 => $frame_id,
            'id'                       => $id,
            'database'                 => $database,
            'databases_columns'        => $databases_columns,
            'uploads'                  => $uploads,
            'delete_upload_column_ids' => $delete_upload_column_ids,
            ]
        );
    }

    /**
     * 数値項目に使う事を想定。
     * 入力値があった場合（マイナスを意図した入力記号はすべて半角に置換する）＆ 全角→半角へ丸める
     * 郵便場合（111-2222）、電話番号（111-2222-3333）の入力は対応してない。
     */
    private function convertNumericAndMinusZenkakuToHankaku($columns_value)
    {
        if ($columns_value) {
            // 入力値があった場合（マイナスを意図した入力記号はすべて半角に置換する）
            // －１, －１等のマイナス１の入力を丸める。
            $replace_defs = [
                'ー' => '-',
                '－' => '-',
                '―' => '-'
            ];
            $search = array_keys($replace_defs);
            $replace = array_values($replace_defs);

            // 全角→半角へ丸めて、一時変数に保持
            $tmp_numeric_columns_value = mb_convert_kana(
                str_replace(
                    $search,
                    $replace,
                    $columns_value
                ),
                'n'
            );

            if (is_numeric($tmp_numeric_columns_value)) {
                // 全角→半角変換した結果が数値の場合
                return $tmp_numeric_columns_value;
            }
        }

        return $columns_value;
    }

    /**
     * データ登録
     */
    public function publicStore($request, $page_id, $frame_id, $id = null, $isTemporary = false)
    {
        // Databases、Frame データ
        $database = $this->getDatabases($frame_id);

        if ($isTemporary) {
            $status = 1;  // 一時保存
        } else {
            // 承認の要否確認とステータス処理
            if ($this->buckets->needApprovalUser(Auth::user())) {
                $status = 2;  // 承認待ち
            } else {
                $status = 0;  // 公開
            }
        }

        // 変更の場合（行 idが渡ってきたら）、既存の行データを使用。新規の場合は行レコード取得
        if (empty($id)) {
            $databases_inputs = new DatabasesInputs();
            $databases_inputs->databases_id = $database->id;
            $databases_inputs->status = $status;
            $databases_inputs->posted_at = $request->posted_at . ':00';
            $databases_inputs->save();
        } else {
            $databases_inputs = DatabasesInputs::where('id', $id)->first();
            // 更新されたら、行レコードの updated_at を更新したいので、update()
            $databases_inputs->updated_at = now();
            $databases_inputs->status = $status;
            $databases_inputs->posted_at = $request->posted_at . ':00';
            $databases_inputs->update();
        }

        // ファイル（uploadsテーブル＆実ファイル）の削除。データ登録前に削除する。（後からだと内容が変わっていてまずい）
        if (!empty($id) && $request->has('delete_upload_column_ids')) {
            foreach ($request->delete_upload_column_ids as $delete_upload_column_id) {
                if ($delete_upload_column_id) {
                    // 削除するファイル情報が入っている詳細データの特定
                    $del_databases_input_cols = DatabasesInputCols::where('databases_inputs_id', $id)
                                                                  ->where('databases_columns_id', $delete_upload_column_id)
                                                                  ->first();
                    // ファイルが添付されていた場合
                    if ($del_databases_input_cols && $del_databases_input_cols->value) {
                        // 削除するファイルデータ
                        $delete_upload = Uploads::find($del_databases_input_cols->value);

                        // ファイルの削除
                        if ($delete_upload) {
                            $directory = $this->getDirectory($delete_upload->id);
                            Storage::delete($directory . '/' . $delete_upload->id . '.' .$delete_upload->extension);

                            // データベースの削除
                            $delete_upload->delete();
                        }
                    }
                }
            }
        }

        // データベースのカラムデータ
        $databases_columns = DatabasesColumns::where('databases_id', $database->id)->orderBy('display_sequence')->get();

        // 権限のよって登録・編集の非表示columのdatabases_columns_id配列を取得する
        $hide_columns_ids = $this->getHideColumnsIds($databases_columns, 'regist_edit_display_flag');
        // Log::debug('[' . __METHOD__ . '] ' . __FILE__ . ' (line ' . __LINE__ . ')');
        // Log::debug(var_export($databases_columns_ids, true));

        // id（行 id）が渡ってきたら、詳細データは一度消す。その後、登録と同じ処理にする。
        // delete -> insertのため、権限非表示カラムは消さずに残す。
        if (!empty($id)) {
            DatabasesInputCols::where('databases_inputs_id', $id)
                                ->whereNotIn('databases_columns_id', $hide_columns_ids)
                                ->delete();
        }

        // データベースのカラムデータ 権限非表示カラムを除いて再取得
        $databases_columns = DatabasesColumns::where('databases_id', $database->id)
                                                ->whereNotIn('id', $hide_columns_ids)
                                                ->orderBy('display_sequence')
                                                ->get();

        // delete: フォームの名残で残っていたメール送信処理をコメントアウト
        // // メールの送信文字列
        // $contents_text = '';

        // // 登録者のメールアドレス
        // $user_mailaddresses = array();

        // databases_input_cols 登録
        foreach ($databases_columns as $databases_column) {
            // 登録日型・更新日型・公開日型は、databases_inputsテーブルの登録日・更新日・公開日を利用するため、登録しない
            if ($databases_column->column_type == \DatabaseColumnType::created ||
                    $databases_column->column_type == \DatabaseColumnType::updated ||
                    $databases_column->column_type == \DatabaseColumnType::posted) {
                continue;
            }

            $value = "";
            if (is_array($request->databases_columns_value[$databases_column->id])) {
                $value = implode(',', $request->databases_columns_value[$databases_column->id]);
            } else {
                $value = $request->databases_columns_value[$databases_column->id];
            }

            // ファイル系で削除指示があるものは、

            // データ登録フラグを見て登録
            if ($database->data_save_flag) {
                $databases_input_cols = new DatabasesInputCols();
                $databases_input_cols->databases_inputs_id = $databases_inputs->id;
                $databases_input_cols->databases_columns_id = $databases_column['id'];
                $databases_input_cols->value = $value;
                $databases_input_cols->save();

                // ファイルタイプがファイル系の場合は、uploads テーブルの一時フラグを更新
                if (($databases_column->column_type == \DatabaseColumnType::file)  ||
                    ($databases_column->column_type == \DatabaseColumnType::image) ||
                    ($databases_column->column_type == \DatabaseColumnType::video)) {
                    $uploads_count = Uploads::where('id', $value)->update(['temporary_flag' => 0]);
                }
            }

            // delete: フォームの名残で残っていたメール送信処理をコメントアウト
            // // メールの内容
            // $contents_text .= $databases_column->column_name . "：" . $value . "\n";

            // // メール型
            // if ($databases_column->column_type == \DatabaseColumnType::mail) {
            //     $user_mailaddresses[] = $value;
            // }
        }

        // delete: フォームの名残で残っていたメール送信処理をコメントアウト
        // // 最後の改行を除去
        // $contents_text = trim($contents_text);

        // // 採番 ※[採番プレフィックス文字列] + [ゼロ埋め採番6桁]
        // $number = $database->numbering_use_flag ? $database->numbering_prefix . sprintf('%06d', $this->getNo('databases', $database->bucket_id, $database->numbering_prefix)) : null;

        // // 登録後メッセージ内の採番文字列を置換
        // // $after_message = str_replace('[[number]]', $number, $database->after_message);

        // // メール送信
        // if ($database->mail_send_flag) {
        //     // メール本文の組み立て
        //     $mail_databaseat = $database->mail_databaseat;
        //     $mail_text = str_replace('[[body]]', $contents_text, $mail_databaseat);

        //     // メール本文内の採番文字列を置換
        //     $mail_text = str_replace('[[number]]', $number, $mail_text);

        //     // メール送信（管理者側）
        //     $mail_addresses = explode(',', $database->mail_send_address);
        //     foreach ($mail_addresses as $mail_address) {
        //         Mail::to($mail_address)->send(new ConnectMail(['subject' => $database->mail_subject, 'template' => 'mail.send'], ['content' => $mail_text]));
        //     }

        //     // メール送信（ユーザー側）
        //     foreach ($user_mailaddresses as $user_mailaddress) {
        //         if (!empty($user_mailaddress)) {
        //             Mail::to($user_mailaddress)->send(new ConnectMail(['subject' => $database->mail_subject, 'template' => 'mail.send'], ['content' => $mail_text]));
        //         }
        //     }
        // }

        // 登録時のAction を/redirect/plugin にしたため、ここでreturn しなくてよい。

        // 表示テンプレートを呼び出す。
        //return $this->index($request, $page_id, $frame_id);
        /*
        return $this->view(
            'databases_thanks', [
            'after_message' => $after_message
        ]);
        */
    }

    /**
     * データ削除
     */
    public function delete($request, $page_id, $frame_id, $id)
    {
        // 行 idがなければ終了
        if (empty($id)) {
            // 表示テンプレートを呼び出す。
            return $this->index($request, $page_id, $frame_id);
        }

        // ファイル型の調査のため、詳細カラムデータを取得
        $input_cols = $this->getDatabasesInputCols($id);

        // ファイル型のファイル、uploads テーブルを削除
        foreach ($input_cols as $input_col) {
            if (($input_col->column_type == \DatabaseColumnType::file) ||
                ($input_col->column_type == \DatabaseColumnType::image) ||
                ($input_col->column_type == \DatabaseColumnType::video)) {
                // 削除するファイルデータ
                $delete_upload = Uploads::find($input_col->value);

                // ファイルの削除
                if ($delete_upload) {
                    $directory = $this->getDirectory($delete_upload->id);
                    Storage::delete($directory . '/' . $delete_upload->id . '.' .$delete_upload->extension);

                    // データベースの削除
                    $delete_upload->delete();
                }
            }
        }

        // 詳細カラムデータを削除
        DatabasesInputCols::where('databases_inputs_id', $id)->delete();

        // 行データを削除
        DatabasesInputs::where('id', $id)->delete();


        // 表示テンプレートを呼び出す。
        return $this->index($request, $page_id, $frame_id);


        // Databases、Frame データ
        $database = $this->getDatabases($frame_id);

        // 変更の場合（行 idが渡ってきたら）、既存の行データを使用。新規の場合は行レコード取得
        if (empty($id)) {
            $databases_inputs = new DatabasesInputs();
            $databases_inputs->databases_id = $database->id;
            $databases_inputs->save();
        } else {
            $databases_inputs = DatabasesInputs::where('id', $id)->first();
        }

        // ファイル（uploadsテーブル＆実ファイル）の削除。データ登録前に削除する。（後からだと内容が変わっていてまずい）
        if (!empty($id) && $request->has('delete_upload_column_ids')) {
            foreach ($request->delete_upload_column_ids as $delete_upload_column_id) {
                if ($delete_upload_column_id) {
                    // 削除するファイル情報が入っている詳細データの特定
                    $del_databases_input_cols = DatabasesInputCols::where('databases_inputs_id', $id)
                                                                  ->where('databases_columns_id', $delete_upload_column_id)
                                                                  ->first();
                    // ファイルが添付されていた場合
                    if ($del_databases_input_cols && $del_databases_input_cols->value) {
                        // 削除するファイルデータ
                        $delete_upload = Uploads::find($del_databases_input_cols->value);

                        // ファイルの削除
                        if ($delete_upload) {
                            $directory = $this->getDirectory($delete_upload->id);
                            Storage::delete($directory . '/' . $delete_upload->id . '.' .$delete_upload->extension);

                            // データベースの削除
                            $delete_upload->delete();
                        }
                    }
                }
            }
        }

        // id（行 id）が渡ってきたら、詳細データは一度消す。その後、登録と同じ処理にする。
        if (!empty($id)) {
            DatabasesInputCols::where('databases_inputs_id', $id)->delete();
        }

        // データベースのカラムデータ
        $databases_columns = DatabasesColumns::where('databases_id', $database->id)->orderBy('display_sequence')->get();

        // メールの送信文字列
        $contents_text = '';

        // 登録者のメールアドレス
        $user_mailaddresses = array();

        // databases_input_cols 登録
        foreach ($databases_columns as $databases_column) {
            $value = "";
            if (is_array($request->databases_columns_value[$databases_column->id])) {
                $value = implode(',', $request->databases_columns_value[$databases_column->id]);
            } else {
                $value = $request->databases_columns_value[$databases_column->id];
            }

            // ファイル系で削除指示があるものは、
            // データ登録フラグを見て登録
            if ($database->data_save_flag) {
                $databases_input_cols = new DatabasesInputCols();
                $databases_input_cols->databases_inputs_id = $databases_inputs->id;
                $databases_input_cols->databases_columns_id = $databases_column['id'];
                $databases_input_cols->value = $value;
                $databases_input_cols->save();

                // ファイルタイプがファイル系の場合は、uploads テーブルの一時フラグを更新
                if ($databases_column->column_type == \DatabaseColumnType::file) {
                    $uploads_count = Uploads::where('id', $value)->update(['temporary_flag' => 0]);
                }
            }

            // メールの内容
            $contents_text .= $databases_column->column_name . "：" . $value . "\n";

            // メール型
            if ($databases_column->column_type == \DatabaseColumnType::mail) {
                $user_mailaddresses[] = $value;
            }
        }

        // 最後の改行を除去
        $contents_text = trim($contents_text);

        // 採番 ※[採番プレフィックス文字列] + [ゼロ埋め採番6桁]
        $number = $database->numbering_use_flag ? $database->numbering_prefix . sprintf('%06d', $this->getNo('databases', $database->bucket_id, $database->numbering_prefix)) : null;

        // 登録後メッセージ内の採番文字列を置換
        $after_message = str_replace('[[number]]', $number, $database->after_message);

        // メール送信
        if ($database->mail_send_flag) {
            // メール本文の組み立て
            $mail_databaseat = $database->mail_databaseat;
            $mail_text = str_replace('[[body]]', $contents_text, $mail_databaseat);

            // メール本文内の採番文字列を置換
            $mail_text = str_replace('[[number]]', $number, $mail_text);

            // メール送信（管理者側）
            $mail_addresses = explode(',', $database->mail_send_address);
            foreach ($mail_addresses as $mail_address) {
                Mail::to($mail_address)->send(new ConnectMail(['subject' => $database->mail_subject, 'template' => 'mail.send'], ['content' => $mail_text]));
            }

            // メール送信（ユーザー側）
            foreach ($user_mailaddresses as $user_mailaddress) {
                if (!empty($user_mailaddress)) {
                    Mail::to($user_mailaddress)->send(new ConnectMail(['subject' => $database->mail_subject, 'template' => 'mail.send'], ['content' => $mail_text]));
                }
            }
        }

        // 削除時のAction を/redirect/plugin にしたため、ここでreturn しなくてよい。

        // 表示テンプレートを呼び出す。
        //return $this->index($request, $page_id, $frame_id);
        /*
        return $this->view(
            'databases_thanks', [
            'after_message' => $after_message
        ]);
        */
    }

    /**
     * データベース選択表示関数
     */
    public function listBuckets($request, $page_id, $frame_id, $id = null)
    {
        // 対象のプラグイン
        $plugin_name = $this->frame->plugin_name;

        // Frame データ
        $plugin_frame = DB::table('frames')
                            ->select('frames.*')
                            ->where('frames.id', $frame_id)->first();

        // データ取得（1ページの表示件数指定）
        $plugins = DB::table($plugin_name)
                       ->select($plugin_name . '.*', $plugin_name . '.' . $plugin_name . '_name as plugin_bucket_name')
                       ->orderBy('created_at', 'desc')
                       ->paginate(10, ["*"], "frame_{$frame_id}_page");

        // 表示テンプレートを呼び出す。
        return $this->view(
            'databases_list_buckets', [
            'plugin_frame' => $plugin_frame,
            'plugins'      => $plugins,
            ]
        );
    }

    /**
     * データベース新規作成画面
     */
    public function createBuckets($request, $page_id, $frame_id, $databases_id = null, $create_flag = false, $message = null, $errors = null)
    {
        // 新規作成フラグを付けてデータベース設定変更画面を呼ぶ
        $create_flag = true;
        return $this->editBuckets($request, $page_id, $frame_id, $databases_id, $create_flag, $message, $errors);
    }

    /**
     * データベース設定変更画面の表示
     */
    public function editBuckets($request, $page_id, $frame_id, $databases_id = null, $create_flag = false, $message = null, $errors = null)
    {
        // セッション初期化などのLaravel 処理。
        $request->flash();

        // データベース＆フレームデータ
        $database_frame = $this->getDatabaseFrame($frame_id);

        // データベースデータ
        $database = new Databases();

        // databases_id が渡ってくればdatabases_id が対象
        if (!empty($databases_id)) {
            $database = Databases::where('id', $databases_id)->first();
        } elseif (!empty($database_frame->bucket_id) && $create_flag == false) {
            // Frame のbucket_id があれば、bucket_id からデータベースデータ取得、なければ、新規作成か選択へ誘導
            $database = Databases::where('bucket_id', $database_frame->bucket_id)->first();
        }

        // 表示テンプレートを呼び出す。
        return $this->view(
            'databases_edit_database',
            [
                'database_frame'  => $database_frame,
                'database'        => $database,
                'create_flag' => $create_flag,
                'message'     => $message,
                'errors'      => $errors,
            ]
        )->withInput($request->all);
    }

    /**
     *  データベース登録処理
     */
    public function saveBuckets($request, $page_id, $frame_id, $databases_id = null)
    {

        // デフォルトで必須
        $validator_values['databases_name'] = ['required'];
        $validator_attributes['databases_name'] = 'データベース名';

        // 「以下のアドレスにメール送信する」がONの場合、送信するメールアドレスは必須
        if ($request->mail_send_flag) {
            $validator_values['mail_send_address'] = [
                'required'
            ];
            $validator_attributes['mail_send_address'] = '送信するメールアドレス';
        }

        // 項目のエラーチェック
        $validator = Validator::make($request->all(), $validator_values);
        $validator->setAttributeNames($validator_attributes);

        // エラーがあった場合は入力画面に戻る。
        $message = null;
        if ($validator->fails()) {
            if (empty($databases_id)) {
                $create_flag = true;
                return $this->createBuckets($request, $page_id, $frame_id, $databases_id, $create_flag, $message, $validator->errors());
            } else {
                $create_flag = false;
                return $this->editBuckets($request, $page_id, $frame_id, $databases_id, $create_flag, $message, $validator->errors());
            }
        }

        // 更新後のメッセージ
        $message = null;

        // 画面から渡ってくるdatabases_id が空ならバケツとブログを新規登録
        if (empty($request->databases_id)) {
            // バケツの登録
            $bucket = new Buckets();
            // $bucket->bucket_name = '無題';
            $bucket->bucket_name = $request->databases_name;
            $bucket->plugin_name = 'databases';
            $bucket->save();

            // ブログデータ新規オブジェクト
            $databases = new Databases();
            $databases->bucket_id = $bucket->id;

            // Frame のBuckets を見て、Buckets が設定されていなければ、作成したものに紐づける。
            // Frame にBuckets が設定されていない ＞ 新規のフレーム＆ブログ作成
            // Frame にBuckets が設定されている ＞ 既存のフレーム＆ブログ更新
            // （表示データベース選択から遷移してきて、内容だけ更新して、フレームに紐づけないケースもあるため）
            $frame = Frame::where('id', $frame_id)->first();
            if (empty($frame->bucket_id)) {
                // FrameのバケツIDの更新
                $frame = Frame::where('id', $frame_id)->update(['bucket_id' => $bucket->id]);
            }

            // DB作成後に表示される項目設定リンクは、作成したDBではなく、表示中DBの項目設定リンクのため、一旦リンクを外す
            // 項目設定周りが databases_id に対応できたら、databases_id をリンク含めて復活できると思う
            // $message = 'データベース設定を追加しました。<br />　 データベースで使用する項目を設定してください。［ <a href="' . url('/') . '/plugin/databases/editColumn/' . $page_id . '/' . $frame_id . '/#frame-' . $frame_id . '">項目設定</a> ］';
            $message = 'データベース設定を追加しました。<br />' .
                        '　 [ <a href="' . url('/') . '/plugin/databases/listBuckets/' . $page_id . '/' . $frame_id . '/#frame-' . $frame_id . '">DB選択</a> ]から作成したデータベースを選択後、［ 項目設定 ］で使用する項目を設定してください。';
        } else {
            // databases_id があれば、データベースを更新
            // データベースデータ取得
            $databases = Databases::where('id', $request->databases_id)->first();

            // データベース名で、Buckets名も更新する
            Buckets::where('id', $databases->bucket_id)->update(['bucket_name' => $request->databases_name]);

            $message = 'データベース設定を変更しました。';
        }

        // データベース設定
        $databases->databases_name      = $request->databases_name;
        $databases->mail_send_flag      = (empty($request->mail_send_flag))      ? 0 : $request->mail_send_flag;
        $databases->mail_send_address   = $request->mail_send_address;
        $databases->user_mail_send_flag = (empty($request->user_mail_send_flag)) ? 0 : $request->user_mail_send_flag;
        $databases->from_mail_name      = $request->from_mail_name;
        $databases->mail_subject        = $request->mail_subject;
        $databases->mail_databaseat     = $request->mail_databaseat;
        $databases->data_save_flag      = (empty($request->data_save_flag))      ? 0 : $request->data_save_flag;
        $databases->after_message       = $request->after_message;
        $databases->numbering_use_flag  = (empty($request->numbering_use_flag))  ? 0 : $request->numbering_use_flag;
        $databases->numbering_prefix    = $request->numbering_prefix;

        // データ保存
        $databases->save();

        // 新規作成フラグを付けてデータベース設定変更画面を呼ぶ
        $create_flag = false;

        // bugfix: 登録後は登録後の$databases->idを渡す。渡さないと作成後に表示中のDBの変更画面になり、そこに作成したDB名がセットされた状態で表示される
        // return $this->editBuckets($request, $page_id, $frame_id, $databases_id, $create_flag, $message);
        return $this->editBuckets($request, $page_id, $frame_id, $databases->id, $create_flag, $message);
    }

    /**
     *  データベース削除処理
     */
    public function destroyBuckets($request, $page_id, $frame_id, $databases_id)
    {
        // databases_id がある場合、データを削除
        if ($databases_id) {
            // カラム権限データを削除する。
            DatabasesColumnsRole::where('databases_id', $databases_id)->delete();

            $databases_columns = DatabasesColumns::where('databases_id', $databases_id)->orderBy('display_sequence')->get();
            foreach ($databases_columns as $databases_column) {
                // カラムに紐づく選択肢の削除
                $this->deleteColumnsSelects($databases_column->id);
            }

            // カラムデータを削除する。
            DatabasesColumns::where('databases_id', $databases_id)->delete();

            // データベース設定を削除する。
            Databases::destroy($databases_id);

            // バケツIDの取得のためにFrame を取得(Frame を更新する前に取得しておく)
            $frame = Frame::where('id', $frame_id)->first();

            // FrameのバケツIDの更新
            Frame::where('bucket_id', $frame->bucket_id)->update(['bucket_id' => null]);

            // backetsの削除
            Buckets::where('id', $frame->bucket_id)->delete();
        }
        // 削除処理はredirect 付のルートで呼ばれて、処理後はページの再表示が行われるため、ここでは何もしない。
    }

    /**
     * データ紐づけ変更関数
     */
    public function changeBuckets($request, $page_id = null, $frame_id = null, $id = null)
    {
        // FrameのバケツIDの更新
        Frame::where('id', $frame_id)
                ->update(['bucket_id' => $request->select_bucket]);

        // 関連するセッションクリア
        $request->session()->forget('databases.'.$frame_id);

        // 表示ブログ選択画面を呼ぶ
        return $this->listBuckets($request, $page_id, $frame_id, $id);
    }

    /**
     * 項目の追加
     */
    public function addColumn($request, $page_id, $frame_id, $id = null)
    {
        // エラーチェック
        $validator = Validator::make($request->all(), [
            'column_name'  => ['required'],
            'column_type'  => ['required'],
        ]);
        $validator->setAttributeNames([
            'column_name'  => '項目名',
            'column_type'  => '型',
        ]);

        $errors = null;
        if ($validator->fails()) {
            // エラーと共に編集画面を呼び出す
            $errors = $validator->errors();
            return $this->editColumn($request, $page_id, $frame_id, $request->databases_id, null, $errors);
        }

        // 新規登録時の表示順を設定
        $max_display_sequence = DatabasesColumns::query()->where('databases_id', $request->databases_id)->max('display_sequence');
        $max_display_sequence = $max_display_sequence ? $max_display_sequence + 1 : 1;

        // 項目の登録処理
        $column = new DatabasesColumns();
        $column->databases_id = $request->databases_id;
        $column->column_name = $request->column_name;
        $column->column_type = $request->column_type;
        $column->required = $request->required ? \Required::on : \Required::off;
        $column->display_sequence = $max_display_sequence;
        $column->caption_color = \Bs4TextColor::dark;

        // 複数年月型（テキスト入力）は、デフォルトでキャプションをセットする
        if (\DatabaseColumnType::dates_ym == $request->column_type) {
            $column->caption = \DatabaseColumnType::dates_ym_caption;
        }

        $column->save();
        $message = '項目【 '. $request->column_name .' 】を追加しました。';

        // 編集画面へ戻る。
        return $this->editColumn($request, $page_id, $frame_id, $request->databases_id, $message, $errors);
    }

    /**
     * 項目の詳細画面の表示
     */
    public function editColumnDetail($request, $page_id, $frame_id, $column_id, $message = null, $errors = null)
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
        $database_db = $this->getDatabases($frame_id);

        // データベースのID。まだデータベースがない場合は0
        $databases_id = 0;
        if (!empty($database_db)) {
            $databases_id = $database_db->id;
        }

        // --- 画面に値を渡す準備
        $column = DatabasesColumns::where('id', $column_id)->first();
        $selects = DatabasesColumnsSelects::where('databases_columns_id', $column->id)->orderBy('display_sequence', 'asc')->get();
        $columns_roles = DatabasesColumnsRole::where('databases_columns_id', $column->id)
                                                ->get()
                                                // keyをrole_nameにした結果をセット
                                                ->mapWithKeys(function ($item) {
                                                    return [$item['role_name'] => $item];
                                                });

        // 編集画面テンプレートを呼び出す。
        return $this->view(
            'databases_edit_row_detail',
            [
                'databases_id' => $databases_id,
                'column' => $column,
                'selects' => $selects,
                'columns_roles' => $columns_roles,
                'message' => $message,
                'errors' => $errors,
            ]
        );
    }

    /**
     * カラム編集画面の表示
     */
    public function editColumn($request, $page_id, $frame_id, $id = null, $message = null, $errors = null)
    {
        if ($errors) {
            // エラーあり：入力値をフラッシュデータとしてセッションへ保存
            $request->flash();
        } else {
            // エラーなし：セッションから入力値を消去
            $request->flush();
        }

        // フレームに紐づくデータベースID を探して取得
        $database_db = $this->getDatabases($frame_id);

        // データベースのID。まだデータベースがない場合は0
        $databases_id = 0;
        if (!empty($database_db)) {
            $databases_id = $database_db->id;
        }

        // 項目データ取得
        // 予約項目データ
        $columns = DatabasesColumns::query()
            ->select(
                'databases_columns.id',
                'databases_columns.databases_id',
                'databases_columns.column_type',
                'databases_columns.column_name',
                'databases_columns.required',
                'databases_columns.frame_col',
                'databases_columns.title_flag',
                'databases_columns.caption',
                'databases_columns.caption_color',
                'databases_columns.classname',
                'databases_columns.display_sequence',
                'databases_columns.row_group',
                'databases_columns.column_group',
                DB::raw('count(databases_columns_selects.id) as select_count'),
                DB::raw('GROUP_CONCAT(databases_columns_selects.value order by databases_columns_selects.display_sequence SEPARATOR \',\') as select_names')
            )
            ->where('databases_columns.databases_id', $databases_id)
            // 予約項目の子データ（選択肢）
            ->leftjoin('databases_columns_selects', function ($join) {
                $join->on('databases_columns.id', '=', 'databases_columns_selects.databases_columns_id');
            })
            ->groupby(
                'databases_columns.id',
                'databases_columns.databases_id',
                'databases_columns.column_type',
                'databases_columns.column_name',
                'databases_columns.required',
                'databases_columns.frame_col',
                'databases_columns.title_flag',
                'databases_columns.caption',
                'databases_columns.caption_color',
                'databases_columns.classname',
                'databases_columns.display_sequence',
                'databases_columns.row_group',
                'databases_columns.column_group'
            )
            ->orderby('databases_columns.display_sequence')
            ->get();

        // 新着等のタイトル指定 が設定されているか（データベース毎に１つ設定）
        $title_flag = 0;
        foreach ($columns as $column) {
            if ($column->title_flag) {
                $title_flag = 1;
                break;
            }
        }

        // 編集画面テンプレートを呼び出す。
        return $this->view(
            'databases_edit',
            [
                'databases_id'   => $databases_id,
                'columns'    => $columns,
                'title_flag' => $title_flag,
                'message'    => $message,
                'errors'     => $errors,
            ]
        );
    }

    /**
     * 項目の削除
     */
    public function deleteColumn($request, $page_id, $frame_id)
    {
        // 明細行から削除対象の項目名を抽出
        $str_column_name = "column_name_"."$request->column_id";

        // カラム権限の削除
        DatabasesColumnsRole::where('databases_columns_id', $request->column_id)->delete();

        // 項目の削除
        DatabasesColumns::where('id', $request->column_id)->delete();

        // 項目に紐づく選択肢の削除
        $this->deleteColumnsSelects($request->column_id);
        $message = '項目【 '. $request->$str_column_name .' 】を削除しました。';

        // 編集画面へ戻る。
        return $this->editColumn($request, $page_id, $frame_id, $request->databases_id, $message, null);
    }

    /**
     * 項目の更新
     */
    public function updateColumn($request, $page_id, $frame_id)
    {
        // 明細行から更新対象を抽出する為のnameを取得
        $str_column_name = "column_name_"."$request->column_id";
        $str_column_type = "column_type_"."$request->column_id";
        $str_required = "required_"."$request->column_id";

        // エラーチェック用に値を詰める
        $request->merge([
            "column_name" => $request->$str_column_name,
            "column_type" => $request->$str_column_type,
            "required" => $request->$str_required,
        ]);

        $validate_value = [
            'column_name'  => ['required'],
            'column_type'  => ['required'],
        ];

        $validate_attribute = [
            'column_name'  => '項目名',
            'column_type'  => '型',
        ];

        // エラーチェック
        $validator = Validator::make($request->all(), $validate_value);
        $validator->setAttributeNames($validate_attribute);

        $errors = null;
        if ($validator->fails()) {
            // エラーと共に編集画面を呼び出す
            $errors = $validator->errors();
            return $this->editColumn($request, $page_id, $frame_id, $request->databases_id, null, $errors);
        }

        // 項目の更新処理
        $column = DatabasesColumns::query()->where('id', $request->column_id)->first();
        $column->column_name = $request->column_name;
        $column->column_type = $request->column_type;
        $column->required = $request->required ? \Required::on : \Required::off;

        // 複数年月型（テキスト入力）は、キャプションが空なら定型文をセットする
        if (\DatabaseColumnType::dates_ym == $request->column_type &&
                !$column->caption) {
            $column->caption = \DatabaseColumnType::dates_ym_caption;
        }

        $column->save();
        $message = '項目【 '. $request->column_name .' 】を更新しました。';

        // 編集画面を呼び出す
        return $this->editColumn($request, $page_id, $frame_id, $request->databases_id, $message, $errors);
    }

    /**
     * 項目の表示順の更新
     */
    public function updateColumnSequence($request, $page_id, $frame_id)
    {
        // ボタンが押された行の施設データ
        $target_column = DatabasesColumns::query()
            ->where('databases_id', $request->databases_id)
            ->where('id', $request->column_id)
            ->first();

        // ボタンが押された前（後）の施設データ
        $query = DatabasesColumns::query()
            ->where('databases_id', $request->databases_id);
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

        $message = '項目【 '. $target_column->column_name .' 】の表示順を更新しました。';

        // 編集画面を呼び出す
        return $this->editColumn($request, $page_id, $frame_id, $request->databases_id, $message, null);
    }

    /**
     * 項目に紐づく選択肢の更新
     */
    public function updateColumnDetail($request, $page_id, $frame_id)
    {
        $column = DatabasesColumns::where('id', $request->column_id)->first();

        $validator_values = null;
        $validator_attributes = null;

        // 桁数チェックの指定時、入力値が数値であるかチェック
        if ($request->rule_digits_or_less) {
            $validator_values['rule_digits_or_less'] = [
                'numeric',
            ];
            $validator_attributes['rule_digits_or_less'] = '入力桁数';
        }
        // 最大値の指定時、入力値が数値であるかチェック
        if ($request->rule_max) {
            $validator_values['rule_max'] = [
                'numeric',
            ];
            $validator_attributes['rule_max'] = '最大値';
        }
        // 最小値の指定時、入力値が数値であるかチェック
        if ($request->rule_min) {
            $validator_values['rule_min'] = [
                'numeric',
            ];
            $validator_attributes['rule_min'] = '最小値';
        }
        // 入力文字数の指定時、入力値が数値であるかチェック
        if ($request->rule_word_count) {
            $validator_values['rule_word_count'] = [
                'numeric',
            ];
            $validator_attributes['rule_word_count'] = '入力最大文字数';
        }
        // ～日以降許容を指定時、入力値が数値であるかチェック
        if ($request->rule_date_after_equal) {
            $validator_values['rule_date_after_equal'] = [
                'numeric',
            ];
            $validator_attributes['rule_date_after_equal'] = '～日以降を許容';
        }
        // 行グループを指定時、入力値が数値であるかチェック
        if ($request->row_group) {
            $validator_values['row_group'] = [
                'numeric',
            ];
            $validator_attributes['row_group'] = '行グループ';
        }
        // 列グループを指定時、入力値が数値であるかチェック
        if ($request->column_group) {
            $validator_values['column_group'] = [
                'numeric',
            ];
            $validator_attributes['column_group'] = '列グループ';
        }

        // エラーチェック
        if ($validator_values) {
            $validator = Validator::make($request->all(), $validator_values);
            $validator->setAttributeNames($validator_attributes);

            $errors = null;
            if ($validator->fails()) {
                // エラーと共に編集画面を呼び出す
                $errors = $validator->errors();
                return $this->editColumnDetail($request, $page_id, $frame_id, $request->column_id, null, $errors);
            }
        }

        // タイトル指定
        $title_flag = (empty($request->title_flag)) ? 0 : $request->title_flag;
        if ($title_flag) {
            // title_flagはデータベース内で１つだけ ON にする項目
            // そのため title_flag = 1 なら データベース内の title_flag = 1 を一度 0 に更新する。
            DatabasesColumns::where('databases_id', $request->databases_id)
                    ->where('title_flag', 1)
                    ->update(['title_flag' => 0]);
        }

        // タイトル指定
        $column->title_flag = $title_flag;

        // 項目の更新処理
        $column->caption = $request->caption;
        $column->caption_color = $request->caption_color;
        $column->frame_col = $request->frame_col;
        $column->classname = $request->classname;
        // 分刻み指定
        if ($column->column_type == \DatabaseColumnType::time) {
            $column->minutes_increments = $request->minutes_increments;
        }
        // 数値のみ許容
        $column->rule_allowed_numeric = (empty($request->rule_allowed_numeric)) ? 0 : $request->rule_allowed_numeric;
        // 英数値のみ許容
        $column->rule_allowed_alpha_numeric = (empty($request->rule_allowed_alpha_numeric)) ? 0 : $request->rule_allowed_alpha_numeric;
        // 入力桁数
        $column->rule_digits_or_less = $request->rule_digits_or_less;
        // 入力文字数
        $column->rule_word_count = $request->rule_word_count;
        // 最大値
        $column->rule_max = $request->rule_max;
        // 最小値
        $column->rule_min = $request->rule_min;
        // ～日以降を許容
        $column->rule_date_after_equal = $request->rule_date_after_equal;

        // DBカラム設定

        // 一覧から非表示にする指定
        $column->list_hide_flag = (empty($request->list_hide_flag)) ? 0 : $request->list_hide_flag;
        // 詳細から非表示にする指定
        $column->detail_hide_flag = (empty($request->detail_hide_flag)) ? 0 : $request->detail_hide_flag;
        // 権限で表示カラムを制御
        $column->role_display_control_flag = (empty($request->role_display_control_flag)) ? 0 : $request->role_display_control_flag;
        // 並べ替え指定
        $column->sort_flag = (empty($request->sort_flag)) ? 0 : $request->sort_flag;
        // 検索対象指定
        $column->search_flag = (empty($request->search_flag)) ? 0 : $request->search_flag;
        // 絞り込み対象指定
        $column->select_flag = (empty($request->select_flag)) ? 0 : $request->select_flag;
        // 行グループ
        $column->row_group = $request->row_group;
        // 列グループ
        $column->column_group = $request->column_group;

        // 保存
        $column->save();


        // delete -> insert
        // カラム権限データを削除する。
        DatabasesColumnsRole::where('databases_columns_id', $request->column_id)->delete();

        $column_role_name_keys = \DatabaseColumnRoleName::getMemberKeys();

        foreach ($column_role_name_keys as $column_role_name_key) {
            if (! isset($request->$column_role_name_key)) {
                // チェックなしの権限はスルー
                continue;
            }
            $list_detail_display_flag = isset($request->$column_role_name_key['list_detail_display_flag']) ? $request->$column_role_name_key['list_detail_display_flag'] : 0;
            $regist_edit_display_flag = isset($request->$column_role_name_key['regist_edit_display_flag']) ? $request->$column_role_name_key['regist_edit_display_flag'] : 0;

            $columns_role = new DatabasesColumnsRole();
            $columns_role->databases_id = $request->databases_id;
            $columns_role->databases_columns_id = $request->column_id;
            $columns_role->role_name = $column_role_name_key;
            $columns_role->list_detail_display_flag = $list_detail_display_flag;
            $columns_role->regist_edit_display_flag = $regist_edit_display_flag;

            // 保存
            $columns_role->save();
        }

        $message = '項目【 '. $column->column_name .' 】を更新しました。';

        // 編集画面を呼び出す
        return $this->editColumnDetail($request, $page_id, $frame_id, $request->column_id, $message, null);
    }

    /**
     * 項目に紐づく選択肢の登録
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
            return $this->editColumnDetail($request, $page_id, $frame_id, $request->column_id, null, $errors);
        }

        // 新規登録時の表示順を設定
        $max_display_sequence = DatabasesColumnsSelects::query()->where('databases_columns_id', $request->column_id)->max('display_sequence');
        $max_display_sequence = $max_display_sequence ? $max_display_sequence + 1 : 1;

        // 施設の登録処理
        $select = new DatabasesColumnsSelects();
        $select->databases_columns_id = $request->column_id;
        $select->value = $request->select_name;
        $select->display_sequence = $max_display_sequence;
        $select->save();
        $message = '選択肢【 '. $request->select_name .' 】を追加しました。';

        // 編集画面を呼び出す
        return $this->editColumnDetail($request, $page_id, $frame_id, $request->column_id, $message, $errors);
    }

    /**
     * 項目に紐づく都道府県選択肢の登録
     */
    public function addPref($request, $page_id, $frame_id)
    {
        // 新規登録時の表示順を設定
        $max_display_sequence = DatabasesColumnsSelects::query()->where('databases_columns_id', $request->column_id)->max('display_sequence');
        $max_display_sequence = $max_display_sequence ? $max_display_sequence + 1 : 1;

        // 施設の登録処理
        foreach ($this->getPrefList() as $pref) {
            // uploads テーブルに情報追加、ファイルのid を取得する
            DatabasesColumnsSelects::create([
                'databases_columns_id' => $request->column_id,
                'value'                => $pref,
                'display_sequence'     => $max_display_sequence,
            ]);
            $max_display_sequence++;
        }
        $message = '選択肢【 '. $request->select_name .' 】を追加しました。';

        // 編集画面を呼び出す
        return $this->editColumnDetail($request, $page_id, $frame_id, $request->column_id, $message);
    }

    /**
     * 項目に紐づく選択肢の更新
     */
    public function updateSelect($request, $page_id, $frame_id)
    {
        // 明細行から更新対象を抽出する為のnameを取得
        $str_select_name = "select_name_"."$request->select_id";

        // エラーチェック用に値を詰める
        $request->merge([
            "select_name" => $request->$str_select_name,
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
        $select = DatabasesColumnsSelects::query()->where('id', $request->select_id)->first();
        $select->value = $request->select_name;
        $select->save();
        $message = '選択肢【 '. $request->select_name .' 】を更新しました。';

        // 編集画面を呼び出す
        return $this->editColumnDetail($request, $page_id, $frame_id, $request->column_id, $message, $errors);
    }

    /**
     * 項目に紐づく選択肢の表示順の更新
     */
    public function updateSelectSequence($request, $page_id, $frame_id)
    {
        // ボタンが押された行の施設データ
        $target_select = DatabasesColumnsSelects::query()
            ->where('id', $request->select_id)
            ->first();

        // ボタンが押された前（後）の施設データ
        $query = DatabasesColumnsSelects::query()
            ->where('databases_columns_id', $request->column_id);
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

        $message = '選択肢【 '. $target_select->value .' 】の表示順を更新しました。';

        // 編集画面を呼び出す
        return $this->editColumnDetail($request, $page_id, $frame_id, $request->column_id, $message, null);
    }

    /**
     * 項目に紐づく選択肢の削除
     */
    public function deleteSelect($request, $page_id, $frame_id)
    {

        // 削除
        DatabasesColumnsSelects::query()->where('id', $request->select_id)->delete();

        // 明細行から削除対象の選択肢名を抽出
        $str_select_name = "select_name_"."$request->select_id";
        $message = '選択肢【 '. $request->$str_select_name .' 】を削除しました。';

        // 編集画面を呼び出す
        return $this->editColumnDetail($request, $page_id, $frame_id, $request->column_id, $message, null);
    }

    /**
     * カラム選択肢削除
     */
    private function deleteColumnsSelects($columns_id)
    {
        if (!empty($columns_id)) {
            DB::table('databases_columns_selects')->where('databases_columns_id', $columns_id)->delete();
        }
    }

    /**
     * データベースデータダウンロード
     */
    public function downloadCsv($request, $page_id, $frame_id, $id, $data_output_flag = true)
    {

        // id で対象のデータの取得

        // データベースの取得
        $database = Databases::where('id', $id)->first();

        // カラムの取得
        $columns = DatabasesColumns::where('databases_id', $id)->orderBy('display_sequence', 'asc')->get();

        // move: インポートフォーマットのダウンロード対応するため、下に移動
        // // 登録データの取得
        // $input_cols = DatabasesInputCols::whereIn('databases_inputs_id', DatabasesInputs::select('id')->where('databases_id', $id))
        //                               ->orderBy('databases_inputs_id', 'asc')->orderBy('databases_columns_id', 'asc')
        //                               ->get();

        /*
        ダウンロード前の配列イメージ。
        0行目をDatabasesColumns から生成して、1行目以降は0行目の キーのみのコピーを作成し、データを入れ込んでいく。
        1行目以降の行番号は databases_inputs_id の値を使用

        0 [
            37 => 姓
            40 => 名
            45 => テキスト
        ]
        1 [
            37 => 永原
            40 => 篤
            45 => テストです。
        ]
        2 [
            37 => 田中
            40 =>
            45 =>
        ]

        -- DatabasesInputCols のSQL
        SELECT *
        FROM databases_input_cols
        WHERE databases_inputs_id IN (
            SELECT id FROM databases_inputs WHERE databases_id = 17
        )
        ORDER BY databases_inputs_id, databases_columns_id

        */
        // 返却用配列
        $csv_array = array();

        // データ行用の空配列
        $copy_base = array();

        // 見出し行
        foreach ($columns as $column) {
            $csv_array[0][$column->id] = $column->column_name;
            $copy_base[$column->id] = '';
        }

        // 固定項目
        $csv_array[0]['posted_at'] = '公開日時';
        $copy_base['posted_at'] = '';

        if ($data_output_flag) {
            // 登録データの取得
            $input_cols = DatabasesInputCols::
                                        select(
                                            'databases_input_cols.*',
                                            'databases_inputs.created_at  as inputs_created_at',
                                            'databases_inputs.updated_at  as inputs_updated_at',
                                            'databases_inputs.posted_at  as inputs_posted_at'
                                        )
                                        ->join('databases_inputs', 'databases_inputs.id', '=', 'databases_input_cols.databases_inputs_id')
                                        ->whereIn('databases_inputs_id', DatabasesInputs::select('id')->where('databases_id', $id))
                                        ->orderBy('databases_inputs_id', 'asc')->orderBy('databases_columns_id', 'asc')
                                        ->get();

            // データ
            foreach ($input_cols as $input_col) {
                if (!array_key_exists($input_col->databases_inputs_id, $csv_array)) {
                    // 初回のみベースをセット
                    $csv_array[$input_col->databases_inputs_id] = $copy_base;

                    $csv_array[$input_col->databases_inputs_id][$input_col->databases_columns_id] = $input_col->inputs_created_at;
                    $csv_array[$input_col->databases_inputs_id][$input_col->databases_columns_id] = $input_col->inputs_updated_at;
                    $csv_array[$input_col->databases_inputs_id][$input_col->databases_columns_id] = $input_col->inputs_posted_at;

                    // 初回で固定項目をセット
                    $databases_inputs = DatabasesInputs::where('id', $input_col->databases_inputs_id)->first();
                    // excelでは 2020-07-01 のハイフンや 2020/07/01 と頭ゼロが付けられないため、インポート時は修正できる日付形式に見直し
                    // $csv_array[$input_col->databases_inputs_id]['posted_at'] = $databases_inputs->posted_at->format('Y/m/d H:i');
                    $csv_array[$input_col->databases_inputs_id]['posted_at'] = $databases_inputs->posted_at->format('Y/n/j H:i');

                    // 登録日型、更新日型、公開日型は $input_cols に含まれないので、初回でセット
                    foreach ($columns as $column) {
                        switch ($column->column_type) {
                            case \DatabaseColumnType::created:
                                $csv_array[$input_col->databases_inputs_id][$column->id] = $input_col->inputs_created_at;
                                break;
                            case \DatabaseColumnType::updated:
                                $csv_array[$input_col->databases_inputs_id][$column->id] = $input_col->inputs_updated_at;
                                break;
                            case \DatabaseColumnType::posted:
                                $csv_array[$input_col->databases_inputs_id][$column->id] = $input_col->inputs_posted_at;
                                break;
                        }
                    }
                }

                $csv_array[$input_col->databases_inputs_id][$input_col->databases_columns_id] = $input_col->value;
            }
        }

        // レスポンス版
        $filename = $database->databases_name . '.csv';
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
            $csv_data .= "\n";
        }

        // 文字コード変換
        $csv_data = mb_convert_encoding($csv_data, "SJIS-win");

        return response()->make($csv_data, 200, $headers);
    }

    /**
     * インポート画面表示
     */
    public function import($request, $page_id, $frame_id, $id)
    {
        // id で対象のデータの取得

        // データベースの取得
        $database = Databases::where('id', $id)->first();

        // 表示テンプレートを呼び出す。
        return $this->view(
            'databases_import',
            [
                'database' => $database,
            ]
        );
    }

    /**
     * インポート
     */
    public function uploadCsv($request, $page_id, $frame_id, $id)
    {
        // 画面エラーチェック
        $validator = Validator::make($request->all(), [
            'databases_csv'  => [
                'required',
                'file',
                'mimes:csv,txt,zip', // mimesの都合上text/csvなのでtxtも許可が必要
                'mimetypes:text/plain,application/zip',
            ],
        ]);
        $validator->setAttributeNames([
            'databases_csv'  => 'CSVファイル',
        ]);

        if ($validator->fails()) {
            // Log::debug(var_export($validator->errors(), true));
            // エラーと共に編集画面を呼び出す
            return redirect()->back()->withErrors($validator)->withInput();
        }


        // ストリームフィルタとして登録. 5C問題対応
        // 参考：https://qiita.com/suin/items/3edfb9cb15e26bffba11
        stream_filter_register(
            'sjis_to_utf8_encoding_filter',
            SjisToUtf8EncodingFilter::class
        );

        // CSVファイル一時保孫
        $path = $request->file('databases_csv')->store('tmp');

        // 一行目（ヘッダ）読み込み
        $fp = fopen(storage_path('app/') . $path, 'r');
        // ファイル読み込み時に使うストリームフィルタを指定. 5C問題対応
        stream_filter_append($fp, 'sjis_to_utf8_encoding_filter');

        $header_columns = fgetcsv($fp, 0, ",");
        //dd(storage_path('app/') . $path);
        // Log::debug('$header_columns:'. var_export($header_columns, true));

        // カラムの取得
        $databases_columns = DatabasesColumns::where('databases_id', $id)->orderBy('display_sequence', 'asc')->get();
        $databases_column_names = [];
        foreach ($databases_columns as $databases_column) {
            $databases_column_names[] = $databases_column->column_name;
        }
        // 固定項目
        $databases_column_names[] = '公開日時';
        // Log::debug('$databases_columns:'. var_export($databases_columns, true));

        // ヘッダー項目のエラーチェック
        $error_msgs = $this->checkCsvHeader($header_columns, $databases_column_names);
        if (!empty($error_msgs)) {
            // 一時ファイルの削除
            fclose($fp);
            Storage::delete($path);

            // return ( $this->import($request, $page_id, $error_msgs) );
            return redirect()->back()->withErrors(['databases_csv' => $error_msgs])->withInput();
        }

        // データ項目のエラーチェック
        $error_msgs = $this->checkCvslines($fp, $databases_columns);
        if (!empty($error_msgs)) {
            // 一時ファイルの削除
            fclose($fp);
            Storage::delete($path);

            // return ( $this->import($request, $page_id, $error_msgs) );
            return redirect()->back()->withErrors(['databases_csv' => $error_msgs])->withInput();
        }

        // // 一時ファイルの削除
        // fclose($fp);
        // Storage::delete($path);
        // dd('ここまで');

        // ファイルを閉じて、開きなおす
        fclose($fp);
        $fp = fopen(storage_path('app/') . $path, 'r');

        // ヘッダー
        $header_columns = fgetcsv($fp, 0, ",");

        // データベースの取得
        $database = Databases::where('id', $id)->first();

        // データ
        while (($csv_columns = fgetcsv($fp, 0, ",")) !== false) {
            // --- 入力値変換
            foreach ($csv_columns as $col => &$csv_column) {
                // 入力値をトリム
                // $request->merge(self::trimInput($request->all()));
                $csv_column = self::trimInput($csv_column);

                // $csv_columnsは項目数分くる, $databases_columnsは項目数分ある。
                // よってこの２つの配列数は同じになる想定。issetでチェックしているが基本ある想定。
                if (isset($databases_columns[$col])) {
                    // 数値チェック
                    // if ($databases_column->rule_allowed_numeric) {
                    if ($databases_columns[$col]->rule_allowed_numeric) {
                        // 入力値があった場合（マイナスを意図した入力記号はすべて半角に置換する）＆ 全角→半角へ丸める
                        $csv_column = $this->convertNumericAndMinusZenkakuToHankaku($csv_column);
                    }
                }
            }

            // 配列の末尾から要素(公開日時)を取り除いて取得
            // CSVのデータ行の末尾は、必ず固定項目の公開日時の想定
            $posted_at = array_pop($csv_columns);
            $posted_at = new Carbon($posted_at);

            $status = 0;  // 公開

            // // 一時ファイルの削除
            // fclose($fp);
            // Storage::delete($path);
            // dd('ここまで' . $posted_at);

            // --- データ行の親データ、及び公開日時登録
            $databases_inputs = new DatabasesInputs();
            $databases_inputs->databases_id = $database->id;
            $databases_inputs->status = $status;
            // $databases_inputs->posted_at = $posted_at . ':00';
            $databases_inputs->posted_at = $posted_at;
            $databases_inputs->save();

            // // ファイル（uploadsテーブル＆実ファイル）の削除。データ登録前に削除する。（後からだと内容が変わっていてまずい）
            // if (!empty($id) && $request->has('delete_upload_column_ids')) {
            //     foreach ($request->delete_upload_column_ids as $delete_upload_column_id) {
            //         if ($delete_upload_column_id) {
            //             // 削除するファイル情報が入っている詳細データの特定
            //             $del_databases_input_cols = DatabasesInputCols::where('databases_inputs_id', $id)
            //                                                         ->where('databases_columns_id', $delete_upload_column_id)
            //                                                         ->first();
            //             // ファイルが添付されていた場合
            //             if ($del_databases_input_cols && $del_databases_input_cols->value) {
            //                 // 削除するファイルデータ
            //                 $delete_upload = Uploads::find($del_databases_input_cols->value);

            //                 // ファイルの削除
            //                 if ($delete_upload) {
            //                     $directory = $this->getDirectory($delete_upload->id);
            //                     Storage::delete($directory . '/' . $delete_upload->id . '.' .$delete_upload->extension);

            //                     // データベースの削除
            //                     $delete_upload->delete();
            //                 }
            //             }
            //         }
            //     }
            // }

            // --- データ行の各項目登録
            foreach ($csv_columns as $col => $csv_column) {
                // $csv_columnsは項目数分くる, $databases_columnsは項目数分ある。
                // よってこの２つの配列数は同じになる想定。issetでチェックしているが基本ある想定。
                if (isset($databases_columns[$col])) {
                    // 登録日型・更新日型・公開日型は、databases_inputsテーブルの登録日・更新日・公開日を利用するため、登録しない
                    if ($databases_columns[$col]->column_type == \DatabaseColumnType::created ||
                            $databases_columns[$col]->column_type == \DatabaseColumnType::updated ||
                            $databases_columns[$col]->column_type == \DatabaseColumnType::posted) {
                        continue;
                    }

                    // ファイルタイプがファイル系の場合は、登録しない（[TODO] 今後登録できるように見直し）
                    if (($databases_columns[$col]->column_type == \DatabaseColumnType::file)  ||
                        ($databases_columns[$col]->column_type == \DatabaseColumnType::image) ||
                        ($databases_columns[$col]->column_type == \DatabaseColumnType::video)) {
                        continue;
                    }

                    // change: データ登録フラグは、フォームの名残で残っているだけのため、フラグ見ないようする
                    // データ登録フラグを見て登録
                    // if ($database->data_save_flag) {
                    $databases_input_cols = new DatabasesInputCols();
                    $databases_input_cols->databases_inputs_id = $databases_inputs->id;
                    $databases_input_cols->databases_columns_id = $databases_columns[$col]['id'];
                    // $databases_input_cols->value = $value;
                    $databases_input_cols->value = $csv_column;
                    $databases_input_cols->save();

                        // // ファイルタイプがファイル系の場合は、uploads テーブルの一時フラグを更新
                        // if (($databases_columns[$col]->column_type == \DatabaseColumnType::file)  ||
                        //     ($databases_columns[$col]->column_type == \DatabaseColumnType::image) ||
                        //     ($databases_columns[$col]->column_type == \DatabaseColumnType::video)) {
                        //     // $uploads_count = Uploads::where('id', $value)->update(['temporary_flag' => 0]);
                        //     $uploads_count = Uploads::where('id', $csv_column)->update(['temporary_flag' => 0]);
                        // }
                    // }
                }
            }
        }

        // 一時ファイルの削除
        fclose($fp);
        Storage::delete($path);

        $request->flash_message = 'インポートしました。';

        // redirect_path指定して自動遷移するため、returnで表示viewの指定不要。
    }

    /**
     * CSVヘッダーチェック
     */
    private function checkCsvHeader($header_columns, $header_column_format)
    {
        if (empty($header_columns)) {
            return array("CSVファイルが空です。");
        }

        // 項目の不足チェック
        $shortness = array_diff($header_column_format, $header_columns);
        if (!empty($shortness)) {
            // Log::debug(var_export($header_column_format, true));
            // Log::debug(var_export($header_columns, true));
            return array("1行目に " . implode(",", $shortness) . " が不足しています。");
        }
        // 項目の不要チェック
        $excess = array_diff($header_columns, $header_column_format);
        if (!empty($excess)) {
            return array("1行目に " . implode(",", $excess) . " は不要です。");
        }

        return array();
    }

    /**
     * CSVデータ行チェック
     */
    private function checkCvslines($fp, $databases_columns)
    {
        $rules = [];
        // $rules = [
        //     0 => [],
        //     1 => ['required'],
        // ];
        $attribute_names = [];

        // エラーチェック配列
        $validator_array = array('column' => array(), 'message' => array());

        foreach ($databases_columns as $col => $databases_column) {
            // $validator_array['column']['databases_columns_value.' . $databases_column->id] = $validator_rule;
            // $validator_array['message']['databases_columns_value.' . $databases_column->id] = $databases_column->column_name;

            // バリデータールールを取得
            $validator_array = $this->getValidatorRule($validator_array, $databases_column);

            // バリデータールールあるか
            // if (array_key_exists('databases_columns_value.' . $databases_column->id, $validator_array['column'])) {
            if (isset($validator_array['column']['databases_columns_value.' . $databases_column->id])) {
                $rules[$col] = $validator_array['column']['databases_columns_value.' . $databases_column->id];
            } else {
                // ルールなしは空配列入れないと、バリデーション項目がずれるのでセット
                $rules[$col] = [];
            }
        }
        // 固定項目エリア
        // 公開日時
        // excelでは 2020-07-01 のハイフンや 2020/07/01 と頭ゼロが付けられないため、インポート時は修正できる日付形式に見直し
        // $rules[$col + 1] = ['required', 'date_format:Y-m-d H:i'];
        $rules[$col + 1] = ['required', 'date_format:Y/n/j H:i'];

        // ヘッダー行が1行目なので、2行目からデータ始まる
        $line_count = 2;
        $errors = [];

        while (($csv_columns = fgetcsv($fp, 0, ",")) !== false) {
            // バリデーション
            $validator = Validator::make($csv_columns, $rules);
            // Log::debug($line_count . '行目の$csv_columns:' . var_export($csv_columns, true));
            // Log::debug(var_export($rules, true));

            // 行数＋項目名
            $attribute_names = [];
            foreach ($databases_columns as $col => $databases_column) {
                $attribute_names[$col] = $line_count . '行目の' . $databases_column->column_name;
            }
            // 固定項目
            $attribute_names[$col + 1] = $line_count . '行目の公開日時';

            $validator->setAttributeNames($attribute_names);
            // Log::debug(var_export($attribute_names, true));

            if ($validator->fails()) {
                $errors = array_merge($errors, $validator->errors()->all());
                // continue;
            }

            $line_count++;
        }

        return $errors;
    }

    /**
     * CSVインポートのフォーマットダウンロード
     */
    public function downloadCsvFormat($request, $page_id, $frame_id, $id)
    {
        // データ出力しない（フォーマットのみ出力）
        $data_output_flag = false;
        return $this->downloadCsv($request, $page_id, $frame_id, $id, $data_output_flag);
    }

    /**
     * 表示設定変更画面の表示
     */
    public function editView($request, $page_id, $frame_id)
    {
        // セッション初期化などのLaravel 処理。
        $request->flash();

        // データベース＆フレームデータ
        $database_frame = $this->getDatabaseFrame($frame_id);

        // フレームデータ
        $view_frame = DatabasesFrames::where('frames_id', $frame_id)->first();
        if (empty($view_frame)) {
            $view_frame = new DatabasesFrames();
        }

        // Frames > Buckets > Database で特定
        if (empty($database_frame->bucket_id)) {
            $database = null;
            $columns = null;
        } else {
            $database = Databases::where('bucket_id', $database_frame->bucket_id)->first();

            // カラムの取得
            $columns = DatabasesColumns::where('databases_id', $database->id)->orderBy('display_sequence', 'asc')->get();
        }

        // 表示テンプレートを呼び出す。
        return $this->view(
            'databases_edit_view', [
            'database_frame' => $database_frame,
            'view_frame'     => $view_frame,
            'database'       => $database,
            'columns'        => $columns,
            ]
        )->withInput($request->all);
    }

    /**
     *  表示設定保存処理
     */
    public function saveView($request, $page_id, $frame_id)
    {
        // セッション初期化などのLaravel 処理。
        $request->flash();

        // デフォルトで必須
        $validator_values['view_count'] = ['required', 'numeric'];
        $validator_attributes['view_count'] = '表示件数';

        // menuテンプレートでのみ使われる項目
        // 半角数字
        $validator_values['view_page_id'] = ['numeric'];
        $validator_attributes['view_page_id'] = '表示するページID';
        $validator_values['view_frame_id'] = ['numeric'];
        $validator_attributes['view_frame_id'] = '表示するフレームID';

        // 項目のエラーチェック
        $validator = Validator::make($request->all(), $validator_values);
        $validator->setAttributeNames($validator_attributes);

        // エラーがあった場合は入力画面に戻る。
        // $message = null;
        if ($validator->fails()) {
            return $this->editView($request, $page_id, $frame_id)->withErrors($validator);
        }

        // 更新後のメッセージ
        //$message = null;

        // データベース＆フレームデータ
        $database_frame = $this->getDatabaseFrame($frame_id);

        // 表示設定の保存
        $databases_frames = DatabasesFrames::updateOrCreate(
            [
                'databases_id'      => $database_frame->databases_id,
                'frames_id'         => $frame_id
            ],
            [
                'databases_id'      => $database_frame->databases_id,
                'frames_id'         => $frame_id,
                'use_search_flag'   => $request->use_search_flag,
                'use_select_flag'   => $request->use_select_flag,
                'use_sort_flag'     => $request->use_sort_flag ? implode(',', $request->use_sort_flag) : null,
                'default_sort_flag' => $request->default_sort_flag,
                'view_count'        => $request->view_count,
                'default_hide'      => $request->default_hide,
                'view_page_id'        => $request->view_page_id,
                'view_frame_id'        => $request->view_frame_id
            ]
        );

        return $this->editView($request, $page_id, $frame_id);
    }

    /**
     *  検索用メソッド
     */
    public static function getSearchArgs($search_keyword, $page_ids = null)
    {
        // Query Builder のバグ？
        // whereIn で指定した引数が展開されずに、引数の変数分だけ、setBindings の引数を要求される。
        // そのため、whereIn とsetBindings 用の変数に同じ $page_ids を設定している。
        $query = DB::table('databases_inputs')
                   ->select(
                       'databases_inputs.id         as post_id',
                       'frames.id                   as frame_id',
                       'frames.page_id              as page_id',
                       'pages.permanent_link        as permanent_link',
                       'databases_inputs.id         as post_title',
                       DB::raw('0 as important'),
                       'databases_inputs.created_at as posted_at',
                       DB::raw('null as posted_name'),
                       DB::raw('null as classname'),
                       DB::raw('null as categories_id'),
                       DB::raw('null as category'),
                       DB::raw('"databases" as plugin_name')
                   )
                   ->join('databases', 'databases.id', '=', 'databases_inputs.databases_id')
                   ->join('frames', 'frames.bucket_id', '=', 'databases.bucket_id')
                   ->join('pages', 'pages.id', '=', 'frames.page_id')
                   ->whereIn('pages.id', $page_ids);

        //$bind = array($page_ids, 0, '%'.$search_keyword.'%', '%'.$search_keyword.'%');
        $bind = array($page_ids);

        $return[] = $query;
        $return[] = $bind;
        $return[] = 'show_page';
        $return[] = '/page';

        return $return;
    }

    /**
     * 登録データ行の取得
     */
    private function getDatabasesInputs($id)
    {
        // 登録データ行の取得
        // $inputs = DatabasesInputs::where('id', $id)->first();
        $inputs = DatabasesInputs::where('id', $id)
                                    ->where(function ($query) {
                                        // 権限によって表示する記事を絞る
                                        $query = $this->appendAuthWhere($query, 'databases_inputs');
                                    })
                                    ->first();

        return $inputs;
    }

    /**
     * 権限によって表示する記事を絞る
     *
     * 基本：
     *   - 承認機能を実装するには指定したテーブル($table_name)に status, created_id カラムがある事
     * オプション：
     *   - テーブルに posted_at(投稿日時) カラムがある場合、投稿日時前＋権限なし or 未ログインなら表示しない
     *
     * status = 0:公開(Active)
     * status = 1:Temporary（一時保存）
     * status = 2:Approval pending（承認待ち）
     * status = 9:History（履歴・データ削除）
     * 参考) https://github.com/opensource-workshop/connect-cms/wiki/Data-history-policy（データ履歴の方針）
     *
     * コンテンツ管理者(role_article_admin): 無条件に全記事見れる
     * モデレータ(role_article):            無条件に全記事見れる
     * 承認者(role_approval):               0:公開(Active) or 2:承認待ち の全記事見れる
     * 編集者(role_reporter):               0:公開(Active) or 自分の作成した記事 見れる
     * 権限なし（コンテンツ管理者・モデレータ・承認者・編集者以外）or 未ログイン:    0:公開(Active) 記事見れる
     *
     * [オプション：posted_at(投稿日時) カラムがある]
     * 権限なし（コンテンツ管理者・モデレータ・承認者・編集者以外）or 未ログイン:    0:公開(Active) and 投稿日時前 記事見れる
     *
     * 例) $table_name = 'blogs_posts';
     * 例) $table_name = 'databases_inputs';
     */
    protected function appendAuthWhere($query, $table_name)
    {
        if (empty($query)) {
            // 空なら何もしない
            return $query;
        }

        // モデレータ(記事修正, role_article)権限
        // コンテンツ管理者(role_article_admin)   = 全記事の取得
        if ($this->isCan('role_article') || $this->isCan('role_article_admin')) {
            // 全件取得のため、追加条件なしで戻る。
            return $query;
        }

        if ($this->isCan('role_approval')) {
            //
            // 承認者(role_approval)権限 = Active ＋ 承認待ちの取得
            //
            // [TODO] status Enum作成したほうがよさそう。コードの意味の全体が把握できないため
            $query->Where($table_name . '.status', '=', 0)
                    ->orWhere($table_name . '.status', '=', 2);
        } elseif ($this->isCan('role_reporter')) {
            //
            // 編集者(role_reporter)権限 = Active ＋ 自分の全ステータス記事の取得
            //
            $query->Where($table_name . '.status', '=', 0)
                    ->orWhere($table_name . '.created_id', '=', Auth::user()->id);
        } else {
            // 権限なし（コンテンツ管理者・モデレータ・承認者・編集者以外）
            // 未ログイン
            $query->where($table_name . '.status', 0);

            // DBカラム posted_at(投稿日時) 存在するか
            if (Schema::hasColumn($table_name, 'posted_at')) {
                $query->where($table_name . '.posted_at', '<=', Carbon::now());
            }
        }

        // var_dump($query->get());
        return $query;
    }

    /**
     * 承認
     */
    public function approval($request, $page_id = null, $frame_id = null, $id = null)
    {
        // 登録データ行の取得
        $databases_inputs = $this->getDatabasesInputs($id);

        // データがあることを確認
        if (empty($databases_inputs)) {
            return;
        }

        // 更新されたら、行レコードの updated_at を更新したいので、update()
        $databases_inputs->updated_at = now();
        $databases_inputs->status = 0;  // 公開
        $databases_inputs->update();

        // 登録後は表示用の初期処理を呼ぶ。
        return $this->index($request, $page_id, $frame_id);
    }

    /**
     * 一時保存
     */
    public function temporarysave($request, $page_id = null, $frame_id = null, $id = null)
    {
        // 一時保存
        $isTemporary = true;
        $this->publicStore($request, $page_id, $frame_id, $id, $isTemporary);
    }

    /* スタティック関数 */

    /**
     *  新着情報用メソッド
     */
    public static function getWhatsnewArgs()
    {
        // 戻り値('sql_method'、link_pattern'、'link_base')
        // 新着側でユニオンしてるため、selectで指定する項目は全必須。プラグイン側にない項目はNULLで返す。

/*
SELECT
    frames.page_id                as page_id,
    frames.id                     as frame_id,
    databases_inputs.id           as post_id,
    databases_input_cols.`value`  as post_title,
    null                          as important,
    databases_inputs.posted_at    as posted_at,
    databases_inputs.created_name as posted_name,
    null                          as classname,
    null                          as category
FROM
    frames,
    `databases`,
    databases_inputs
    LEFT JOIN databases_columns
        ON databases_inputs.databases_id = databases_columns.databases_id
        AND databases_columns.title_flag = 1
    LEFT JOIN databases_input_cols
        ON databases_inputs.id = databases_input_cols.databases_inputs_id
        AND databases_columns.id = databases_input_cols.databases_columns_id
WHERE
    frames.bucket_id = `databases`.bucket_id
AND `databases`.id = databases_inputs.databases_id
AND databases_inputs.status = 0
AND databases_inputs.posted_at <= NOW()
;
*/
        // データ詳細の取得
        $return[] = DatabasesInputs::select(
            'frames.page_id                as page_id',
            'frames.id                     as frame_id',
            'databases_inputs.id           as post_id,',
            'databases_input_cols.value    as post_title,',
            DB::raw('null                  as important'),
            'databases_inputs.posted_at    as posted_at',
            'databases_inputs.created_name as posted_name',
            DB::raw('null                  as classname'),
            DB::raw('null                  as category'),
            DB::raw('"databases"           as plugin_name')
        )
                ->join('databases', 'databases.id', '=', 'databases_inputs.databases_id')
                ->join('frames', 'frames.bucket_id', '=', 'databases.bucket_id')
                ->leftJoin('databases_columns', function ($leftJoin) {
                    $leftJoin->on('databases_inputs.databases_id', '=', 'databases_columns.databases_id')
                                ->where('databases_columns.title_flag', 1);
                })
                ->leftJoin('databases_input_cols', function ($leftJoin) {
                    $leftJoin->on('databases_inputs.id', '=', 'databases_input_cols.databases_inputs_id')
                                ->on('databases_columns.id', '=', 'databases_input_cols.databases_columns_id');
                })
                ->where('databases_inputs.status', 0)
                ->where('databases_inputs.posted_at', '<=', Carbon::now());

        $return[] = 'show_page_frame_post';
        $return[] = '/plugin/databases/detail';

        return $return;
    }
}
