<?php

namespace App\Plugins\User\Databases;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Validation\Rule;

use Carbon\Carbon;

use App\Models\Common\Buckets;
use App\Models\Common\BucketsRoles;
use App\Models\Common\Frame;
use App\Models\Common\Uploads;
use App\Models\User\Databases\Databases;
use App\Models\User\Databases\DatabasesColumns;
use App\Models\User\Databases\DatabasesColumnsSelects;
use App\Models\User\Databases\DatabasesColumnsRole;
use App\Models\User\Databases\DatabasesFrames;
use App\Models\User\Databases\DatabasesInputs;
use App\Models\User\Databases\DatabasesInputCols;
use App\Models\User\Databases\DatabasesRole;

use App\Rules\CustomValiAlphaNumForMultiByte;
use App\Rules\CustomValiCheckWidthForString;
use App\Rules\CustomValiDatesYm;
use App\Rules\CustomValiCsvImage;
use App\Rules\CustomValiCsvExtensions;
use App\Rules\CustomValiWysiwygMax;

use App\Plugins\User\UserPluginBase;

use App\Utilities\Csv\CsvUtils;
use App\Utilities\Zip\UnzipUtils;
use App\Utilities\String\StringUtils;

use App\Enums\Bs4TextColor;
use App\Enums\CsvCharacterCode;
use App\Enums\DatabaseColumnType;
use App\Enums\DatabaseColumnRoleName;
use App\Enums\DatabaseRoleName;
use App\Enums\DatabaseSortFlag;
use App\Enums\Required;
use App\Enums\NoticeEmbeddedTag;
use App\Enums\StatusType;

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
 * @package Controller
 */
class DatabasesPlugin extends UserPluginBase
{
    const CHECKBOX_SEPARATOR = '|';

    /* オブジェクト変数 */

    /**
     * 変更時のPOSTデータ
     */
    public $post = null;

    /* コアから呼び出す関数 */

    /**
     * 追加の関数定義（コアから呼び出す）
     */
    public function getPublicFunctions()
    {
        // 標準関数以外で画面などから呼ばれる関数の定義
        $functions = array();
        $functions['get']  = [
            'detail',
            'input',
            'search',
        ];
        $functions['post'] = [
            'index',
            'detail',
            'input',
            'cancel',
            'addPref',
            'search',
        ];
        return $functions;
    }

    /**
     * メール送信で使用するメソッド
     */
    public function useBucketMailMethods()
    {
        return ['notice', 'approval', 'approved'];
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
        $role_check_table = array();
        $role_check_table["input"]                = array('posts.create', 'posts.update');
        $role_check_table["publicConfirm"]        = array('posts.create', 'posts.update');
        $role_check_table["publicStore"]          = array('posts.create', 'posts.update');

        $role_check_table["addPref"]              = array('buckets.addColumn');
        return $role_check_table;
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

    /**
     *  POST取得関数（コアから呼び出す）
     *  コアがPOSTチェックの際に呼び出す関数
     */
    public function getPost($id, $action = null)
    {
        if (is_null($action)) {
            // プラグイン内からの呼び出しを想定。処理を通す。
        } elseif (in_array($action, ['input', 'publicConfirm', 'publicStore', 'temporarysave', 'delete'])) {
            // コアから呼び出し。posts.update|posts.deleteの権限チェックを指定したアクションは、処理を通す。
        } else {
            // それ以外のアクションは null で返す。
            return null;
        }

        // 一度読んでいれば、そのPOSTを再利用する。
        if (!empty($this->post)) {
            return $this->post;
        }

        // 登録データ行の取得
        $this->post = DatabasesInputs::
            where(function ($query) {
                // 権限によって表示する記事を絞る
                $query = $this->appendAuthWhereBase($query, 'databases_inputs');
            })
            ->firstOrNew(['id' => $id]);

        return $this->post;
    }

    /* private関数 */

    /**
     *  データ取得
     */
    private function getDatabases($frame_id)
    {
        // Databases、Frame データ
        $database = Databases::select('databases.*')
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
            if ($database->user_mail_send_flag == '1' && empty($databases_columns->where('column_type', DatabaseColumnType::mail)->first())) {
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
     * 紐づくデータベースID とフレームデータの取得
     */
    private function getDatabaseFrame($frame_id)
    {
        // Frame データ
        $frame = Frame::
            select(
                'frames.*',
                'databases.id as databases_id',
                'databases_frames.id as databases_frames_id',
                'databases.databases_name',
                'databases.search_results_empty_message',
                'use_search_flag',
                'placeholder_search',
                'use_select_flag',
                'use_sort_flag',
                'view_count',
                'default_hide'
                // 'view_page_id',
                // 'view_frame_id'
            )
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
        $input_cols = DatabasesInputCols::
            select(
                'databases_input_cols.*',
                'databases_columns.column_type',
                'databases_columns.column_name',
                'databases_columns.classname',
                'databases_columns.role_display_control_flag',
                'databases_columns.databases_id',
                'databases_columns.title_flag',
                'uploads.client_original_name'
            )
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
                                     ->whereIn('databases_columns.column_type', [DatabaseColumnType::file, DatabaseColumnType::image, DatabaseColumnType::video])
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
    public function index($request, $page_id, $frame_id)
    {
        // delete: 同ページに(a)データベースプラグイン,(b)フォームを配置して(b)フォームで入力エラーが起きても、入力値が復元しないバグ対応。
        //   (b)で登録処理が動いても, 同ページの(a)データベースのindex()が動き、この $request->flash() でセッション消すのが原因。
        // セッション初期化などのLaravel 処理。
        // $request->flash();

        // リクエストにページが渡ってきたら、セッションに保持しておく。（詳細や更新後に元のページに戻るため）
        $frame_page = "frame_{$frame_id}_page";
        if ($request->has($frame_page)) {
                $request->session()->put('page_no.'.$frame_id, $request->$frame_page);
        } else {
            // 指定がなければセッションから削除
            $request->session()->forget('page_no.'.$frame_id);
        }

        // Databases、Frame データ
        $database = $this->getDatabases($frame_id);
        // Log::debug(var_export($database, true));
        // -> DB->first();でデータなしの場合、NULLが返る
        // -> [2020-09-02 18:05:43] local.DEBUG: NULL


        $setting_error_messages = null;
        $databases_columns = null;
        // $databases_columns_id_select = null;
        if ($database) {
            // $databases_columns_id_select = $this->getDatabasesColumnsSelects($database->id);

            /**
             * データベースのカラムデータを取得
             * ※データベース設定で「登録者にメール送信あり」設定にも関わらず、項目内にメールアドレス型が存在しない場合はリテラル「mail_setting_error」が返る
             */
            $databases_columns = $this->getDatabasesColumns($database);
            // Log::debug(var_export($databases_columns, true));
            // -> DB->get();でデータなしの場合、Collectionクラスが返る
            // ->
            // [2020-09-02 18:02:46] local.DEBUG: Illuminate\Database\Eloquent\Collection::__set_state(array(
            //    'items' =>
            //    array (
            //    ),
            //  ))

            if ($databases_columns == 'mail_setting_error') {
                // memo: フォームの名残。データベース設定画面に「登録者にメール送信する」項目はないため、ここに入る事をはない想定。
                // データベース設定で「登録者にメール送信あり」設定にも関わらず、項目内にメールアドレス型が存在しない場合
                $setting_error_messages[] = 'メールアドレス型の項目を設定してください。（データベースの設定「登録者にメール送信する」と関連）';
            // bugfix: DB->get();でデータなしの場合、Collectionクラスが返るため、CollectionクラスのisEmpty()を使う
            // } elseif (!$databases_columns) {
            } elseif ($databases_columns->isEmpty()) {
                // 項目データがない場合
                $setting_error_messages[] = 'フレームの設定画面から、項目データを作成してください。';
            }
        } else {
            // フレームに紐づくデータベース親データがない場合
            $setting_error_messages[] = 'フレームの設定画面から、使用するデータベースを選択するか、作成してください。';
        }


        //--- 初期表示データ

        if (empty($database)) {
            // $databases = null;
            $columns = null;
            $select_columns = null;
            $sort_columns = null;
            $sort_count = 0;
            $group_rows_cols_columns = null;
            $inputs = null;
            $input_cols = null;
        } else {
            // データベースの取得
            // $databases = Databases::where('id', $database->id)->first();

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

            // debug:確認したいSQLの前にこれを仕込んで
            // DB::enableQueryLog();

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
            $inputs_query = $this->appendAuthWhereBase($inputs_query, 'databases_inputs');

            // 権限によって非表示columのdatabases_columns_id配列を取得する
            $hide_columns_ids = (new DatabasesTool())->getHideColumnsIds($columns, 'list_detail_display_flag');

            $databases_columns_ids = [];
            foreach ($databases_columns as $databases_column) {
                $databases_columns_ids[] = $databases_column->id;
            }

            // キーワード指定の追加
            // 絞り込み制御ON、絞り込み検索キーワードあり
            if (!empty($databases_frames->use_filter_flag) && !empty($databases_frames->filter_search_keyword)) {
                $inputs_query = DatabasesTool::appendSearchKeyword('databases_inputs.id', $inputs_query, $databases_columns_ids, $hide_columns_ids, $databases_frames->filter_search_keyword);
            }
            // 画面のキーワード指定
            if (!empty(session('search_keyword.'.$frame_id))) {
                $inputs_query = DatabasesTool::appendSearchKeyword('databases_inputs.id', $inputs_query, $databases_columns_ids, $hide_columns_ids, session('search_keyword.'.$frame_id));
            }

            // データベースプラグイン単体では $request->search_options をセットしておらず「オプション検索指定」使っていないが、個別の追加テンプレートで利用するため、消さない。
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

            // カスタムテンプレート用
            // 項目名|検索区分|値　→　項目名（id）でマージしてor検索
            if (!empty(session('search_options_or.'.$frame_id))) {
                $search_options_or = session('search_options_or.'.$frame_id);
                $merge_search_options = [];
                foreach ($search_options_or as $search_option) {
                    list($colname, $reg_txt, $val) = explode('|', $search_option);
                    if (count(explode('|', $search_option)) != 3) {
                        continue;  // 指定が正しくなければ飛ばす
                    }
                    $option_search_column_obj = $columns->where('column_name', $colname);
                    if (empty($option_search_column_obj)) {
                        continue;  // 指定が正しくなければ飛ばす
                    }
                    $option_search_column = $option_search_column_obj->first();
                    if (empty($option_search_column)) {
                        continue;  // 指定が正しくなければ飛ばす
                    }
                    if (empty($reg_txt) || !in_array($reg_txt, ['ALL', 'PART', 'FRONT', 'REAR', 'GT', 'LT', 'GE', 'LE'])) {
                        continue;  // 指定が正しくなければ飛ばす
                    }
                    if (empty($val)) {
                        // 0 を検索したい場合もあるので追加
                        if ($val !== "0") {
                            continue;  // 指定が正しくなければ飛ばす
                        }
                    }
                    // カラムIDでマージする
                    $merge_search_options[$option_search_column->id][] = [
                        'reg_txt' => $reg_txt,
                        'val' => $val,
                    ];
                }

                foreach ($merge_search_options as $col_id => $search_vals) {
                    // 検索方法
                    $inputs_query->whereIn('databases_inputs.id', function ($query) use ($col_id, $search_vals) {
                        $query->select('databases_inputs_id')
                        ->from('databases_input_cols')
                        ->join('databases_columns', 'databases_columns.id', '=', 'databases_input_cols.databases_columns_id')
                        ->where('databases_input_cols.databases_columns_id', $col_id);

                        $query->where(function ($query) use ($search_vals) {
                            foreach ($search_vals as $vals) {
                                $reg_txt = $vals['reg_txt'];
                                $val = $vals['val'];
                                if ($reg_txt == 'ALL') {
                                    $query->orwhere('value', $val);
                                } elseif ($reg_txt == 'PART') {
                                    $query->orwhere('value', 'like', '%' . $val . '%');
                                }
                            }
                        });
                        $query->groupBy('databases_inputs_id');
                    });
                }
            }

            // カスタムテンプレート用
            // 期間検索　［yyyymm(dd)|yyyymm(dd)...］で入力されているデータを検索
            // 検索対象の項目型は複数年月型（テキスト入力）が推奨だが、期間外データを入力する場合は1行文字列型でも可能
            if (!empty(session('search_term.'.$frame_id))) {
                $search_term = session('search_term.'.$frame_id);
                if (isset($search_term['column_name'])) {
                    $colname = $search_term['column_name'];
                    $tmp_request_search_term = $search_term;
                    $search_term_column_obj = $columns->where('column_name', $colname);
                    if (!empty($search_term_column_obj)) {
                        $search_term_column = $search_term_column_obj->first();
                        if (!empty($search_term_column)) {
                            $col_id = $search_term_column->id;
                            unset($tmp_request_search_term['column_name']);
                            $term_month = 12;
                            if (isset($search_term['term_month'])) {
                                $term_month = (int)$search_term['term_month'];
                                unset($tmp_request_search_term['term_month']);
                            }
                            // datepickerで入力された場合にはyyyy/MMでくるので置換する
                            $search_vals = [];
                            $term_value_from = "";
                            $term_value_to = "";
                            foreach ($tmp_request_search_term as $key => $val) {
                                $search_vals[$key] = str_replace("/", "", $val);
                                if ($key == "term_value_from") {
                                    $term_value_from = $val;
                                }
                                if ($key == "term_value_to") {
                                    $term_value_to = $val;
                                }
                            }
                            $add_const_word_search_flg = false;
                            if (!empty($term_value_from) && empty($term_value_to)) {
                                //検索前が入力　後が未入力
                                $target_day = date("Y-m-1", strtotime($term_value_from. "/01"));
                                for ($i = 0; $i < $term_month; $i++) {
                                    // $term_value_to に12ヶ月分入れる
                                    $month = date("Ym", strtotime($target_day . "+$i month"));
                                    $search_vals["term_value_to_".$i] = $month;
                                    unset($search_vals["term_value_from"]);
                                    unset($search_vals["term_value_to"]);
                                }
                                $add_const_word_search_flg = true;
                            } elseif (empty($term_value_from) && !empty($term_value_to)) {
                                //検索前が未入力　後が入力
                                $target_day = date("Y-m-1", strtotime($term_value_from. "/01"));
                                for ($i = 0; $i < $term_month; $i++) {
                                    // $term_value_from に前12ヶ月分入れる
                                            $month = date("Ym", strtotime($target_day . "-$i month"));
                                    $search_vals["term_value_from_".$i] = $month;
                                    unset($search_vals["term_value_from"]);
                                    unset($search_vals["term_value_to"]);
                                }
                                $add_const_word_search_flg = true;
                            } elseif (!empty($term_value_from) && !empty($term_value_to)) {
                                //検索前入力、後が入力
                                $strtime_term_value_from = strtotime($term_value_from. "/01");
                                $strtime_term_value_to = strtotime($term_value_to. "/01");
                                // FROM TO が逆の場合は入れ替える
                                if ($strtime_term_value_from > $strtime_term_value_to) {
                                    $tmp_strtime_term_value_from = $strtime_term_value_from;
                                    $tmp_strtime_term_value_to = $strtime_term_value_to;
                                    $strtime_term_value_to = $tmp_strtime_term_value_from;
                                    $strtime_term_value_from = $tmp_strtime_term_value_to;
                                }
                                $i = 0;
                                unset($search_vals["term_value_from"]);
                                unset($search_vals["term_value_to"]);
                                $search_vals["term_value_".$i] = date("Ym", $strtime_term_value_from);
                                while (($strtime_term_value_from = strtotime("+1 MONTH", $strtime_term_value_from)) <= $strtime_term_value_to) {
                                    $i++;
                                    $search_vals["term_value_".$i] = date("Ym", $strtime_term_value_from);
                                }
                                $add_const_word_search_flg = true;
                            }

                            // テンプレートでsearch_term[XXXX]をセットすることで、ORの値を任意に増やすことができる（*や通年）等期間外のデータ
                            if ($add_const_word_search_flg) {
                                $inputs_query->whereIn('databases_inputs.id', function ($query) use ($col_id, $search_vals) {
                                    $query->select('databases_inputs_id')
                                    ->from('databases_input_cols')
                                    ->join('databases_columns', 'databases_columns.id', '=', 'databases_input_cols.databases_columns_id')
                                    ->where('databases_input_cols.databases_columns_id', $col_id);
                                    $query->where(function ($query) use ($search_vals) {
                                        foreach ($search_vals as $val) {
                                            $query->orwhere('value', 'like', '%' . $val . '%');
                                        }
                                    });
                                    $query->groupBy('databases_inputs_id');
                                });
                            }
                        }
                    }
                }
            }

            // 絞り込み指定の追加
            // 絞り込み制御ON、絞り込み指定あり
            if (!empty($databases_frames->use_filter_flag) && !empty($databases_frames->filter_search_columns)) {
                $inputs_query = DatabasesTool::appendSearchColumns('databases_inputs.id', $inputs_query, json_decode($databases_frames->filter_search_columns, true));
            }
            // 画面の絞り込み指定
            if (!empty(session('search_column.'.$frame_id))) {
                $inputs_query = DatabasesTool::appendSearchColumns('databases_inputs.id', $inputs_query, session('search_column.'.$frame_id));
            }

            // 並べ替え指定があれば、並べ替えする項目をSELECT する。
            if ($sort_column_id == DatabaseSortFlag::random && $sort_column_order == DatabaseSortFlag::order_session) {
                $inputs_query->inRandomOrder(session('sort_seed.'.$frame_id));
            } elseif ($sort_column_id == DatabaseSortFlag::random && $sort_column_order == DatabaseSortFlag::order_every) {
                $inputs_query->inRandomOrder();
            } elseif ($sort_column_id == DatabaseSortFlag::created && $sort_column_order == DatabaseSortFlag::order_asc) {
                $inputs_query->orderBy('databases_inputs.created_at', 'asc');
            } elseif ($sort_column_id == DatabaseSortFlag::created && $sort_column_order == DatabaseSortFlag::order_desc) {
                $inputs_query->orderBy('databases_inputs.created_at', 'desc');
            } elseif ($sort_column_id == DatabaseSortFlag::updated && $sort_column_order == DatabaseSortFlag::order_asc) {
                $inputs_query->orderBy('databases_inputs.updated_at', 'asc');
            } elseif ($sort_column_id == DatabaseSortFlag::updated && $sort_column_order == DatabaseSortFlag::order_desc) {
                $inputs_query->orderBy('databases_inputs.updated_at', 'desc');
            } elseif ($sort_column_id == DatabaseSortFlag::display && $sort_column_order == DatabaseSortFlag::order_asc) {
                $inputs_query->orderBy('databases_inputs.display_sequence', 'asc');
            } elseif ($sort_column_id == DatabaseSortFlag::display && $sort_column_order == DatabaseSortFlag::order_desc) {
                $inputs_query->orderBy('databases_inputs.display_sequence', 'desc');
            } elseif ($sort_column_id == DatabaseSortFlag::posted && $sort_column_order == DatabaseSortFlag::order_asc) {
                $inputs_query->orderBy('databases_inputs.posted_at', 'asc');
            } elseif ($sort_column_id == DatabaseSortFlag::posted && $sort_column_order == DatabaseSortFlag::order_desc) {
                $inputs_query->orderBy('databases_inputs.posted_at', 'desc');
            } elseif ($sort_column_id && ctype_digit($sort_column_id) && $sort_column_order == DatabaseSortFlag::order_asc) {
                $inputs_query->orderBy('databases_input_cols.value', 'asc');
            } elseif ($sort_column_id && ctype_digit($sort_column_id) && $sort_column_order == DatabaseSortFlag::order_desc) {
                $inputs_query->orderBy('databases_input_cols.value', 'desc');
            }
            $inputs_query->orderBy('databases_inputs.id', 'asc');

            // データ取得
            $get_count = 10;
            if ($databases_frames) {
                $get_count = $databases_frames->view_count;
            }
            $inputs = $inputs_query->paginate($get_count, ["*"], "frame_{$frame_id}_page");

            // 登録データ行のタイトル取得
            $inputs_titles = DatabasesInputs::
                    select(
                        'databases_inputs.id',
                        'databases_input_cols.value as title'
                    )
                    ->whereIn('databases_inputs.id', $inputs->pluck('id'))
                    ->leftJoin('databases_columns', function ($leftJoin) use ($hide_columns_ids) {
                        $leftJoin->on('databases_inputs.databases_id', '=', 'databases_columns.databases_id')
                                    ->where('databases_columns.title_flag', 1)
                                    // タイトル指定しても、権限によって非表示columだったらvalue表示しない（基本的に、タイトル指定したけど権限で非表示は、設定ミスと思う。その時は(無題)で表示される）
                                    ->whereNotIn('databases_columns.id', $hide_columns_ids);
                    })
                    ->leftJoin('databases_input_cols', function ($leftJoin) {
                        $leftJoin->on('databases_inputs.id', '=', 'databases_input_cols.databases_inputs_id')
                                    ->on('databases_columns.id', '=', 'databases_input_cols.databases_columns_id');
                    })
                    ->get();

            foreach ($inputs as &$input) {
                $inputs_title = $inputs_titles->where('id', $input->id)->first();
                $input->title = isset($inputs_title) ? $inputs_title->title : null;
            }

            // <--- 登録データ行の取得

            // debug: sql dumpする
            // Log::debug(var_export(DB::getQueryLog(), true));

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
        return $this->view('databases', [
            // 'database' => $database,
            // 'databases_columns' => $databases_columns,
            // 'databases_columns_id_select' => $databases_columns_id_select,
            // 'errors' => $errors,
            'setting_error_messages' => $setting_error_messages,

            // 'databases'        => $databases,
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
        // change: 同ページに(a)データベースプラグイン,(b)フォームを配置して(b)フォームで入力エラーが起きても、入力値が復元しないバグ対応。
        // ])->withInput($request->all);
        ]);
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
        $hide_columns_ids = (new DatabasesTool())->getHideColumnsIds($databases_columns, 'list_detail_display_flag');

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
        $hide_columns_ids = (new DatabasesTool())->getHideColumnsIds($databases_columns, 'regist_edit_display_flag');

        // 表示しないcolumnは、group_rows_cols_columnsに含まない。
        $disp_databases_columns = $databases_columns->whereNotIn('id', $hide_columns_ids);

        return $disp_databases_columns;
    }

    /**
     *  データ検索関数
     *  コアがページ表示の際に呼び出す関数
     */
    public function search($request, $page_id, $frame_id)
    {
        // POST されたときは、新しい絞り込み条件が設定された。ということになるので、セッションの書き換え
        if ($request->isMethod('post')) {
            // 検索ON
            session(['is_search.'.$frame_id => 1]);

            // キーワード
            session(['search_keyword.'.$frame_id => $request->search_keyword]);

            // 絞り込み
            session(['search_column.'.$frame_id => $request->search_column]);

            // オプション検索
            session(['search_options.'.$frame_id => $request->search_options]);

            // オプション検索OR
            session(['search_options_or.'.$frame_id => $request->search_options_or]);

            // オプション検索期間
            session(['search_term.'.$frame_id => $request->search_term]);

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
            // var_dump($sort_column_parts);

            // 検索条件を削除
            if ($request->has('clear')) {
                session(['is_search.'.$frame_id => '']);
                session(['search_keyword.'.$frame_id => '']);
                session(['search_column.'.$frame_id => '']);
                session(['search_options.'.$frame_id => '']);
                session(['search_options_or.'.$frame_id => '']);
                session(['search_term.'.$frame_id => '']);
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
        $inputs = $this->getDatabasesInputs($id);

        // データがあることを確認
        if (empty($inputs->id)) {
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
     * 新規記事画面
     */
    public function input($request, $page_id, $frame_id, $id = null, $errors = null)
    {
        // セッション初期化などのLaravel 処理。
        $request->flash();

        // Databases、Frame データ
        $database = $this->getDatabases($frame_id);

        // 権限のよって固定項目"表示順"を非表示にするか
        $is_hide_posted = (new DatabasesTool())->isHidePosted($database);

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
            if (empty($inputs->id)) {
                return;
            }

            // データ詳細の取得
            $input_cols = $this->getDatabasesInputCols($id);
        }

        // 表示テンプレートを呼び出す。
        return $this->view('databases_input', [
            'request'  => $request,
            'frame_id' => $frame_id,
            'id'       => $id,
            'database' => $database,
            'databases_columns' => $databases_columns,
            'databases_columns_id_select' => $databases_columns_id_select,
            'input_cols'  => $input_cols,
            'inputs'      => $inputs,
            'is_hide_posted' => $is_hide_posted,
            'errors'      => $errors,
        ]);
    }

    /**
     * セットすべきバリデータールールが存在する場合、受け取った配列にセットして返す
     *
     * @param [array] $validator_array 二次元配列
     * @param [App\Models\User\Databases\DatabasesColumns] $databases_column
     * @return array
     */
    private function getValidatorRule($validator_array, $databases_column)
    {
        // 入力しないカラム型は、バリデータチェックしない
        if (DatabasesColumns::isNotInputColumnType($databases_column->column_type)) {
            return $validator_array;
        }

        $validator_rule = null;
        // 必須チェック
        if ($databases_column->required) {
            $validator_rule[] = 'required';
        }
        // メールアドレスチェック
        if ($databases_column->column_type == DatabaseColumnType::mail) {
            $validator_rule[] = 'email';
            if ($databases_column->required == 0) {
                $validator_rule[] = 'nullable';
            }
        }
        // 数値チェック
        if ($databases_column->rule_allowed_numeric) {
            $validator_rule[] = 'nullable';
            $validator_rule[] = 'numeric';
        }
        // 英数値チェック
        if ($databases_column->rule_allowed_alpha_numeric) {
            $validator_rule[] = 'nullable';
            $validator_rule[] = new CustomValiAlphaNumForMultiByte();
        }
        // 最大文字数チェック
        if ($databases_column->rule_word_count) {
            $validator_rule[] = new CustomValiCheckWidthForString($databases_column->column_name, $databases_column->rule_word_count);
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
            $comparison_date = Carbon::now()->addDay($databases_column->rule_date_after_equal)->databaseat('Y/m/d');
            $validator_rule[] = 'after_or_equal:' . $comparison_date;
        }
        // 日付チェック
        if ($databases_column->column_type == DatabaseColumnType::date) {
            $validator_rule[] = 'nullable';
            $validator_rule[] = 'date';
        }
        // 複数年月型（テキスト入力）チェック
        if ($databases_column->column_type == DatabaseColumnType::dates_ym) {
            $validator_rule[] = 'nullable';
            $validator_rule[] = new CustomValiDatesYm();
        }
        // 時間チェック
        if ($databases_column->column_type == DatabaseColumnType::time) {
            $validator_rule[] = 'nullable';
            $validator_rule[] = 'date_format:H:i';
        }
        // 画像チェック
        if ($databases_column->column_type == DatabaseColumnType::image) {
            $validator_rule[] = 'nullable';
            $validator_rule[] = 'image';
        }
        // 動画チェック
        if ($databases_column->column_type == DatabaseColumnType::video) {
            $validator_rule[] = 'nullable';
            $validator_rule[] = 'mimes:mp4';
        }
        // wysiwygチェック
        if ($databases_column->column_type == DatabaseColumnType::wysiwyg) {
            $validator_rule[] = 'nullable';
            $validator_rule[] = new CustomValiWysiwygMax();
        }
        // 単一選択チェック
        // 複数選択チェック
        // リストボックスチェック
        if ($databases_column->column_type == DatabaseColumnType::radio ||
                $databases_column->column_type == DatabaseColumnType::checkbox ||
                $databases_column->column_type == DatabaseColumnType::select) {
            // カラムの選択肢用データ
            $selects = DatabasesColumnsSelects::where('databases_columns_id', $databases_column->id)
                                            ->orderBy('databases_columns_id', 'asc')
                                            ->orderBy('display_sequence', 'asc')
                                            ->pluck('value')
                                            ->toArray();

            // 単一選択チェック
            if ($databases_column->column_type == DatabaseColumnType::radio) {
                $validator_rule[] = 'nullable';
                // Rule::inのみで、selectsの中の１つが入ってるかチェック
                $validator_rule[] = Rule::in($selects);
            }
            // 複数選択チェック
            if ($databases_column->column_type == DatabaseColumnType::checkbox) {
                $validator_rule[] = 'nullable';
                // array & Rule::in で、selectsの中の値に存在しているかチェック
                $validator_rule[] = 'array';
                $validator_rule[] = Rule::in($selects);
            }
            // リストボックスチェック
            if ($databases_column->column_type == DatabaseColumnType::select) {
                $validator_rule[] = 'nullable';
                // Rule::inのみで、selectsの中の１つが入ってるかチェック
                $validator_rule[] = Rule::in($selects);
            }
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

        // 権限のよって固定項目"表示順"を非表示にするか
        $is_hide_posted = (new DatabasesTool())->isHidePosted($database);

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
            $validator_array = $this->getValidatorRule($validator_array, $databases_column);
        }

        // 固定項目エリア
        $validator_array['column']['posted_at'] = ['required', 'date_format:Y-m-d H:i'];
        $validator_array['column']['display_sequence'] = ['nullable', 'numeric'];
        $validator_array['message']['posted_at'] = '公開日時';
        $validator_array['message']['display_sequence'] = '表示順';

        // --- 入力値変換
        // 入力値をトリム
        // bugfix: $request->all()を取得して全て$request->merge()すると、「Serialization of 'Illuminate\Http\UploadedFile' is not allowed」エラーが発生する時がある。
        // Illuminate\Session\Store.phpでセッションのserialize()を行っており、oldセッションにUploadオブジェクトが混ざるとシリアライズできずにエラーになっていた。
        // $request->all()で全てトリムする必要はなく、アップロードファイル以外の必要な入力値のみトリムするよう見直す。
        //$request->merge(StringUtils::trimInput($request->all()));

        foreach ($databases_columns as $databases_column) {
            // ファイルタイプ以外の入力値をトリム
            if (! DatabasesColumns::isFileColumnType($databases_column->column_type)) {
                if (isset($request->databases_columns_value[$databases_column->id])) {
                    // 一度配列にして、trim後、また文字列に戻す。
                    $tmp_columns_value = StringUtils::trimInput($request->databases_columns_value[$databases_column->id]);

                    $tmp_array = $request->databases_columns_value;
                    $tmp_array[$databases_column->id] = $tmp_columns_value;
                    $request->merge([
                        "databases_columns_value" => $tmp_array,
                    ]);
                }
            }

            // 数値チェック
            if ($databases_column->rule_allowed_numeric) {
                // 入力値があった場合（マイナスを意図した入力記号はすべて半角に置換する）＆ 全角→半角へ丸める
                $tmp_numeric_columns_value = StringUtils::convertNumericAndMinusZenkakuToHankaku($request->databases_columns_value[$databases_column->id]);

                $tmp_array = $request->databases_columns_value;
                $tmp_array[$databases_column->id] = $tmp_numeric_columns_value;
                $request->merge([
                    "databases_columns_value" => $tmp_array,
                ]);
            }
            // 複数年月型
            if ($databases_column->column_type == DatabaseColumnType::dates_ym) {
                // 一度配列にして、trim後、また文字列に戻す。
                $tmp_columns_value = StringUtils::trimInputKanma($request->databases_columns_value[$databases_column->id]);

                $tmp_array = $request->databases_columns_value;
                $tmp_array[$databases_column->id] = $tmp_columns_value;
                $request->merge([
                    "databases_columns_value" => $tmp_array,
                ]);
            }
            // wysiwyg型
            if ($databases_column->column_type == DatabaseColumnType::wysiwyg) {
                // XSS対応のJavaScript等の制限
                $tmp_columns_value = $this->clean($request->databases_columns_value[$databases_column->id]);

                $tmp_array = $request->databases_columns_value;
                $tmp_array[$databases_column->id] = $tmp_columns_value;
                $request->merge([
                    "databases_columns_value" => $tmp_array,
                ]);
            }
        }

        $request->merge([
            // 表示順:  全角→半角変換
            "display_sequence" => StringUtils::convertNumericAndMinusZenkakuToHankaku($request->display_sequence),
        ]);

        // 項目のエラーチェック
        $validator = Validator::make($request->all(), $validator_array['column']);
        $validator->setAttributeNames($validator_array['message']);
        // Log::debug(var_export($request->all(), true));
        // Log::debug(var_export($validator_array, true));

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
            if (DatabasesColumns::isFileColumnType($databases_column->column_type)) {
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
                        'plugin_name'          => 'databases',
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
        return $this->view('databases_confirm', [
            'request'                  => $request,
            'frame_id'                 => $frame_id,
            'id'                       => $id,
            'database'                 => $database,
            'databases_columns'        => $databases_columns,
            'uploads'                  => $uploads,
            'delete_upload_column_ids' => $delete_upload_column_ids,
            'is_hide_posted'           => $is_hide_posted,
        ]);
    }

    /**
     * データ登録
     */
    public function publicStore($request, $page_id, $frame_id, $id = null, $isTemporary = false)
    {
        // Databases、Frame データ
        $database = $this->getDatabases($frame_id);

        if ($isTemporary) {
            $status = StatusType::temporary;  // 一時保存
        } else {
            // 承認の要否確認とステータス処理
            if ($this->isApproval()) {
                $status = StatusType::approval_pending;  // 承認待ち
            } else {
                $status = StatusType::active;  // 公開
            }
        }

        // 表示順が空なら、自分を省いた最後の番号+1 をセット
        $display_sequence = $this->getSaveDisplaySequence($request->display_sequence, $database->id, $id);

        // 変更の場合（行 idが渡ってきたら）、既存の行データを使用。新規の場合は行レコード取得
        if (empty($id)) {
            $databases_inputs = new DatabasesInputs();

            // 新規登録の判定のために、保存する前のレコードを退避しておく。
            $before_databases_inputs = clone $databases_inputs;

            // 値の保存
            $databases_inputs->databases_id = $database->id;
            $databases_inputs->status = $status;
            $databases_inputs->display_sequence = $display_sequence;
            $databases_inputs->posted_at = $request->posted_at . ':00';
            $databases_inputs->save();
        } else {
            $databases_inputs = DatabasesInputs::where('id', $id)->first();

            // 新規登録の判定のために、保存する前のレコードを退避しておく。
            $before_databases_inputs = clone $databases_inputs;

            // 更新されたら、行レコードの updated_at を更新したいので、update()
            $databases_inputs->updated_at = now();
            $databases_inputs->status = $status;
            $databases_inputs->display_sequence = $display_sequence;
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

                            // uploadの削除
                            $delete_upload->delete();
                        }
                    }
                }
            }
        }

        // データベースのカラムデータ
        $databases_columns = DatabasesColumns::where('databases_id', $database->id)->orderBy('display_sequence')->get();

        // 権限のよって登録・編集の非表示columのdatabases_columns_id配列を取得する
        $hide_columns_ids = (new DatabasesTool())->getHideColumnsIds($databases_columns, 'regist_edit_display_flag');
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

        // databases_input_cols 登録
        foreach ($databases_columns as $databases_column) {
            // 登録日型・更新日型・公開日型・表示順は、databases_inputsテーブルの登録日・更新日・公開日・表示順を利用するため、登録しない
            if (DatabasesColumns::isNotInputColumnType($databases_column->column_type)) {
                continue;
            }

            $value = "";
            if (is_array($request->databases_columns_value[$databases_column->id])) {
                $value = implode(self::CHECKBOX_SEPARATOR, $request->databases_columns_value[$databases_column->id]);
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
                if (DatabasesColumns::isFileColumnType($databases_column->column_type)) {
                    $uploads_count = Uploads::where('id', $value)->update(['temporary_flag' => 0]);
                }
            }
        }

        // titleカラムが無いため、プラグイン独自でセット
        $overwrite_notice_embedded_tags = [NoticeEmbeddedTag::title => $this->getTitle($databases_inputs)];

        // メール送信 引数(レコードを表すモデルオブジェクト, 保存前のレコード, 詳細表示メソッド, 上書き埋め込みタグ)
        $this->sendPostNotice($databases_inputs, $before_databases_inputs, 'detail', $overwrite_notice_embedded_tags);

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
     * 登録する表示順を取得
     */
    private function getSaveDisplaySequence($display_sequence, $databases_id, $databases_inputs_id)
    {
        // 表示順が空なら、自分を省いた最後の番号+1 をセット
        if (!is_null($display_sequence)) {
            $display_sequence = intval($display_sequence);
        } else {
            $max_display_sequence = DatabasesInputs::where('databases_id', $databases_id)->where('id', '<>', $databases_inputs_id)->max('display_sequence');
            $display_sequence = empty($max_display_sequence) ? 1 : $max_display_sequence + 1;
        }
        return $display_sequence;
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

        // ファイル型の調査のため、詳細カラムデータを取得（削除通知でも使用）
        $input_cols = $this->getDatabasesInputCols($id);

        // ファイル型のファイル、uploads テーブルを削除
        foreach ($input_cols as $input_col) {
            if (DatabasesColumns::isFileColumnType($input_col->column_type)) {
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

        // メール送信のために、削除する前に行レコードを退避しておく。
        $deleted_input = DatabasesInputs::firstOrNew(['id' => $id]);
        $deleted_title = $this->getTitle($deleted_input);

        // 詳細カラムデータを削除
        DatabasesInputCols::where('databases_inputs_id', $id)->delete();

        // 行データを削除
        DatabasesInputs::where('id', $id)->delete();

        // 削除通知に渡すために、項目の編集（最初の公開（権限で制御しない）の項目名と値）
        $notice_cols = $input_cols->where("role_display_control_flag", 0);
        $delete_comment = "";
        $overwrite_notice_embedded_tags = [];
        if ($notice_cols->isNotEmpty()) {
            $notice_cols_first = $notice_cols->first();
            $delete_comment  = "以下、削除されたデータの最初の公開項目です。\n";
            $delete_comment .= "「" . $notice_cols_first->column_name . "：" . $notice_cols_first->value . "」の行を削除しました。";

            // titleカラムが無いため、プラグイン独自でセット
            $overwrite_notice_embedded_tags = [NoticeEmbeddedTag::title => $deleted_title];
        }

        // メール送信 引数(削除した行, 詳細表示メソッド, 削除データを表すメッセージ, 上書き埋め込みタグ)
        $this->sendDeleteNotice($deleted_input, 'detail', $delete_comment, $overwrite_notice_embedded_tags);

        // 表示テンプレートを呼び出す。
        return $this->index($request, $page_id, $frame_id);
    }

    /**
     * タイトル取得
     *
     * [TODO] このメソッドの不具合ではないが、新着等のタイトル取得は不十分になってる臭い。
     *        おそらく input_cols のタイトル取得のみに対応していて、input_cols にデータがない 登録日型 や、
     *        input_cols にデータがあってもファイル型のような、input_cols->value には ファイルID のみ格納していて、別途 client_original_name 等を取得するものは対応してなさそう。
     */
    private function getTitle($input)
    {
        if (is_null($input)) {
            return '';
        }

        // タイトルカラム
        $column = DatabasesColumns::where('databases_id', $input->databases_id)
            ->where('title_flag', '1')
            ->orderBy('display_sequence', 'asc')
            ->first();

        if (is_null($column)) {
            return '';
        }

        // タイトルカラムの入力値（入力値があるものだけ。例えば 登録日型 は input_cols にデータない）
        $input_cols = $this->getDatabasesInputCols($input->id);
        $obj = $input_cols->firstWhere('title_flag', '1');

        // 項目の型で処理を分ける。
        if ($column->column_type == DatabaseColumnType::file) {
            // ファイル型
            if (empty($obj)) {
                $value = '';
            } else {
                $value = $obj->client_original_name;
            }
        } elseif ($column->column_type == DatabaseColumnType::image) {
            // 画像型
            if (empty($obj)) {
                $value = '';
            } else {
                $value = Uploads::getFilenameNoExtensionById($obj->value);
            }
        } elseif ($column->column_type == DatabaseColumnType::video) {
            // 動画型
            if (empty($obj)) {
                $value = '';
            } else {
                $value = $obj->client_original_name;
            }
        } elseif ($column->column_type == DatabaseColumnType::link) {
            // リンク型
            if (empty($obj)) {
                $value = '';
            } else {
                $value = $obj->value;
            }
        } elseif ($column->column_type == DatabaseColumnType::date) {
            // 日付型
            if (empty($obj) || empty($obj->value)) {
                $value = '';
            } else {
                $value = date('Y/m/d', strtotime($obj->value));
            }
        } elseif ($column->column_type == DatabaseColumnType::checkbox) {
            // 複数選択型
            if (empty($obj)) {
                $value = '';
            } else {
                $value = str_replace('|', ', ', $obj->value);
            }
        } elseif ($column->column_type == DatabaseColumnType::created) {
            // 登録日型
            $value = $input->created_at;
        } elseif ($column->column_type == DatabaseColumnType::updated) {
            // 更新日型
            $value = $input->updated_at;
        } elseif ($column->column_type == DatabaseColumnType::posted) {
            // 公開日型
            $value = $input->posted_at;
        } elseif ($column->column_type == DatabaseColumnType::display) {
            // 表示順型
            $value = $input->display_sequence;
        } else {
            // その他の型
            $value = $obj ? $obj->value : "";
        }

        return $value;
    }

    /**
     * データベース選択表示関数
     */
    public function listBuckets($request, $page_id, $frame_id, $id = null)
    {
        // 対象のプラグイン
        $plugin_name = $this->frame->plugin_name;

        // Frame データ
        $plugin_frame = Frame::select('frames.*')
                ->where('frames.id', $frame_id)->first();

        // データ取得（1ページの表示件数指定）
        $plugins = Databases::
                select(
                    $plugin_name . '.id',
                    $plugin_name . '.bucket_id',
                    $plugin_name . '.created_at',
                    $plugin_name . '.' . $plugin_name . '_name as plugin_bucket_name',
                    DB::raw('count(databases_inputs.databases_id) as entry_count')
                )
                ->leftJoin('databases_inputs', $plugin_name . '.id', '=', 'databases_inputs.databases_id')
                ->groupBy(
                    $plugin_name . '.id',
                    $plugin_name . '.bucket_id',
                    $plugin_name . '.created_at',
                    $plugin_name . '.' . $plugin_name . '_name',
                    'databases_inputs.databases_id'
                )
                ->orderBy($plugin_name . '.created_at', 'desc')
                ->paginate(10, ["*"], "frame_{$frame_id}_page");

        // 表示テンプレートを呼び出す。
        return $this->view('databases_list_buckets', [
            'plugin_frame' => $plugin_frame,
            'plugins' => $plugins,
        ]);
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

        if (empty($database->id)) {
            $databases_roles = new DatabasesRole();
        } else {
            $databases_roles = DatabasesRole::where('databases_id', $database->id)
                                                ->get()
                                                // keyをrole_nameにした結果をセット
                                                ->mapWithKeys(function ($item) {
                                                    return [$item['role_name'] => $item];
                                                });
        }

        // 表示テンプレートを呼び出す。
        return $this->view('databases_edit_database', [
            'database_frame'  => $database_frame,
            'database'        => $database,
            'databases_roles' => $databases_roles,
            'create_flag' => $create_flag,
            'message'     => $message,
            'errors'      => $errors,
        ])->withInput($request->all);
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
        if (empty($databases_id)) {
            // バケツの登録
            $bucket = new Buckets();
            $bucket->bucket_name = $request->databases_name;
            $bucket->plugin_name = 'databases';
            $bucket->save();

            if (empty($request->copy_databases_id)) {
                // 登録

                // データベース新規オブジェクト
                $databases = new Databases();
            } else {
                // コピー

                // コピー元IDで、データベースデータ取得
                $copy_databases = Databases::where('id', $request->copy_databases_id)->first();
                // ID消して複製
                $databases = $copy_databases->replicate();
            }
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
            $databases = Databases::where('id', $databases_id)->first();

            // データベース名で、Buckets名も更新する
            Buckets::where('id', $databases->bucket_id)->update(['bucket_name' => $request->databases_name]);

            $message = 'データベース設定を変更しました。';
        }

        // データベース設定
        $databases->databases_name      = $request->databases_name;
        $databases->posted_role_display_control_flag = (empty($request->posted_role_display_control_flag)) ? 0 : $request->posted_role_display_control_flag;
        $databases->search_results_empty_message = $request->search_results_empty_message;

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

        // delete -> insert
        // データベース権限データ(表示順の権限毎の制御)を削除する。
        DatabasesRole::where('databases_id', $databases->id)->delete();

        $database_role_name_keys = DatabaseRoleName::getMemberKeys();

        foreach ($database_role_name_keys as $database_role_name_key) {
            if (! isset($request->$database_role_name_key)) {
                // チェックなしの権限はスルー
                continue;
            }
            $posted_regist_edit_display_flag = isset($request->$database_role_name_key['posted_regist_edit_display_flag']) ? $request->$database_role_name_key['posted_regist_edit_display_flag'] : 0;

            $database_role = new DatabasesRole();
            $database_role->databases_id = $databases->id;
            $database_role->role_name = $database_role_name_key;
            $database_role->posted_regist_edit_display_flag = $posted_regist_edit_display_flag;

            // 保存
            $database_role->save();
        }

        // 登録
        if (empty($databases_id)) {
            // コピーIDあり
            if ($request->copy_databases_id) {
                // 【DBカラムコピー】
                $copy_databases_columns = DatabasesColumns::where('databases_id', $request->copy_databases_id)->get();
                foreach ($copy_databases_columns as $copy_databases_column) {
                    // ID消して複製
                    $databases_column = $copy_databases_column->replicate();
                    $databases_column->databases_id = $databases->id;
                    $databases_column->save();

                    // 【DBカラムの権限表示指定コピー】
                    $copy_databases_columns_roles = DatabasesColumnsRole::where('databases_id', $request->copy_databases_id)
                                                                        ->where('databases_columns_id', $copy_databases_column->id)
                                                                        ->get();
                    foreach ($copy_databases_columns_roles as $copy_databases_columns_role) {
                        // ID消して複製
                        $databases_columns_role = $copy_databases_columns_role->replicate();
                        $databases_columns_role->databases_id = $databases->id;
                        $databases_columns_role->databases_columns_id = $databases_column->id;
                        $databases_columns_role->save();
                    }

                    // 選択肢カラム
                    if ($copy_databases_column->column_type == DatabaseColumnType::radio ||
                            $copy_databases_column->column_type == DatabaseColumnType::checkbox ||
                            $copy_databases_column->column_type == DatabaseColumnType::select) {
                        // 【DBカラム選択肢コピー】
                        $copy_databases_columns_selects = DatabasesColumnsSelects::where('databases_columns_id', $copy_databases_column->id)->get();
                        foreach ($copy_databases_columns_selects as $copy_databases_columns_select) {
                            // ID消して複製
                            $databases_columns_select = $copy_databases_columns_select->replicate();
                            $databases_columns_select->databases_columns_id = $databases_column->id;
                            $databases_columns_select->save();
                        }
                    }
                }
            }
        }

        // 新規作成フラグを付けてデータベース設定変更画面を呼ぶ
        $create_flag = false;

        // bugfix: 登録後は登録後の$databases->idを渡す。渡さないと作成後に表示中のDBの変更画面になり、そこに作成したDB名がセットされた状態で表示される
        // return $this->editBuckets($request, $page_id, $frame_id, $databases_id, $create_flag, $message);
        return $this->editBuckets($request, $page_id, $frame_id, $databases->id, $create_flag, $message);
    }

    /**
     * データベース削除処理
     */
    public function destroyBuckets($request, $page_id, $frame_id, $databases_id)
    {
        // databases_id がある場合、データを削除
        if ($databases_id) {
            // 表示設定を削除する。
            DatabasesFrames::where('databases_id', $databases_id)->delete();

            // カラム権限データを削除する。
            DatabasesColumnsRole::where('databases_id', $databases_id)->delete();

            $databases_columns = DatabasesColumns::where('databases_id', $databases_id)->orderBy('display_sequence')->get();

            ////
            //// 添付ファイルの削除
            ////
            $file_column_type_ids = [];
            foreach ($databases_columns as $databases_column) {
                // ファイルタイプ
                if (DatabasesColumns::isFileColumnType($databases_column->column_type)) {
                    $file_column_type_ids[] = $databases_column->id;
                }
            }

            // 削除するファイル情報が入っている詳細データの特定
            $del_file_ids = DatabasesInputCols::whereIn('databases_columns_id', $file_column_type_ids)
                                                ->whereNotNull('value')
                                                ->pluck('value')
                                                ->all();

            // 削除するファイルデータ (もし重複IDあったとしても、in検索によって排除される)
            $delete_uploads = Uploads::whereIn('id', $del_file_ids)->get();
            foreach ($delete_uploads as $delete_upload) {
                // ファイルの削除
                $directory = $this->getDirectory($delete_upload->id);
                Storage::delete($directory . '/' . $delete_upload->id . '.' .$delete_upload->extension);

                // uploadの削除
                $delete_upload->delete();
            }


            foreach ($databases_columns as $databases_column) {
                // 詳細データ値を削除する。
                DatabasesInputCols::where('databases_columns_id', $databases_column->id)->delete();

                // カラムに紐づく選択肢の削除
                $this->deleteColumnsSelects($databases_column->id);
            }

            // 入力行データを削除する。
            DatabasesInputs::where('databases_id', $databases_id)->delete();

            // カラムデータを削除する。
            DatabasesColumns::where('databases_id', $databases_id)->delete();

            // データベース権限データ(表示順の権限毎の制御)を削除する。
            DatabasesRole::where('databases_id', $databases_id)->delete();

            // bugfix: backets, buckets_rolesは $frame->bucket_id で消さない。選択したDBのbucket_idで消す
            $databases = Databases::find($databases_id);

            // buckets_rolesの削除
            BucketsRoles::where('buckets_id', $databases->bucket_id)->delete();

            // backetsの削除
            Buckets::where('id', $databases->bucket_id)->delete();

            // change: このバケツを表示している全ページのフレームのバケツIDを消す
            // // バケツIDの取得のためにFrame を取得(Frame を更新する前に取得しておく)
            // $frame = Frame::where('id', $frame_id)->first();
            // // bugfix: フレームのbucket_idと削除するDBのbucket_idが同じなら、FrameのバケツIDの更新する
            // if ($frame->bucket_id == $databases->bucket_id) {
            //     // FrameのバケツIDの更新
            //     Frame::where('bucket_id', $frame->bucket_id)->update(['bucket_id' => null]);
            // }
            // FrameのバケツIDの更新. このバケツを表示している全ページのフレームのバケツIDを消す
            Frame::where('bucket_id', $databases->bucket_id)->update(['bucket_id' => null]);

            // データベース設定を削除する。
            Databases::destroy($databases_id);
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
        $column->required = $request->required ? Required::on : Required::off;
        $column->display_sequence = $max_display_sequence;
        $column->caption_color = Bs4TextColor::dark;

        // 複数年月型（テキスト入力）は、デフォルトでキャプションをセットする
        if (DatabaseColumnType::dates_ym == $request->column_type) {
            $column->caption = DatabaseColumnType::dates_ym_caption;
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
                'databases_columns.caption_list_detail',
                'databases_columns.caption_list_detail_color',
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
                'databases_columns.caption_list_detail',
                'databases_columns.caption_list_detail_color',
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
        return $this->view('databases_edit', [
            'databases_id' => $databases_id,
            'columns' => $columns,
            'title_flag' => $title_flag,
            'message' => $message,
            'errors' => $errors,
        ]);
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
        $str_column_name = "column_name_".$request->column_id;
        $str_column_type = "column_type_".$request->column_id;
        $str_required = "required_".$request->column_id;

        $validate_value = [
            'column_name_'.$request->column_id => ['required'],
            'column_type_'.$request->column_id => ['required'],
        ];

        $validate_attribute = [
            'column_name_'.$request->column_id => '項目名',
            'column_type_'.$request->column_id => '型',
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
        $column->column_name = $request->$str_column_name;
        $column->column_type = $request->$str_column_type;
        $column->required = $request->$str_required ? Required::on : Required::off;

        // 複数年月型（テキスト入力）は、キャプションが空なら定型文をセットする
        if (DatabaseColumnType::dates_ym == $request->column_type &&
                !$column->caption) {
            $column->caption = DatabaseColumnType::dates_ym_caption;
        }

        $column->save();
        $message = '項目【 '. $request->$str_column_name .' 】を更新しました。';

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
     * 項目に紐づく詳細設定の更新
     */
    public function updateColumnDetail($request, $page_id, $frame_id)
    {
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

        // bugfix: 更新データは上記update後に取得しないと、title_flagが更新されない不具合対応
        $column = DatabasesColumns::where('id', $request->column_id)->first();

        // タイトル指定
        $column->title_flag = $title_flag;

        // 項目の更新処理
        $column->caption = $request->caption;
        $column->caption_color = $request->caption_color;
        $column->caption_list_detail = $request->caption_list_detail;
        $column->caption_list_detail_color = $request->caption_list_detail_color;
        $column->frame_col = $request->frame_col;
        $column->classname = $request->classname;
        // 分刻み指定
        if ($column->column_type == DatabaseColumnType::time) {
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
        // 項目名を非表示にする指定
        $column->label_hide_flag = (empty($request->label_hide_flag)) ? 0 : $request->label_hide_flag;
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

        $column_role_name_keys = DatabaseColumnRoleName::getMemberKeys();

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
        $messages = [
            'select_name.regex' => ':attributeに | を含める事はできないため、取り除いてください。',
        ];

        // エラーチェック  regex（|を含まない）
        $validator = Validator::make($request->all(), [
            'select_name'  => ['required', 'regex:/^(?!.*\|).*$/'],
        ], $messages);
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

        $messages = [
            'select_name.regex' => ':attributeに | を含める事はできないため、取り除いてください。',
        ];

        // エラーチェック regex（|を含まない）
        $validator = Validator::make($request->all(), [
            'select_name'  => ['required', 'regex:/^(?!.*\|).*$/'],
        ], $messages);
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

        // 見出し行-頭（固定項目）
        $csv_array[0]['id'] = 'id';
        $copy_base['id'] = '';
        // 見出し行
        foreach ($columns as $column) {
            $csv_array[0][$column->id] = $column->column_name;
            $copy_base[$column->id] = '';
        }
        // 見出し行-末尾（固定項目）
        $csv_array[0]['posted_at'] = '公開日時';
        $csv_array[0]['display_sequence'] = '表示順';
        $copy_base['posted_at'] = '';
        $copy_base['display_sequence'] = '';

        // $data_output_flag = falseは、CSVフォーマットダウンロード処理
        if ($data_output_flag) {
            // 登録データの取得
            $input_cols = DatabasesInputCols::
                                        select(
                                            'databases_input_cols.*',
                                            'databases_inputs.created_at as inputs_created_at',
                                            'databases_inputs.updated_at as inputs_updated_at',
                                            'databases_inputs.posted_at as inputs_posted_at',
                                            'databases_inputs.display_sequence as inputs_display_sequence'
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
                    $csv_array[$input_col->databases_inputs_id][$input_col->databases_columns_id] = $input_col->inputs_display_sequence;

                    // 初回で固定項目をセット
                    $csv_array[$input_col->databases_inputs_id]['id'] = $input_col->databases_inputs_id;

                    $databases_inputs = DatabasesInputs::where('id', $input_col->databases_inputs_id)->first();
                    // excelでは 2020-07-01 のハイフンや 2020/07/01 と頭ゼロが付けられないため、インポート時は修正できる日付形式に見直し
                    // $csv_array[$input_col->databases_inputs_id]['posted_at'] = $databases_inputs->posted_at->format('Y/m/d H:i');
                    $csv_array[$input_col->databases_inputs_id]['posted_at'] = $databases_inputs->posted_at->format('Y/n/j H:i');

                    $csv_array[$input_col->databases_inputs_id]['display_sequence'] = $databases_inputs->display_sequence;

                    // 登録日型、更新日型、公開日型は $input_cols に含まれないので、初回でセット
                    foreach ($columns as $column) {
                        switch ($column->column_type) {
                            case DatabaseColumnType::created:
                                $csv_array[$input_col->databases_inputs_id][$column->id] = $input_col->inputs_created_at;
                                break;
                            case DatabaseColumnType::updated:
                                $csv_array[$input_col->databases_inputs_id][$column->id] = $input_col->inputs_updated_at;
                                break;
                            case DatabaseColumnType::posted:
                                $csv_array[$input_col->databases_inputs_id][$column->id] = $input_col->inputs_posted_at;
                                break;
                            case DatabaseColumnType::display:
                                $csv_array[$input_col->databases_inputs_id][$column->id] = $input_col->inputs_display_sequence;
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
            // 末尾カンマを削除
            $csv_data = substr($csv_data, 0, -1);
            $csv_data .= "\n";
        }

        // Log::debug(var_export($request->character_code, true));

        // 文字コード変換
        // $csv_data = mb_convert_encoding($csv_data, "SJIS-win");
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
        // クラスを使用する前に、それが存在するかどうかを調べます
        if (UnzipUtils::useZipArchive()) {
            // zip or csv
            $rules = [
                'databases_csv'  => [
                    'required',
                    'file',
                    'mimes:csv,txt,zip,html', // mimesの都合上text/csvなのでtxtも許可が必要. ウィジウィグのHTMLがcsvに含まれているとhtmlと判定されるため、ファイル拡張子チェックでhtmlを許可
                    'mimetypes:text/plain,application/zip,text/html',
                ],
            ];
            // csvを通すため、txt,htmlを追加しているため、メッセージをカスタマイズする。
            $messages = [
                'databases_csv.mimes' => ':attributeにはcsv, zipのうちいずれかの形式のファイルを指定してください。',
                'databases_csv.mimetypes' => ':attributeにはtext/plain, application/zipのうちいずれかの形式のファイルを指定してください。',
            ];
        } else {
            // csv
            $rules = [
                'databases_csv'  => [
                    'required',
                    'file',
                    'mimes:csv,txt,html', // mimesの都合上text/csvなのでtxtも許可が必要. ウィジウィグのHTMLがcsvに含まれているとhtmlと判定されるため、ファイル拡張子チェックでhtmlを許可
                    'mimetypes:text/plain,text/html',
                ],
            ];
            // csvを通すため、txt,htmlを追加しているため、メッセージをカスタマイズする。
            $messages = [
                'databases_csv.mimes' => ':attributeにはcsv形式のファイルを指定してください。',
                'databases_csv.mimetypes' => ':attributeにはtext/plain形式のファイルを指定してください。',
            ];
        }

        // 画面エラーチェック
        $validator = Validator::make($request->all(), $rules, $messages);
        $validator->setAttributeNames([
            'databases_csv'  => 'CSVファイル',
        ]);

        if ($validator->fails()) {
            // Log::debug(var_export($validator->errors(), true));
            // エラーと共に編集画面を呼び出す
            return redirect()->back()->withErrors($validator)->withInput();
        }


        // CSVファイル一時保存
        $path = $request->file('databases_csv')->store('tmp');
        // Log::debug(var_export(storage_path('app/') . $path, true));
        $csv_full_path = storage_path('app/') . $path;
        $unzip_dir_full_path = null;

        // ファイル拡張子取得
        $file_extension = $request->file('databases_csv')->getClientOriginalExtension();
        // 小文字に変換
        $file_extension = strtolower($file_extension);
        // Log::debug(var_export($file_extension, true));

        // クラスを使用する前に、それが存在するかどうかを調べます
        // if (class_exists('ZipArchive')) {
        if (UnzipUtils::useZipArchive()) {
            if ($file_extension == 'zip') {
                $zip_full_path = storage_path('app/') . $path;

                // 一時的な解凍フォルダ名
                // $tmp_dir = uniqid('', true);
                $tmp_dir = UnzipUtils::getTmpDir();
                $unzip_dir_full_path = storage_path('app/') . "tmp/database/{$tmp_dir}/";

                $error_msg = UnzipUtils::unzip($zip_full_path, $unzip_dir_full_path);
                if ($error_msg !== true) {
                    // 一時ファイルの削除
                    $this->rmImportTmpFile($path, $file_extension, $unzip_dir_full_path);

                    return redirect()->back()->withErrors(['databases_csv' => $error_msg])->withInput();
                }

                // パターンにマッチするパス名を探す。 csvは１つの想定
                // winの標準機能でzip圧縮すると、zip内にフォルダが１つでき、その中にファイルが格納されているため、
                // zip内１つ下のフォルダを検索
                // $csv_full_path = $unzip_dir_full_path . "database/database.csv";
                // $csv_full_paths = glob($unzip_dir_full_path . "database/*.csv");
                $csv_full_paths = glob($unzip_dir_full_path . "*/*.csv");
                // Log::debug(var_export($csv_full_paths, true));

                if (empty($csv_full_paths)) {
                    // 「エラー」zipファイルにcsvを含めてください。csv0個
                    // 一時ファイルの削除
                    $this->rmImportTmpFile($path, $file_extension, $unzip_dir_full_path);

                    $error_msg = "ZIPファイルにCSVを含めてください。";
                    return redirect()->back()->withErrors(['databases_csv' => $error_msg])->withInput();
                }
                if (count($csv_full_paths) >= 2) {
                    // 「エラー」zipファイルに含めるcsvは１つにしてください。csv2個以上
                    // 一時ファイルの削除
                    $this->rmImportTmpFile($path, $file_extension, $unzip_dir_full_path);

                    $error_msg = "ZIPファイルに含めるCSVは１つにしてください。";
                    return redirect()->back()->withErrors(['databases_csv' => $error_msg])->withInput();
                }
                $csv_full_path = $csv_full_paths[0];

                // 一時ファイルの削除
                // $this->rmImportTmpFile($path, $file_extension, $unzip_dir_full_path);
                // dd('ここまで');
            }
        }

        // 文字コード
        $character_code = $request->character_code;

        // 文字コード自動検出
        if ($character_code == CsvCharacterCode::auto) {
            // 文字コードの自動検出(文字エンコーディングをsjis-win, UTF-8の順番で自動検出. 対象文字コード外の場合、false戻る)
            $character_code = CsvUtils::getCharacterCodeAuto($csv_full_path);
            if (!$character_code) {
                // 一時ファイルの削除
                $this->rmImportTmpFile($path, $file_extension, $unzip_dir_full_path);

                $error_msgs = "文字コードを自動検出できませんでした。CSVファイルの文字コードを " . CsvCharacterCode::getSelectMembersDescription(CsvCharacterCode::sjis_win) .
                            ", " . CsvCharacterCode::getSelectMembersDescription(CsvCharacterCode::utf_8) . " のいずれかに変更してください。";

                return redirect()->back()->withErrors(['databases_csv' => $error_msgs])->withInput();
            }
        }

        // 読み込み
        $fp = fopen($csv_full_path, 'r');
        // CSVファイル：Shift-JIS -> UTF-8変換時のみ
        if ($character_code == CsvCharacterCode::sjis_win) {
            // ストリームフィルタ内で、Shift-JIS -> UTF-8変換
            $fp = CsvUtils::setStreamFilterRegisterSjisToUtf8($fp);
        }

        // bugfix: fgetcsv() は ロケール設定の影響を受け、xampp環境＋日本語文字列で誤動作したため、ロケール設定する。
        setlocale(LC_ALL, 'ja_JP.UTF-8');

        // 一行目（ヘッダ）
        $header_columns = fgetcsv($fp, 0, ',');
        // CSVファイル：UTF-8のみ
        if ($character_code == CsvCharacterCode::utf_8) {
            // UTF-8のみBOMコードを取り除く
            $header_columns = CsvUtils::removeUtf8Bom($header_columns);
        }
        // dd($csv_full_path);
        // \Log::debug('$header_columns:'. var_export($header_columns, true));

        // カラムの取得
        $databases_columns = DatabasesColumns::where('databases_id', $id)->orderBy('display_sequence', 'asc')->get();
        // Log::debug('$databases_columns:'. var_export($databases_columns, true));

        // ヘッダー項目のエラーチェック
        $error_msgs = $this->checkCsvHeader($header_columns, $databases_columns);
        if (!empty($error_msgs)) {
            // 一時ファイルの削除
            fclose($fp);
            $this->rmImportTmpFile($path, $file_extension, $unzip_dir_full_path);

            // return ( $this->import($request, $page_id, $error_msgs) );
            return redirect()->back()->withErrors(['databases_csv' => $error_msgs])->withInput();
        }

        // データ項目のエラーチェック
        $error_msgs = $this->checkCvslines($fp, $databases_columns, $id, $file_extension, $unzip_dir_full_path);
        if (!empty($error_msgs)) {
            // 一時ファイルの削除
            fclose($fp);
            $this->rmImportTmpFile($path, $file_extension, $unzip_dir_full_path);

            // return ( $this->import($request, $page_id, $error_msgs) );
            return redirect()->back()->withErrors(['databases_csv' => $error_msgs])->withInput();
        }

        if ($file_extension == 'zip') {
            // １．全ファイルアップロード
            //     uploadsフォルダを全アップロード、変数にアップロードIDもつ
            //     使われないファイルがアップロードされる事もあるが、temporary_flag = 1で残るので後から判別可能（今後ファイルクリーアップ作って綺麗にする方向かなぁ）

            // パターンにマッチするパス名を探す。
            // $unzip_uploads_full_paths = glob($unzip_dir_full_path . "database/uploads/*");
            $unzip_uploads_full_paths = glob($unzip_dir_full_path . "*/uploads/*");
            // Log::debug(var_export($unzip_uploads_full_paths, true));
            $filesystem = new Filesystem();

            // アップロードしたzipのアップロードファイル
            // $unzip_uploadeds = [
            //     991 => 'uploads/filename1.jpg',
            //     992 => 'uploads/filename2.jpg',
            //     993 => 'uploads/filename3.jpg',
            // ];
            $unzip_uploadeds = [];

            foreach ($unzip_uploads_full_paths as $unzip_uploads_full_path) {
                // uploads テーブルに情報追加、ファイルのid を取得する
                $upload = Uploads::create([
                    // 'client_original_name' => $request->file($req_filename)->getClientOriginalName(),
                    // 'mimetype'             => $request->file($req_filename)->getClientMimeType(),
                    // 'extension'            => $request->file($req_filename)->getClientOriginalExtension(),
                    // 'size'                 => $request->file($req_filename)->getClientSize(),
                    'client_original_name' => $filesystem->basename($unzip_uploads_full_path),
                    'mimetype'             => $filesystem->mimeType($unzip_uploads_full_path),
                    'extension'            => $filesystem->extension($unzip_uploads_full_path),
                    'size'                 => $filesystem->size($unzip_uploads_full_path),
                    'plugin_name'          => 'databases',
                    'page_id'              => $page_id,
                    'temporary_flag'       => 1,
                    'created_id'           => Auth::user()->id,
                ]);
                // Log::debug(var_export($filesystem->mimeType($unzip_uploads_full_path), true));
                // Log::debug(var_export($filesystem->basename($unzip_uploads_full_path), true));
                // Log::debug(var_export($filesystem->extension($unzip_uploads_full_path), true));
                // Log::debug(var_export($filesystem->size($unzip_uploads_full_path), true));

                // // 一時ファイルの削除
                // fclose($fp);
                // $this->rmImportTmpFile($path, $file_extension, $unzip_dir_full_path);
                // dd('ここまで');

                ////
                //// ファイル保存
                ////
                $directory = $this->getDirectory($upload->id);

                // $upload_path = $request->file($req_filename)->storeAs($directory, $upload->id . '.' . $request->file($req_filename)->getClientOriginalExtension());
                //
                // zipで添付ファイルアップロードのため、$request->file($req_filename)->storeAs($directory, $upload->id...) を使えない。
                // storeAs内で $directory を作成してると思われ、uploadsディレクトリが無い場合もありえる（他機能で１度もアップロードしてない場合等）ため、自分でアップロードディレクトリを作成する。
                // $recursive=trueは再回帰的にディレクトリ作成.
                if (! $filesystem->exists(storage_path('app/') . $directory . '/')) {
                    $filesystem->makeDirectory(storage_path('app/') . $directory . '/', 0775, true);
                }

                // 一時ディレクトリから、uploadsディレクトリに移動
                // 拡張子なしに対応
                // $filesystem->move($unzip_uploads_full_path, storage_path('app/') . $directory . '/' . $upload->id . '.' . $filesystem->extension($unzip_uploads_full_path));
                $unzip_extension = $filesystem->extension($unzip_uploads_full_path) ? '.'.$filesystem->extension($unzip_uploads_full_path) : '';
                $filesystem->move($unzip_uploads_full_path, storage_path('app/') . $directory . '/' . $upload->id . $unzip_extension);

                $unzip_uploadeds[$upload->id] = 'uploads/' . $filesystem->basename($unzip_uploads_full_path);
            }
        }

        // // 一時ファイルの削除
        // fclose($fp);
        // $this->rmImportTmpFile($path, $file_extension, $unzip_dir_full_path);
        // dd('ここまで');

        // ファイルポインタの位置を先頭に戻す
        rewind($fp);

        // ヘッダー
        $header_columns = fgetcsv($fp, 0, ',');
        // CSVファイル：UTF-8のみ
        if ($character_code == CsvCharacterCode::utf_8) {
            // UTF-8のみBOMコードを取り除く
            $header_columns = CsvUtils::removeUtf8Bom($header_columns);
        }

        // データベースの取得
        $database = Databases::where('id', $id)->first();

        // データ
        while (($csv_columns = fgetcsv($fp, 0, ',')) !== false) {
            // --- 入力値変換
            // Log::debug(var_export($csv_columns, true));

            // 入力値をトリム(preg_replace(/u)で置換. /u = UTF-8 として処理)
            // $request->merge(self::trimInput($request->all()));
            $csv_columns = StringUtils::trimInput($csv_columns);

            // 配列の頭から要素(id)を取り除いて取得
            // CSVのデータ行の頭は、必ず固定項目のidの想定
            $databases_inputs_id = array_shift($csv_columns);
            // 空文字をnullに変換
            $databases_inputs_id = StringUtils::convertEmptyStringsToNull($databases_inputs_id);

            foreach ($csv_columns as $col => &$csv_column) {
                // 空文字をnullに変換
                $csv_column = StringUtils::convertEmptyStringsToNull($csv_column);

                // $csv_columnsは項目数分くる, $databases_columnsは項目数分ある。
                // よってこの２つの配列数は同じになる想定。issetでチェックしているが基本ある想定。
                if (isset($databases_columns[$col])) {
                    // 数値チェック
                    // if ($databases_column->rule_allowed_numeric) {
                    if ($databases_columns[$col]->rule_allowed_numeric) {
                        // 入力値があった場合（マイナスを意図した入力記号はすべて半角に置換する）＆ 全角→半角へ丸める
                        $csv_column = StringUtils::convertNumericAndMinusZenkakuToHankaku($csv_column);
                    }

                    // csv値あり
                    if ($csv_column) {
                        if ($file_extension == 'zip') {
                            // ファイルタイプ
                            if (DatabasesColumns::isFileColumnType($databases_columns[$col]->column_type)) {
                                // パスをアップロードIDに書き換える。
                                $csv_column = array_search($csv_column, $unzip_uploadeds);
                                $csv_column = $csv_column === false ? null : $csv_column;

                                if (!empty($databases_inputs_id)) {
                                    // 更新

                                    // アップロードIDあり
                                    if ($csv_column) {
                                        // Uploadファイル削除
                                        // Uploadデータ削除
                                        // ファイル系データ削除

                                        // 削除するファイル情報が入っている詳細データの特定
                                        $del_databases_input_cols = DatabasesInputCols::where('databases_inputs_id', $databases_inputs_id)
                                                                                    ->where('databases_columns_id', $databases_columns[$col]->id)
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

                                        // DatabasesInputColsのデータありのファイルタイプは、ここで消す
                                        DatabasesInputCols::where('databases_inputs_id', $databases_inputs_id)
                                                            ->where('databases_columns_id', $databases_columns[$col]->id)
                                                            ->delete();
                                    }
                                }
                            }
                        }
                        // 複数選択型
                        if ($databases_columns[$col]->column_type == DatabaseColumnType::checkbox) {
                            // 一度配列にして、trim後、また文字列に戻す。
                            $csv_column = StringUtils::trimInputKanma($csv_column);
                        }
                        // 複数年月型
                        if ($databases_columns[$col]->column_type == DatabaseColumnType::dates_ym) {
                            // 一度配列にして、trim後、また文字列に戻す。
                            $csv_column = StringUtils::trimInputKanma($csv_column);
                        }
                        // 日付型
                        if ($databases_columns[$col]->column_type == DatabaseColumnType::date) {
                            // Excelのcsvで日付を入力すると、2020/1/1形式になるため、2020/01/01形式に変換する
                            // バリデーションで無効な日付は排除済み。
                            // csv値ありのみ。
                            $dt = new Carbon($csv_column);
                            $csv_column = $dt->format('Y/m/d');
                        }
                    }
                }
            }
            // Log::debug('$csv_columns:'. var_export($csv_columns, true));

            // 配列の末尾から要素を取り除いて取得。CSVのデータ行の末尾は必ず下記固定項目の想定
            // 配列末尾：表示順
            // 次の末尾：公開日時
            $display_sequence = array_pop($csv_columns);
            $display_sequence = $this->getSaveDisplaySequence($display_sequence, $database->id, $databases_inputs_id);
            $posted_at = array_pop($csv_columns);
            $posted_at = new Carbon($posted_at);

            $status = StatusType::active;  // 公開

            // // 一時ファイルの削除
            // fclose($fp);
            // Storage::delete($path);
            // dd('ここまで' . $posted_at);

            if (empty($databases_inputs_id)) {
                // 登録

                $databases_inputs = new DatabasesInputs();
                $databases_inputs->databases_id = $database->id;
                $databases_inputs->status = $status;
                $databases_inputs->display_sequence = $display_sequence;
                // 公開日時
                // $databases_inputs->posted_at = $posted_at . ':00';
                $databases_inputs->posted_at = $posted_at;
                $databases_inputs->save();
            } else {
                // 更新

                // databases_inputs_idはバリデートでDatabasesInputs存在チェック済みなので、必ずデータある想定
                $databases_inputs = DatabasesInputs::where('id', $databases_inputs_id)->first();
                // 更新されたら、行レコードの updated_at を更新したいので、update()
                $databases_inputs->updated_at = now();
                // インポートで更新時に status は更新しない
                // $databases_inputs->status = $status;
                $databases_inputs->display_sequence = $display_sequence;
                // 公開日時
                $databases_inputs->posted_at = $posted_at;
                $databases_inputs->update();

                // databases_inputs_id（行 id）が渡ってきたら、詳細データは一度消す。その後、登録と同じ処理にする。
                $file_columns_ids = [];
                foreach ($databases_columns as $databases_column) {
                    // ファイルタイプ
                    if (DatabasesColumns::isFileColumnType($databases_column->column_type)) {
                        $file_columns_ids[] = $databases_column->id;
                    }
                }

                // delete -> insertでファイルタイプは、ここでは消さずに残す。
                DatabasesInputCols::where('databases_inputs_id', $databases_inputs_id)
                                    ->whereNotIn('databases_columns_id', $file_columns_ids)
                                    ->delete();
            }

            // --- データ行の各項目登録
            foreach ($csv_columns as $col => $csv_column) {
                // $csv_columnsは項目数分くる, $databases_columnsは項目数分ある。
                // よってこの２つの配列数は同じになる想定。issetでチェックしているが基本ある想定。
                if (isset($databases_columns[$col])) {
                    // 登録日型・更新日型・公開日型・表示順は、databases_inputsテーブルの登録日・更新日・公開日・表示順を利用するため、登録しない
                    if (DatabasesColumns::isNotInputColumnType($databases_columns[$col]->column_type)) {
                        continue;
                    }

                    // ファイルタイプ
                    if (DatabasesColumns::isFileColumnType($databases_columns[$col]->column_type)) {
                        if ($file_extension == 'csv') {
                            if (empty($databases_inputs_id)) {
                                // 登録: ファイルなしは既に$csv_columnにnullをセット済みのため、何もしない.（nullだとno_image画像が表示される）
                                // $csv_column = null;
                            } else {
                                // 更新
                                // 空(null)は、データを消さずに残してあるため、continue で何も処理させない
                                continue;
                            }
                        } elseif ($file_extension == 'zip') {
                            if (empty($databases_inputs_id)) {
                                // 登録: ファイルなしは既に$csv_columnにnullをセット済みのため、何もしない.（nullだとno_image画像が表示される）
                                // $csv_column = null;
                            } else {
                                // 更新
                                if (empty($csv_column)) {
                                    // 空(null)は、データを消さずに残してあるため、continue で何も処理させない
                                    continue;
                                } else {
                                    // データありは、新しいアップロードIDが格納されているため、そのまま登録させる。
                                    // uploads テーブルの一時フラグを更新
                                    // $uploads_count = Uploads::where('id', $value)->update(['temporary_flag' => 0]);
                                    $uploads_count = Uploads::where('id', $csv_column)->update(['temporary_flag' => 0]);
                                }
                            }
                        }
                    }

                    // change: データ登録フラグ（data_save_flag）は、フォームの名残で残っているだけのため、フラグ見ないようする
                    // データ登録フラグを見て登録
                    // if ($database->data_save_flag) {
                    $databases_input_cols = new DatabasesInputCols();
                    $databases_input_cols->databases_inputs_id = $databases_inputs->id;
                    $databases_input_cols->databases_columns_id = $databases_columns[$col]['id'];
                    // $databases_input_cols->value = $value;
                    $databases_input_cols->value = $csv_column;
                    $databases_input_cols->save();
                    // }
                }
            }
        }

        // 一時ファイルの削除
        fclose($fp);
        $this->rmImportTmpFile($path, $file_extension, $unzip_dir_full_path);

        $request->flash_message = 'インポートしました。';

        // redirect_path指定して自動遷移するため、returnで表示viewの指定不要。
    }

    /**
     * CSVヘッダーチェック
     */
    private function checkCsvHeader($header_columns, $databases_columns)
    {
        if (empty($header_columns)) {
            return array("CSVファイルが空です。");
        }

        $header_column_format = [];
        // ヘッダ行-頭（固定項目）
        $header_column_format[] = 'id';
        foreach ($databases_columns as $databases_column) {
            $header_column_format[] = $databases_column->column_name;
        }
        // ヘッダ行-末尾（固定項目）
        $header_column_format[] = '公開日時';
        $header_column_format[] = '表示順';

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
    private function checkCvslines($fp, $databases_columns, $databases_id, $file_extension, $unzip_dir_full_path)
    {
        $rules = [];
        // $rules = [
        //     0 => [],
        //     1 => ['required'],
        // ];

        // 行頭（固定項目）
        // id
        // bugfix: id存在チェクは id & databases_id でチェックしないと、コピーしたデータベースに上書き出来てしまうため、ここではなく別途チェックする。
        // $rules[0] = ['nullable', 'numeric', 'exists:databases_inputs,id'];
        $rules[0] = ['nullable', 'numeric'];

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
                // 行頭（固定項目）の id 分　col をずらすため、+1
                $rules[$col + 1] = $validator_array['column']['databases_columns_value.' . $databases_column->id];

                if ($file_extension == 'csv') {
                    // ファイルタイプ
                    if (DatabasesColumns::isFileColumnType($databases_column->column_type)) {
                        // csv単体のインポートでは、ファイルタイプはインポートできないため、バリデーションルールをチェックなしで上書き。
                        // 登録時の値は別途 null に変換してる。
                        $rules[$col + 1] = [];
                    }
                } elseif ($file_extension == 'zip') {
                    // zipのファイルタイプのバリデーションは、Laravelのそのまま使えなかった。
                    // 【対応】
                    // csv用の画像、動画バリデーションを作成して上書きする
                    // 【原因】
                    // 画像 = image = mimes:jpeg,png,gif,bmp,svg
                    // 動画 = mimes:mp4
                    // のmimesチェックは、Symfony\Component\HttpFoundation\File\UploadedFileクラスの値をチェックするが、
                    // UploadedFile::isValid() 内で php標準の is_uploaded_file() でHTTP POST でアップロードされたファイルかどうかを調べていて、
                    // 無理くり添付ファイルをUploadedFileクラスで newして作った変数では、is_uploaded_file() で false になり「アップロード失敗しました」とバリデーションエラーに必ずなるため。

                    if ($databases_column->column_type == DatabaseColumnType::file) {
                        // バリデーション元々なし（バリデーションがないため、ここには到達しない想定）
                        $rules[$col + 1] = [];
                    } elseif ($databases_column->column_type == DatabaseColumnType::image) {
                        // csv用のバリデーションで上書き
                        $rules[$col + 1] = ['nullable', new CustomValiCsvImage()];
                    } elseif ($databases_column->column_type == DatabaseColumnType::video) {
                        // csv用のバリデーションで上書き
                        $rules[$col + 1] = ['nullable', new CustomValiCsvExtensions(['mp4'])];
                    }
                }
            } else {
                // ルールなしは空配列入れないと、バリデーション項目がずれるのでセット
                $rules[$col + 1] = [];
            }
        }
        // 行末（固定項目）
        // 公開日時
        // excelでは 2020-07-01 のハイフンや 2020/07/01 と頭ゼロが付けられないため、インポート時は修正できる日付形式に見直し
        // $rules[$col + 1] = ['required', 'date_format:Y-m-d H:i'];
        // 行頭（固定項目） の id 分で+1, 行末に追加で+1 = col+2ずらす
        $rules[$col + 2] = ['required', 'date_format:Y/n/j H:i'];
        // 表示順
        $rules[$col + 3] = ['nullable', 'numeric'];

        // ヘッダー行が1行目なので、2行目からデータ始まる
        $line_count = 2;
        $errors = [];

        $filesystem = new Filesystem();
        $unzip_uploads_full_paths2 = [];
        // $unzip_uploads_full_paths2 = [
        //     'uploads/MP4_test_movie.mp4' => 'C:\\connect-cms\\htdocs\\storage\\app/tmp/database/5f5731f7d3ac92.19491258/database/uploads/MP4_test_movie.mp4',
        //     'uploads/file2.jpg' => 'C:\\connect-cms\\htdocs\\storage\\app/tmp/database/5f5731f7d3ac92.19491258/database/uploads/file2.jpg'
        // ];

        if ($file_extension == 'zip') {
            // パターンにマッチするパス名を探す。
            $unzip_uploads_full_paths = glob($unzip_dir_full_path . "*/uploads/*");

            foreach ($unzip_uploads_full_paths as $unzip_uploads_full_path) {
                $unzip_uploads_full_paths2['uploads/' . $filesystem->basename($unzip_uploads_full_path)] = $unzip_uploads_full_path;
            }
        }

        while (($csv_columns = fgetcsv($fp, 0, ',')) !== false) {
            // 入力値をトリム (preg_replace(/u)で置換. /u = UTF-8 として処理)
            $csv_columns = StringUtils::trimInput($csv_columns);

            // 配列の頭から要素(id)を取り除いて取得
            // CSVのデータ行の頭は、必ず固定項目のidの想定
            $databases_inputs_id = array_shift($csv_columns);

            if (!empty($databases_inputs_id)) {
                // id & databases_idの存在チェック
                if (! DatabasesInputs::where('id', $databases_inputs_id)->where('databases_id', $databases_id)->exists()) {
                    $errors[] = $line_count . '行目のidは対象データベースに存在しません。';
                }
            }

            foreach ($csv_columns as $col => &$csv_column) {
                // 空文字をnullに変換
                $csv_column = StringUtils::convertEmptyStringsToNull($csv_column);

                // $csv_columnsは項目数分くる, $databases_columnsは項目数分ある。
                // よってこの２つの配列数は同じになる想定。issetでチェックしているが基本ある想定。
                if (isset($databases_columns[$col])) {
                    // csv値あり
                    if ($csv_column) {
                        if ($file_extension == 'zip') {
                            // ファイルタイプ
                            if (DatabasesColumns::isFileColumnType($databases_columns[$col]->column_type)) {
                                // バリデーションのためだけに、一時的にパスをフルパスに書き換える。
                                if (isset($unzip_uploads_full_paths2[$csv_column])) {
                                    $csv_column = $unzip_uploads_full_paths2[$csv_column];
                                } else {
                                    // 対応するパスが無いため、エラー
                                    $csv_column = null;
                                    $errors[] = $line_count . '行目の' . $databases_columns[$col]->column_name . 'のファイルが見つかりません。';
                                }
                            }
                        }
                        // 複数選択型
                        if ($databases_columns[$col]->column_type == DatabaseColumnType::checkbox) {
                            // 複数選択のバリデーションの入力値は、配列が前提のため、配列に変換する。
                            $csv_column = explode(self::CHECKBOX_SEPARATOR, $csv_column);
                            // 配列値の入力値をトリム (preg_replace(/u)で置換. /u = UTF-8 として処理)
                            $csv_column = StringUtils::trimInput($csv_column);
                            // Log::debug(var_export($csv_column, true));
                        }
                    }
                }
            }

            // 頭のIDをarrayに戻す
            array_unshift($csv_columns, $databases_inputs_id);

            // バリデーション
            $validator = Validator::make($csv_columns, $rules);
            // Log::debug($line_count . '行目の$csv_columns:' . var_export($csv_columns, true));
            // Log::debug(var_export($rules, true));

            $attribute_names = [];
            // 行頭（固定項目）
            // id
            $attribute_names[0] = $line_count . '行目のid';
            foreach ($databases_columns as $col => $databases_column) {
                // 行数＋項目名
                // 頭-固定項目 の id 分　col をずらすため、+1
                $attribute_names[$col + 1] = $line_count . '行目の' . $databases_column->column_name;
            }
            // 行末（固定項目）
            // 行頭（固定項目）の id 分で+1, 行末に追加で+1 = col+2ずらす
            $attribute_names[$col + 2] = $line_count . '行目の公開日時';
            $attribute_names[$col + 3] = $line_count . '行目の表示順';

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
     * インポート時の一時ファイル削除
     */
    private function rmImportTmpFile($path, $file_extension, $unzip_dir_full_path = null)
    {
        if ($file_extension == 'zip') {
            // 空でないディレクトリを削除
            UnzipUtils::rmdirNotEmpty($unzip_dir_full_path);
        }

        // 一時ファイルの削除
        Storage::delete($path);
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
            // bugfix: DBが選択されていない状態で「表示設定」タブを選択するとエラーとなる不具合対応
            // $database = null;
            $database = new Databases();
            $columns = null;
            $select_columns = null;
            $columns_selects = null;
        } else {
            $database = Databases::where('bucket_id', $database_frame->bucket_id)->first();

            // カラムの取得
            $columns = DatabasesColumns::where('databases_id', $database->id)->orderBy('display_sequence', 'asc')->get();

            // 絞り込み制御の対象カラム
            $select_columns = $columns->whereIn('column_type', [DatabaseColumnType::radio, DatabaseColumnType::checkbox, DatabaseColumnType::select]);

            // カラム選択肢の取得
            $columns_selects = DatabasesColumnsSelects::whereIn('databases_columns_id', $columns->pluck('id'))->orderBy('display_sequence', 'asc')->get();
        }

        // 表示テンプレートを呼び出す。
        return $this->view(
            'databases_edit_view',
            [
                'database_frame' => $database_frame,
                'view_frame' => $view_frame,
                'database' => $database,
                'columns' => $columns,
                'select_columns' => $select_columns,
                'columns_selects' => $columns_selects,
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

        // 項目のエラーチェック
        $validator = Validator::make($request->all(), $validator_values);
        $validator->setAttributeNames($validator_attributes);

        // エラーがあった場合は入力画面に戻る。
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
                'placeholder_search' => $request->placeholder_search,
                'use_select_flag'   => $request->use_select_flag,
                'use_sort_flag'     => $request->use_sort_flag ? implode(',', $request->use_sort_flag) : null,
                'default_sort_flag' => $request->default_sort_flag,
                'view_count'        => $request->view_count,
                'default_hide'      => $request->default_hide,
                'use_filter_flag'   => $request->use_filter_flag,
                'filter_search_keyword' => $request->filter_search_keyword,
                'filter_search_columns' => json_encode($request->filter_search_columns),
            ]
        );

        return $this->editView($request, $page_id, $frame_id);
    }

    /**
     * 登録データ行の取得
     */
    private function getDatabasesInputs($id)
    {
        return $this->getPost($id);
    }

    /**
     * 承認
     */
    public function approval($request, $page_id = null, $frame_id = null, $id = null)
    {
        // 登録データ行の取得
        $databases_inputs = $this->getDatabasesInputs($id);

        // 承認済みの判定のために、保存する前に初回確定日時を退避しておく。
        $before_databases_inputs = clone $databases_inputs;

        // データがあることを確認
        if (empty($databases_inputs->id)) {
            return;
        }

        // 更新されたら、行レコードの updated_at を更新したいので、update()
        $databases_inputs->updated_at = now();
        $databases_inputs->status = StatusType::active;  // 公開
        $databases_inputs->update();

        // メール送信 引数(レコードを表すモデルオブジェクト, 保存前のレコード, 詳細表示メソッド)
        $this->sendPostNotice($databases_inputs, $before_databases_inputs, 'detail');

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

        // 全ての「カラム」と「表示設定の絞り込み条件」の取得
        $columns = DatabasesTool::getDatabasesColumnsAndFilterSearchAll();
        $columns = $columns->get();

        // 権限によって非表示columのdatabases_columns_id配列を取得する（各データベースの項目毎で権限によって非表示）
        $hide_columns_ids = (new DatabasesTool())->getHideColumnsIds($columns, 'list_detail_display_flag');

        // 各データベースのフレームの表示設定
        $databases_frames_settings = DatabasesTool::getDatabasesFramesSettings($columns);


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
        $inputs_query = DatabasesInputs::
            select(
                'frames.page_id                as page_id',
                'frames.id                     as frame_id',
                'databases_inputs.id           as post_id,',
                'databases_input_cols.value    as post_title,',
                DB::raw('null                  as post_detail'),
                DB::raw('null                  as important'),
                'databases_inputs.posted_at    as posted_at',
                'databases_inputs.created_name as posted_name',
                DB::raw('null                  as classname'),
                DB::raw('null                  as category'),
                DB::raw('"databases"           as plugin_name')
            )
            ->join('databases', 'databases.id', '=', 'databases_inputs.databases_id')
            ->join('frames', 'frames.bucket_id', '=', 'databases.bucket_id')
            ->leftJoin('databases_columns', function ($leftJoin) use ($hide_columns_ids) {
                $leftJoin->on('databases_inputs.databases_id', '=', 'databases_columns.databases_id')
                            ->where('databases_columns.title_flag', 1)
                            // タイトル指定しても、権限によって非表示columだったらvalue表示しない（基本的に、タイトル指定したけど権限で非表示は、設定ミスと思う。その時は(無題)で表示される）
                            ->whereNotIn('databases_columns.id', $hide_columns_ids);
            })
            ->leftJoin('databases_input_cols', function ($leftJoin) {
                $leftJoin->on('databases_inputs.id', '=', 'databases_input_cols.databases_inputs_id')
                            ->on('databases_columns.id', '=', 'databases_input_cols.databases_columns_id');
            })
            ->where('databases_inputs.status', StatusType::active)
            ->where('databases_inputs.posted_at', '<=', Carbon::now());

        // 全データベースの検索キーワードの絞り込み と カラムの絞り込み
        $inputs_query = DatabasesTool::appendSearchKeywordAndSearchColumnsAllDb(
            'databases_inputs.id',
            $inputs_query,
            $databases_frames_settings,
            $hide_columns_ids
        );

        $return[] = $inputs_query;
        $return[] = 'show_page_frame_post';
        $return[] = '/plugin/databases/detail';

        return $return;
    }

    /**
     *  検索用メソッド
     */
    public static function getSearchArgs($search_keyword, $page_ids = null)
    {

        // 全ての「カラム」と「表示設定の絞り込み条件」の取得
        $columns = DatabasesTool::getDatabasesColumnsAndFilterSearchAll();
        $columns = $columns->get();

        // 権限によって非表示columのdatabases_columns_id配列を取得する（各データベースの項目毎で権限によって非表示）
        $hide_columns_ids = (new DatabasesTool())->getHideColumnsIds($columns, 'list_detail_display_flag');

        // 各データベースのフレームの表示設定
        $databases_frames_settings = DatabasesTool::getDatabasesFramesSettings($columns);
        // Query Builder のバグ？
        // whereIn で指定した引数が展開されずに、引数の変数分だけ、setBindings の引数を要求される。
        // そのため、whereIn とsetBindings 用の変数に同じ $page_ids を設定している。
        $query = DB::table('databases_inputs')
                   ->select(
                       'databases_inputs.id         as post_id',
                       'frames.id                   as frame_id',
                       'frames.page_id              as page_id',
                       'pages.permanent_link        as permanent_link',
                       'databases_input_cols.value  as post_title',
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
                   ->leftJoin('databases_columns', function ($leftJoin) use ($hide_columns_ids) {
                        $leftJoin->on('databases_inputs.databases_id', '=', 'databases_columns.databases_id')
                                    ->where('databases_columns.title_flag', 1)
                                    // タイトル指定しても、権限によって非表示columだったらvalue表示しない（基本的に、タイトル指定したけど権限で非表示は、設定ミスと思う。その時は(無題)で表示される）
                                    ->whereNotIn('databases_columns.id', $hide_columns_ids);
                   })
                   ->leftJoin('databases_input_cols', function ($leftJoin) {
                        $leftJoin->on('databases_inputs.id', '=', 'databases_input_cols.databases_inputs_id')
                                    ->on('databases_columns.id', '=', 'databases_input_cols.databases_columns_id');
                   })
                    ->where('databases_inputs.status', StatusType::active)
                    ->where('databases_inputs.posted_at', '<=', Carbon::now())
                    ->whereIn('pages.id', $page_ids);
    
            // 全データベースの検索キーワードの絞り込み と カラムの絞り込み
            $query = DatabasesTool::appendSearchKeywordAndSearchColumnsAllDb(
                'databases_inputs.id',
                $query,
                $databases_frames_settings,
                $hide_columns_ids
            );
            
            // キーワード検索
            $query = DatabasesTool::appendSearchKeyword(
                'databases_inputs.id',
                $query,
                $columns->pluck('id'),
                $hide_columns_ids,
                $search_keyword
            );

        $return[] = $query;
        $return[] = 'show_page_frame_post';
        $return[] = '/plugin/databases/detail';

        return $return;
    }
}
