<?php

namespace App\Plugins\User\Databases;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

use DB;

use App\Models\Common\Buckets;
use App\Models\Common\Frame;
use App\Models\Common\Page;
use App\Models\Common\Uploads;
use App\Models\User\Databases\Databases;
use App\Models\User\Databases\DatabasesColumns;
use App\Models\User\Databases\DatabasesColumnsSelects;
use App\Models\User\Databases\DatabasesFrames;
use App\Models\User\Databases\DatabasesInputs;
use App\Models\User\Databases\DatabasesInputCols;

use App\Rules\CustomVali_AlphaNumForMultiByte;
use App\Rules\CustomVali_CheckWidthForString;

use App\Mail\ConnectMail;
use App\Plugins\User\UserPluginBase;

/**
 * データベース・プラグイン
 *
 * データベースの作成＆データ収集用プラグイン。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>, 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category データベース・プラグイン
 * @package Contoroller
 */
class DatabasesPlugin extends UserPluginBase
{
    /* オブジェクト変数 */

    /* コアから呼び出す関数 */

    /**
     *  関数定義（コアから呼び出す）
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
            'publicConfirm', 
            'publicStore', 
            'cancel', 
            'updateColumn',
            'updateColumnSequence',
            'updateColumnDetail',
            'addSelect',
            'updateSelect',
            'updateSelectSequence',
            'deleteSelect',
            'saveView',
            'search',
        ];
        return $functions;
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
     *  ※まとめ行の設定が不正な場合はリテラル「frame_setting_error」を返す
     *  ※データベース設定で「登録者にメール送信あり」設定にも関わらず、項目内にメールアドレス型が存在しない場合はリテラル「mail_setting_error」を返す
     */
    private function getDatabasesColumns($database)
    {
        // データベースのカラムデータ
        $database_columns = [];
        if ( !empty($database) ) {
            $databases_columns = DatabasesColumns::where('databases_id', $database->id)->orderBy('display_sequence')->get();
            if($database->user_mail_send_flag == '1' && empty($databases_columns->where('column_type', \DatabaseColumnType::mail)->first())){
                return 'mail_setting_error';
            }
        }

        // カラムデータがない場合
        if (empty($databases_columns)) {
            return null;
        }

        // グループがあれば、結果配列をネストする。
        $ret_array = array();
        for ($i = 0; $i < count($databases_columns); $i++) {
            if ($databases_columns[$i]->column_type == "group") {

                $tmp_group = $databases_columns[$i];
                $group_row = array();
                for ($j = 1; $j <= $databases_columns[$i]->frame_col; $j++) {
                    // dd(count($databases_columns), $i, $j);
                    if(count($databases_columns) >= (1 + $i + $j)){
                        $group_row[] = $databases_columns[$i + $j];
                    }else{
                        return 'frame_setting_error';
                    }
                }
                $tmp_group->group = $group_row;

                $ret_array[] = $tmp_group;
                $i = $i + $databases_columns[$i]->frame_col;
            }
            else {
                $ret_array[] = $databases_columns[$i];
            }
        }

        return $ret_array;
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
        foreach($databases_columns_selects as $databases_columns_select) {

            if ( $before_databases_columns_id != $databases_columns_select->databases_columns_id ) {
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
                 ->select('frames.*', 'databases.id as databases_id', 'databases_frames.id as databases_frames_id', 'use_search_flag', 'use_select_flag', 'use_sort_flag', 'view_count', 'default_hide')
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
        $input_cols = DatabasesInputCols::select('databases_input_cols.*', 'databases_columns.column_type', 'databases_columns.column_name', 'uploads.client_original_name')
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
                                     ->whereIn('databases_columns.column_type', ['file','image','video'])
                                     ->orderBy('databases_inputs_id', 'asc')
                                     ->orderBy('databases_columns_id', 'asc')
                                     ->get();

        // 後でこのCollection から要素を削除する可能性がある。
        // そのため、カラムを特定できるように、カラムをキーにして詰め替える。
        $uploads = collect();
        foreach($records as $record) {
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

        // Databases、Frame データ
        $database = $this->getDatabases($frame_id);


        $setting_error_messages = null;
        $databases_columns = null;
        $databases_columns_id_select = null;
        if ($database) {
            $databases_columns_id_select = $this->getDatabasesColumnsSelects($database->id);
            if(DatabasesColumns::query()
                ->where('databases_id', $database->id)
                ->where('column_type', \DatabaseColumnType::group)
                ->whereNull('frame_col')
                ->get()
                ->count() > 0){
                // データ型が「まとめ行」で、まとめ数の設定がないデータが存在する場合
                $setting_error_messages[] = 'フレームの設定画面から、項目データ（まとめ行のまとめ数）を設定してください。';
            }

            /**
             * データベースのカラムデータを取得
             * ※まとめ行の設定が不正な場合はリテラル「frame_setting_error」が返る
             * ※データベース設定で「登録者にメール送信あり」設定にも関わらず、項目内にメールアドレス型が存在しない場合はリテラル「mail_setting_error」が返る
             */
            $databases_columns = $this->getDatabasesColumns($database);

            if($databases_columns == 'frame_setting_error'){
                // 項目データはあるが、まとめ行の設定（まとめ行の位置とまとめ数の設定）が不正な場合
                $setting_error_messages[] = 'まとめ行の設定が不正です。フレームの設定画面からまとめ行の位置、又は、まとめ数の設定を見直してください。';
            }elseif($databases_columns == 'mail_setting_error'){
                // データベース設定で「登録者にメール送信あり」設定にも関わらず、項目内にメールアドレス型が存在しない場合
                $setting_error_messages[] = 'メールアドレス型の項目を設定してください。（データベースの設定「登録者にメール送信する」と関連）';
            }elseif(!$databases_columns){
                // 項目データがない場合
                $setting_error_messages[] = 'フレームの設定画面から、項目データを作成してください。';
            }
        }else{
            // フレームに紐づくデータベース親データがない場合
            $setting_error_messages[] = 'フレームの設定画面から、使用するデータベースを選択するか、作成してください。';
        }


//--- 初期表示データ

        if (empty($database)) {
            $databases = null;
            $columns = null;
            $inputs = null;
            $input_cols = null;
        }
        else {

            // データベースの取得
            $databases = Databases::where('id', $database->id)->first();

            // フレーム毎のデータベース設定の取得
            $databases_frames = DatabasesFrames::where('frames_id', $frame_id)->where('databases_id', $database->id)->first();

            // カラムの取得
            $columns = DatabasesColumns::where('databases_id', $database->id)->orderBy('display_sequence', 'asc')->get();

            // 登録データ行の取得 --->
            $inputs_query = DatabasesInputs::where('databases_id', $database->id);

            // キーワード指定の追加
            if (!empty(session('search_keyword'))) {
                $inputs_query->whereIn('id', function($query) {
                               // 縦持ちのvalue を検索して、行の id を取得。search_flag で対象のカラムを絞る。
                               $query->select('databases_inputs_id')
                                     ->from('databases_input_cols')
                                     ->join('databases_columns', 'databases_columns.id', '=', 'databases_input_cols.databases_columns_id')
                                     ->where('databases_columns.search_flag', 1)
                                     ->where('value', 'like', '%' . session('search_keyword') . '%')
                                     ->groupBy('databases_inputs_id');
                               });
            }
            // 絞り込み指定の追加
            if (!empty(session('search_column'))) {
                foreach(session('search_column') as $search_column) {
                    if ($search_column && $search_column['columns_id'] && $search_column['value']) {

                        $inputs_query->whereIn('id', function($query) use($search_column) {
                               // 縦持ちのvalue を検索して、行の id を取得。column_id で対象のカラムを絞る。
                               $query->select('databases_inputs_id')
                                     ->from('databases_input_cols')
                                     ->join('databases_columns', 'databases_columns.id', '=', 'databases_input_cols.databases_columns_id')
                                     ->where('databases_columns_id', $search_column['columns_id']);

                               if ($search_column['where'] == 'PART') {
                                   $query->where('value', 'LIKE', '%' . $search_column['value'] . '%');
                               }
                               else {
                                   $query->where('value', $search_column['value']);
                               }
                               $query->groupBy('databases_inputs_id');
                       });
                    }
                }
            }
            // 取得
            $get_count = 10;
            if ($databases_frames) {
                $get_count = $databases_frames->view_count;
            }
            $inputs = $inputs_query->orderBy('id', 'asc')->paginate($get_count);
            // <--- 登録データ行の取得


            // 登録データ詳細の取得
            $input_cols = DatabasesInputCols::select('databases_input_cols.*', 'uploads.client_original_name')
                                            ->leftJoin('uploads', 'uploads.id', '=', 'databases_input_cols.value')
                                            ->whereIn('databases_inputs_id', $inputs->pluck('id'))
                                            ->orderBy('databases_inputs_id', 'asc')->orderBy('databases_columns_id', 'asc')
                                            ->get();

            // カラム選択肢の取得
            $columns_selects = DatabasesColumnsSelects::whereIn('databases_columns_id', $columns->pluck('id'))->orderBy('display_sequence', 'asc')->get();
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
            'databases', [
            'request'  => $request,
            'frame_id' => $frame_id,
            'database' => $database,
            'databases_columns' => $databases_columns,
            'databases_columns_id_select' => $databases_columns_id_select,
            'errors' => $errors,
            'setting_error_messages' => $setting_error_messages,

            'databases'       => $databases,
            'database_frame'  => $database_frame,
            'columns'         => $columns,
            'inputs'          => $inputs,
            'input_cols'      => $input_cols,
            'columns_selects' => isset($columns_selects) ? $columns_selects : null,
            'default_hide_list' => $default_hide_list,

        ])->withInput($request->all);
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
            session(['search_keyword' => $request->search_keyword]);

            // 絞り込み
            session(['search_column' => $request->search_column]);
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
        $inputs = DatabasesInputs::where('id', $id)->first();

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

        // データ詳細の取得
        $input_cols = $this->getDatabasesInputCols($id);

        // 表示する画面
        if ($mode == 'edit') {
            $blade = 'databases_edit';
        }
        else {
            $blade = 'databases_detail';
        }

        // 表示テンプレートを呼び出す。
        return $this->view(
            $blade, [
            'frame_id'   => $frame_id,
            'database'   => $database,
            'columns'    => $columns,
            'inputs'     => $inputs,
            'input_cols' => $input_cols,

        ])->withInput($request->all);
    }

    /**
     *  新規記事画面
     */
    public function input($request, $page_id, $frame_id, $id = null, $errors = null)
    {
        // 権限チェック（まずはモデレータでチェック）
        if ($this->can('role_article')) {
            return $this->view_error("403_inframe", null, '関数実行権限がありません。');
        }

        // セッション初期化などのLaravel 処理。
        $request->flash();

        // Databases、Frame データ
        $database = $this->getDatabases($frame_id);

        // データベースのカラムデータ ※ まとめ行の設定が不正な場合はリテラル「frame_setting_error」が返る
        $databases_columns = $this->getDatabasesColumns($database);

        // カラムの選択肢用データ
        $databases_columns_id_select = null;
        $databases_columns_errors = null;
        if ($database) {
            $databases_columns_id_select = $this->getDatabasesColumnsSelects($database->id);
            // データ型が「まとめ行」、且つ、まとめ数の設定がないデータを取得
            $databases_columns_errors = DatabasesColumns::query()->where('databases_id', $database->id)->where('column_type', \DatabaseColumnType::group)->whereNull('frame_col')->get();
        }

        // データ詳細の取得
        if (empty($id)) {
            $input_cols = null;
        }
        else {
            // データ詳細の取得
            $input_cols = $this->getDatabasesInputCols($id);
        }

        // 表示テンプレートを呼び出す。
        return $this->view(
            'databases_input', [
            'request'  => $request,
            'frame_id' => $frame_id,
            'id'       => $id,
            'database' => $database,
            'databases_columns' => $databases_columns,
            'databases_columns_id_select' => $databases_columns_id_select,
            'databases_columns_errors' => $databases_columns_errors,
            'input_cols'  => $input_cols,
            'errors'      => $errors,
        ])->withInput($request->all);
    }

    /**
     * （再帰関数）入力値の前後をトリムする
     *
     * @param $request
     * @return void
     */
    public static function trimInput($value){
        if (is_array($value)){
            // 渡されたパラメータが配列の場合（radioやcheckbox等）の場合を想定
            $value = array_map(['self', 'trimInput'], $value);
        }elseif (is_string($value)){
            $value = preg_replace('/(^\s+)|(\s+$)/u', '', $value);
        }
 
        return $value;
    }

    /**
     * セットすべきバリデータールールが存在する場合、受け取った配列にセットして返す
     *
     * @param [array] $validator_array 二次元配列
     * @param [App\Models\User\Databases\DatabasesColumns] $databases_column
     * @return void
     */
    private function getValidatorRule($validator_array, $databases_column){

        $validator_rule = null;
        // 必須チェック
        if ($databases_column->required) {
            $validator_rule[] = 'required';
        }
        // メールアドレスチェック
        if ($databases_column->column_type == \DatabaseColumnType::mail) {
            $validator_rule[] = 'email';
        }
        // 数値チェック
        if ($databases_column->rule_allowed_numeric) {
            $validator_rule[] = 'numeric';
        }
        // 英数値チェック
        if ($databases_column->rule_allowed_alpha_numeric) {
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
            $validator_rule[] = 'max:' . $databases_column->rule_max;
        }
        // 最小値チェック
        if ($databases_column->rule_min) {
            $validator_rule[] = 'min:' . $databases_column->rule_min;
        }
        // ～日以降を許容
        if ($databases_column->rule_date_after_equal) {
            $comparison_date = \Carbon::now()->addDay($databases_column->rule_date_after_equal)->databaseat('Y/m/d');
            $validator_rule[] = 'after_or_equal:' . $comparison_date;
        }
        // バリデータールールをセット
        if($validator_rule){
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
        // 権限チェック（まずはモデレータでチェック）
        if ($this->can('role_article')) {
            return $this->view_error("403_inframe", null, '関数実行権限がありません。');
        }

        // Databases、Frame データ
        $database = $this->getDatabases($frame_id);

        // データベースのカラムデータ
        $databases_columns = $this->getDatabasesColumns($database);

        // ファイル系の詳細データ
        $uploads = collect();
        if ($id) {
            $uploads = $this->getUploadsInputCols($id);
        }

        // エラーチェック配列
        $validator_array = array( 'column' => array(), 'message' => array());

        foreach($databases_columns as $databases_column) {
            // まとめ行であれば、ネストされた配列をさらに展開
            if ($databases_column->group) {
                foreach($databases_column->group as $group_item) {
                    // まとめ行で指定している項目について、バリデータールールをセット
                    $validator_array = self::getValidatorRule($validator_array, $group_item);
                }
            }
            // まとめ行以外の項目について、バリデータールールをセット
            $validator_array = self::getValidatorRule($validator_array, $databases_column);
        }

        // 入力値をトリム
        $request->merge(self::trimInput($request->all()));

        // 項目のエラーチェック
        $validator = Validator::make($request->all(), $validator_array['column']);
        $validator->setAttributeNames($validator_array['message']);

        // エラーがあった場合は入力画面に戻る。
        $message = null;
        if ($validator->fails()) {
            return $this->input($request, $page_id, $frame_id, $id, $validator->errors());
        }

        // ファイル関連の変数
        if ($request->has('delete_upload_column_ids')) {
            $delete_upload_column_ids = $request->delete_upload_column_ids;  // 画面で削除のチェックがされたupload_id
        }
        else {
            $delete_upload_column_ids = array();  // 削除や変更で後で削除するファイルのupload_id
        }

        // ファイル項目を探して保存
        foreach($databases_columns as $databases_column) {
            if (($databases_column->column_type == 'file')  ||
                ($databases_column->column_type == 'image') ||
                ($databases_column->column_type == 'video')) {

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
                }
                // ファイルがアップロードされていない
                else {

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
        ]);
    }

    /**
     * データ登録
     */
    public function publicStore($request, $page_id, $frame_id, $id = null)
    {

        // 権限チェック（まずはモデレータでチェック）
        if ($this->can('role_article')) {
            return $this->view_error("403_inframe", null, '関数実行権限がありません。');
        }

        // Databases、Frame データ
        $database = $this->getDatabases($frame_id);

        // 変更の場合（行 idが渡ってきたら）、既存の行データを使用。新規の場合は行レコード取得
        if (empty($id)) {
            $databases_inputs = new DatabasesInputs();
            $databases_inputs->databases_id = $database->id;
            $databases_inputs->save();
        }
        else {
            $databases_inputs = DatabasesInputs::where('id', $id)->first();
        }

        // ファイル（uploadsテーブル＆実ファイル）の削除。データ登録前に削除する。（後からだと内容が変わっていてまずい）
        if (!empty($id) && $request->has('delete_upload_column_ids')) {

            foreach($request->delete_upload_column_ids as $delete_upload_column_id) {
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
        foreach ( $databases_columns as $databases_column ) {
            if ($databases_column->column_type == "group") {
                continue;
            }

            $value = "";
            if (is_array($request->databases_columns_value[$databases_column->id])) {
                $value = implode(',', $request->databases_columns_value[$databases_column->id]);
            }
            else {
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
                if (($databases_column->column_type == "file")  ||
                    ($databases_column->column_type == "image") ||
                    ($databases_column->column_type == "video")) {
                    $uploads_count = Uploads::where('id', $value)->update(['temporary_flag' => 0]);
                }
            }

            // メールの内容
            $contents_text .= $databases_column->column_name . "：" . $value . "\n";

            // メール型
            if ($databases_column->column_type == "mail") {
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
            $mail_text = str_replace( '[[body]]', $contents_text, $mail_databaseat);

            // メール本文内の採番文字列を置換
            $mail_text = str_replace( '[[number]]', $number, $mail_text);

            // メール送信（管理者側）
            $mail_addresses = explode(',', $database->mail_send_address);
            foreach($mail_addresses as $mail_address) {
                Mail::to($mail_address)->send(new ConnectMail(['subject' => $database->mail_subject, 'template' => 'mail.send'], ['content' => $mail_text]));
            }

            // メール送信（ユーザー側）
            foreach($user_mailaddresses as $user_mailaddress) {
                if (!empty($user_mailaddress)) {
                    Mail::to($user_mailaddress)->send(new ConnectMail(['subject' => $database->mail_subject, 'template' => 'mail.send'], ['content' => $mail_text]));
                }
            }
        }

        // 表示テンプレートを呼び出す。
        return $this->index($request, $page_id, $frame_id);
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

        // 権限チェック（まずはモデレータでチェック）
        if ($this->can('role_article')) {
            return $this->view_error("403_inframe", null, '関数実行権限がありません。');
        }

        // 行 idがなければ終了
        if (empty($id)) {
            // 表示テンプレートを呼び出す。
            return $this->index($request, $page_id, $frame_id);
        }

        // ファイル型の調査のため、詳細カラムデータを取得
        $input_cols = $this->getDatabasesInputCols($id);

        // ファイル型のファイル、uploads テーブルを削除
        foreach($input_cols as $input_col) {
            if (($input_col->column_type == 'file') ||
                ($input_col->column_type == 'image') ||
                ($input_col->column_type == 'video')) {

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
        }
        else {
            $databases_inputs = DatabasesInputs::where('id', $id)->first();
        }

        // ファイル（uploadsテーブル＆実ファイル）の削除。データ登録前に削除する。（後からだと内容が変わっていてまずい）
        if (!empty($id) && $request->has('delete_upload_column_ids')) {

            foreach($request->delete_upload_column_ids as $delete_upload_column_id) {
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
        foreach ( $databases_columns as $databases_column ) {
            if ($databases_column->column_type == "group") {
                continue;
            }

            $value = "";
            if (is_array($request->databases_columns_value[$databases_column->id])) {
                $value = implode(',', $request->databases_columns_value[$databases_column->id]);
            }
            else {
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
                if ($databases_column->column_type == "file") {
                    $uploads_count = Uploads::where('id', $value)->update(['temporary_flag' => 0]);
                }
            }

            // メールの内容
            $contents_text .= $databases_column->column_name . "：" . $value . "\n";

            // メール型
            if ($databases_column->column_type == "mail") {
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
            $mail_text = str_replace( '[[body]]', $contents_text, $mail_databaseat);

            // メール本文内の採番文字列を置換
            $mail_text = str_replace( '[[number]]', $number, $mail_text);

            // メール送信（管理者側）
            $mail_addresses = explode(',', $database->mail_send_address);
            foreach($mail_addresses as $mail_address) {
                Mail::to($mail_address)->send(new ConnectMail(['subject' => $database->mail_subject, 'template' => 'mail.send'], ['content' => $mail_text]));
            }

            // メール送信（ユーザー側）
            foreach($user_mailaddresses as $user_mailaddress) {
                if (!empty($user_mailaddress)) {
                    Mail::to($user_mailaddress)->send(new ConnectMail(['subject' => $database->mail_subject, 'template' => 'mail.send'], ['content' => $mail_text]));
                }
            }
        }

        // 表示テンプレートを呼び出す。
        return $this->index($request, $page_id, $frame_id);
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
                       ->paginate(10);

        // 表示テンプレートを呼び出す。
        return $this->view(
            'databases_datalist', [
            'plugin_frame' => $plugin_frame,
            'plugins'      => $plugins,
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
        }
        // Frame のbucket_id があれば、bucket_id からデータベースデータ取得、なければ、新規作成か選択へ誘導
        else if (!empty($database_frame->bucket_id) && $create_flag == false) {
            $database = Databases::where('bucket_id', $database_frame->bucket_id)->first();
        }

        // 表示テンプレートを呼び出す。
        return $this->view(
            'databases_edit_database', [
            'database_frame'  => $database_frame,
            'database'        => $database,
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
        if($request->mail_send_flag){
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
            }
            else  {
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
            $bucket->bucket_name = '無題';
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

            $message = 'データベース設定を追加しました。<br />　 データベースで使用する項目を設定してください。［ <a href="/plugin/databases/editColumn/' . $page_id . '/' . $frame_id . '/">項目設定</a> ］';
        }
        // databases_id があれば、データベースを更新
        else {

            // データベースデータ取得
            $databases = Databases::where('id', $request->databases_id)->first();

            $message = 'データベース設定を変更しました。';
        }

        // データベース設定
        $databases->databases_name          = $request->databases_name;
        $databases->mail_send_flag      = (empty($request->mail_send_flag))      ? 0 : $request->mail_send_flag;
        $databases->mail_send_address   = $request->mail_send_address;
        $databases->user_mail_send_flag = (empty($request->user_mail_send_flag)) ? 0 : $request->user_mail_send_flag;
        $databases->from_mail_name      = $request->from_mail_name;
        $databases->mail_subject        = $request->mail_subject;
        $databases->mail_databaseat         = $request->mail_databaseat;
        $databases->data_save_flag      = (empty($request->data_save_flag))      ? 0 : $request->data_save_flag;
        $databases->after_message       = $request->after_message;
        $databases->numbering_use_flag  = (empty($request->numbering_use_flag))      ? 0 : $request->numbering_use_flag;
        $databases->numbering_prefix   = $request->numbering_prefix;

        // データ保存
        $databases->save();

        // 新規作成フラグを付けてデータベース設定変更画面を呼ぶ
        $create_flag = false;

        return $this->editBuckets($request, $page_id, $frame_id, $databases_id, $create_flag, $message);
    }

    /**
     *  データベース削除処理
     */
    public function destroyBuckets($request, $page_id, $frame_id, $databases_id)
    {
        // databases_id がある場合、データを削除
        if ( $databases_id ) {

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
        $request->session()->forget('databases');

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
        if($errors){
            // エラーあり：入力値をフラッシュデータとしてセッションへ保存
            $request->flash();
        }else{
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
        $column = DatabasesColumns::query()->where('id', $column_id)->first();
        $selects = DatabasesColumnsSelects::query()->where('databases_columns_id', $column->id)->orderBy('display_sequence', 'asc')->get();

        // 編集画面テンプレートを呼び出す。
        return $this->view(
            'databases_edit_row_detail', 
            [
                'databases_id' => $databases_id,
                'column'     => $column,
                'selects'     => $selects,
                'message'     => $message,
                'errors'     => $errors,
            ]
        );
    }

    /**
     * カラム編集画面の表示
     */
    public function editColumn($request, $page_id, $frame_id, $id = null, $message = null, $errors = null)
    {
        if($errors){
            // エラーあり：入力値をフラッシュデータとしてセッションへ保存
            $request->flash();
        }else{
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
                'databases_columns.caption',
                'databases_columns.caption_color',
                'databases_columns.display_sequence',
                DB::raw('count(databases_columns_selects.id) as select_count'),
                DB::raw('GROUP_CONCAT(databases_columns_selects.value order by databases_columns_selects.display_sequence SEPARATOR \',\') as select_names')
            )
            ->where('databases_columns.databases_id', $databases_id)
            // 予約項目の子データ（選択肢）
            ->leftjoin('databases_columns_selects',function($join) {
                $join->on('databases_columns.id','=','databases_columns_selects.databases_columns_id');
            })
            ->groupby(
                'databases_columns.id',
                'databases_columns.databases_id',
                'databases_columns.column_type',
                'databases_columns.column_name',
                'databases_columns.required',
                'databases_columns.frame_col',
                'databases_columns.caption',
                'databases_columns.caption_color',
                'databases_columns.display_sequence'
            )
            ->orderby('databases_columns.display_sequence')
            ->get();

        // 編集画面テンプレートを呼び出す。
        return $this->view(
            'databases_edit', 
            [
                'databases_id'   => $databases_id,
                'columns'    => $columns,
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

        // 項目の削除
        DatabasesColumns::query()->where('id', $request->column_id)->delete();
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
        $column = DatabasesColumns::query()->where('id', $request->column_id)->first();

        $validator_values = null;
        $validator_attributes = null;

        // データ型が「まとめ行」の場合はまとめ数について必須チェック
        if($column->column_type == \DatabaseColumnType::group){
            $validator_values['frame_col'] = [
                'required'
            ];
            $validator_attributes['frame_col'] = 'まとめ数';
        }
        // 桁数チェックの指定時、入力値が数値であるかチェック
        if($request->rule_digits_or_less){
            $validator_values['rule_digits_or_less'] = [
                'numeric',
            ];
            $validator_attributes['rule_digits_or_less'] = '入力桁数';
        }
        // 最大値の指定時、入力値が数値であるかチェック
        if($request->rule_max){
            $validator_values['rule_max'] = [
                'numeric',
            ];
            $validator_attributes['rule_max'] = '最大値';
        }
        // 最小値の指定時、入力値が数値であるかチェック
        if($request->rule_min){
            $validator_values['rule_min'] = [
                'numeric',
            ];
            $validator_attributes['rule_min'] = '最小値';
        }
        // 入力文字数の指定時、入力値が数値であるかチェック
        if($request->rule_word_count){
            $validator_values['rule_word_count'] = [
                'numeric',
            ];
            $validator_attributes['rule_word_count'] = '入力文字数';
        }
        // ～日以降許容を指定時、入力値が数値であるかチェック
        if($request->rule_date_after_equal){
            $validator_values['rule_date_after_equal'] = [
                'numeric',
            ];
            $validator_attributes['rule_date_after_equal'] = '～日以降を許容';
        }

        // エラーチェック
        if($validator_values){
            $validator = Validator::make($request->all(), $validator_values);
            $validator->setAttributeNames($validator_attributes);

            $errors = null;
            if ($validator->fails()) {

                // エラーと共に編集画面を呼び出す
                $errors = $validator->errors();
                return $this->editColumnDetail($request, $page_id, $frame_id, $request->column_id, null, $errors);
            }
        }

        // 項目の更新処理
        $column->caption = $request->caption;
        $column->caption_color = $request->caption_color;
        $column->frame_col = $request->frame_col;
        // 分刻み指定
        if($column->column_type == \DatabaseColumnType::time){
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
        // 並べ替え指定
        $column->sort_flag = (empty($request->sort_flag)) ? 0 : $request->sort_flag;
        // 検索対象指定
        $column->search_flag = (empty($request->search_flag)) ? 0 : $request->search_flag;
        // 絞り込み対象指定
        $column->select_flag = (empty($request->select_flag)) ? 0 : $request->select_flag;

        // 保存
        $column->save();
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
    public function downloadCsv($request, $page_id, $frame_id, $id)
    {

        // id で対象のデータの取得

        // データベースの取得
        $database = Databases::where('id', $id)->first();

        // カラムの取得
        $columns = DatabasesColumns::where('databases_id', $id)->orderBy('display_sequence', 'asc')->get();

        // 登録データの取得
        $input_cols = DatabasesInputCols::whereIn('databases_inputs_id', DatabasesInputs::select('id')->where('databases_id', $id))
                                      ->orderBy('databases_inputs_id', 'asc')->orderBy('databases_columns_id', 'asc')
                                      ->get();

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
        foreach($columns as $column) {
            $csv_array[0][$column->id] = $column->column_name;
            $copy_base[$column->id] = '';
        }

        // データ
        foreach($input_cols as $input_col) {
            if (!array_key_exists($input_col->databases_inputs_id, $csv_array)) {
                $csv_array[$input_col->databases_inputs_id] = $copy_base;
            }
            $csv_array[$input_col->databases_inputs_id][$input_col->databases_columns_id] = $input_col->value;
        }

        // レスポンス版
        $filename = $database->databases_name . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];
 
        // データ
        $csv_data = '';
        foreach($csv_array as $csv_line) {
            foreach($csv_line as $csv_col) {
                $csv_data .= '"' . $csv_col . '",';
            }
            $csv_data .= "\n";
        }

        // 文字コード変換
        $csv_data = mb_convert_encoding($csv_data, "SJIS-win");

        return response()->make($csv_data, 200, $headers);
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

        // Frames > Buckets > Database で特定
        if (empty($database_frame->bucket_id)) {
            $database = null;
        }
        else {
            $database = Databases::where('bucket_id', $database_frame->bucket_id)->first();
        }

        // 表示テンプレートを呼び出す。
        return $this->view(
            'databases_edit_view', [
            'database_frame' => $database_frame,
            'database'       => $database,
        ])->withInput($request->all);
    }

    /**
     *  表示設定保存処理
     */
    public function saveView($request, $page_id, $frame_id)
    {
        // セッション初期化などのLaravel 処理。
        $request->flash();

        // デフォルトで必須
        $validator_values['view_count'] = ['required'];
        $validator_attributes['view_count'] = '表示件数';

        // 項目のエラーチェック
        $validator = Validator::make($request->all(), $validator_values);
        $validator->setAttributeNames($validator_attributes);

        // エラーがあった場合は入力画面に戻る。
        $message = null;
        if ($validator->fails()) {
            return $this->editView($request, $page_id, $frame_id)->withErrors($validator);
        }

        // 更新後のメッセージ
        $message = null;

        // データベース＆フレームデータ
        $database_frame = $this->getDatabaseFrame($frame_id);

        // 表示設定の保存
        $databases_frames = DatabasesFrames::updateOrCreate(
            ['databases_id'    => $database_frame->databases_id,
             'frames_id'       => $frame_id],
            ['databases_id'    => $database_frame->databases_id,
             'frames_id'       => $frame_id,
             'use_search_flag' => $request->use_search_flag,
             'use_select_flag' => $request->use_select_flag,
             'use_sort_flag'   => $request->use_sort_flag,
             'view_count'      => $request->view_count,
             'default_hide'    => $request->default_hide],
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
                   ->select('databases_inputs.id         as post_id',
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

//        $bind = array($page_ids, 0, '%'.$search_keyword.'%', '%'.$search_keyword.'%');
        $bind = array($page_ids);

        $return[] = $query;
        $return[] = $bind;
        $return[] = 'show_page';
        $return[] = '/page';

/*
        $return[] = DB::table('contents')
                      ->select('contents.id                 as post_id',
                               'frames.id                   as frame_id',
                               'frames.page_id              as page_id',
                               'pages.permanent_link        as permanent_link',
                               'frames.frame_title          as post_title',
                               DB::raw('0 as important'),
                               'contents.created_at         as posted_at',
                               'contents.created_name       as posted_name',
                               DB::raw('null as classname'),
                               DB::raw('null as categories_id'),
                               DB::raw('null as category'),
                               DB::raw('"contents" as plugin_name')
                              )
                      ->join('frames', 'frames.bucket_id', '=', 'contents.bucket_id')
                      ->leftjoin('pages', 'pages.id', '=', 'frames.page_id')
                      ->where('status', '?')

                       ->where(function($plugin_query) use($search_keyword) {
                           $plugin_query->where('contents.content_text', 'like', '?')
                                        ->orWhere('frames.frame_title', 'like', '?');
                       })

                      ->whereNull('contents.deleted_at');


        $bind = array(0, '%'.$search_keyword.'%', '%'.$search_keyword.'%');
        $return[] = $bind;
        $return[] = 'show_page';
        $return[] = '/page';
*/
        return $return;
    }
}
