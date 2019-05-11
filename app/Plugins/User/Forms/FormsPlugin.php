<?php

namespace App\Plugins\User\Forms;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

use DB;

use App\Buckets;
use App\FormsColumns;
use App\Frame;
use App\Page;
use App\Plugins\User\UserPluginBase;

/**
 * フォーム・プラグイン
 *
 * フォームの作成＆データ収集用プラグイン。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category フォーム・プラグイン
 * @package Contoroller
 */
class FormsPlugin extends UserPluginBase
{
    /**
     *  編集画面の最初のタブ
     *
     *  スーパークラスをオーバーライド
     */
    public function getFirstFrameEditAction()
    {
        return "edit";
    }

    /**
     *  データ取得
     */
    public function getForms($frame_id)
    {
        // Forms、Frame データ
        $form = DB::table('forms')
            ->select('forms.*')
            ->join('frames', 'frames.bucket_id', '=', 'forms.bucket_id')
            ->where('frames.id', '=', $frame_id)
            ->first();

        return $form;
    }

    /**
     *  カラムデータ取得
     */
    public function getFormsColumns($form)
    {
        // フォームのカラムデータ
        $form_columns = [];
        if ( !empty($form) ) {
            $forms_columns = FormsColumns::where('forms_id', $form->id)->orderBy('display_sequence')->get();
        }

        // グループがあれば、結果配列をネストする。
        $ret_array = array();
        for ($i = 0; $i < count($forms_columns); $i++) {
            if ($forms_columns[$i]->column_type == "group") {

                $tmp_group = $forms_columns[$i];
                $group_row = array();
                for ($j = 1; $j <= $forms_columns[$i]->frame_col; $j++) {
                    $group_row[] = $forms_columns[$i + $j];
                }
                $tmp_group->group = $group_row;

                $ret_array[] = $tmp_group;
                $i = $i + $forms_columns[$i]->frame_col;
            }
            else {
                $ret_array[] = $forms_columns[$i];
            }
        }

        return $ret_array;
    }

    /**
     *  カラムの選択肢用データ取得
     */
    public function getFormsColumnsSelects($forms_id)
    {
        // カラムの選択肢用データ
        $forms_columns_selects = DB::table('forms_columns_selects')
                                     ->join('forms_columns', 'forms_columns.id', '=', 'forms_columns_selects.forms_columns_id')
                                     ->join('forms', 'forms.id', '=', 'forms_columns.forms_id')
                                     ->select('forms_columns_selects.*')
                                     ->where('forms.id', '=', $forms_id)
                                     ->get();
        // カラムID毎に詰めなおし
        $forms_columns_id_select = array();
        $index = 1;
        $before_forms_columns_id = null;
        foreach($forms_columns_selects as $forms_columns_select) {

            if ( $before_forms_columns_id != $forms_columns_select->forms_columns_id ) {
                $index = 1;
                $before_forms_columns_id = $forms_columns_select->forms_columns_id;
            }

            $forms_columns_id_select[$forms_columns_select->forms_columns_id][$index]['value'] = $forms_columns_select->value;
            $index++;
        }

        return $forms_columns_id_select;
    }

    /**
     *  データ初期表示関数
     *  コアがページ表示の際に呼び出す関数
     */
    public function index($request, $page_id, $frame_id, $errors = null)
    {

//Log::debug(print_r($request->forms_columns_value, true));

        // セッション初期化などのLaravel 処理。
        $request->flash();

        // Forms、Frame データ
        $form = $this->getForms($frame_id);

        // フォームのカラムデータ
        $forms_columns = $this->getFormsColumns($form);
//Log::debug($forms_columns);
//        $form_columns = [];
//        if ( !empty($form) ) {
//            $forms_columns = FormsColumns::where('forms_id', $form->id)->orderBy('display_sequence')->get();
//        }

        // カラムの選択肢用データ
        $forms_columns_id_select = $this->getFormsColumnsSelects($form->id);



// 階層表現の途中
//Log::debug(print_r(json_decode($forms_columns), true));
/*
        $ret_array = array();
        for ($i = 0; $i < count($forms_columns); $i++) {
            if ($forms_columns[$i]->column_type == "group") {

                $tmp_group = $forms_columns[$i];
                $group_row = array();
                for ($j = 1; $j <= $forms_columns[$i]->frame_col; $j++) {
                    $group_row[] = $forms_columns[$i + $j];
                }
                $tmp_group->group = $group_row;

                $ret_array[] = $tmp_group;
                $i = $i + $forms_columns[$i]->frame_col;
            }
            else {
                $ret_array[] = $forms_columns[$i];
            }
        }
//Log::debug(print_r($ret_array, true));
*/



        // 表示テンプレートを呼び出す。
        return $this->view(
            'forms', [
            'request' => $request,
            'frame_id' => $frame_id,
            'form' => $form,
//            'forms_columns' => $ret_array,
            'forms_columns' => $forms_columns,
            'forms_columns_id_select' => $forms_columns_id_select,
            'errors'      => $errors,
        ])->withInput($request->all);
    }

    /**
     * 登録時の確認
     */
    public function confirm($request, $page_id, $frame_id, $id = null)
    {
        // Forms、Frame データ
        $form = $this->getForms($frame_id);

        // フォームのカラムデータ
//        $forms_columns = FormsColumns::where('forms_id', $form->id)->orderBy('display_sequence')->get();

        // フォームのカラムデータ
        $forms_columns = $this->getFormsColumns($form);

        // エラーチェック配列
        $validator_array = array( 'column' => array(), 'message' => array());

//Log::debug($forms_columns);
        foreach($forms_columns as $forms_column) {
            // グループ内
            if ($forms_column->group) {
                foreach($forms_column->group as $group_item) {

                    if ($group_item->required) {
                        $validator_array['column']['forms_columns_value.' . $group_item->id] = ['required'];
                        $validator_array['message']['forms_columns_value.' . $group_item->id] = $group_item->column_name;
                    }
                }
            }
            // グループではないもの
            if ($forms_column->required) {
                $validator_array['column']['forms_columns_value.' . $forms_column->id] = ['required'];
                $validator_array['message']['forms_columns_value.' . $forms_column->id] = $forms_column->column_name;
            }
        }

        // 項目のエラーチェック
        $validator = Validator::make($request->all(), $validator_array['column']);
        $validator->setAttributeNames($validator_array['message']);

        // エラーがあった場合は入力画面に戻る。
        $message = null;
        if ($validator->fails()) {
            return $this->index($request, $page_id, $frame_id, $validator->errors());
        }



//Log::debug($forms_columns);
        // 表示テンプレートを呼び出す。
        return $this->view(
            'forms_confirm', [
            'request' => $request,
            'frame_id' => $frame_id,
            'form' => $form,
            'forms_columns' => $forms_columns,
        ]);
    }

    /**
     * データ登録
     */
    public function store($request, $page_id, $frame_id, $id = null)
    {
        // Forms、Frame データ
        $form = $this->getForms($frame_id);

        // forms_inputs 登録
        $forms_inputs_id = DB::table('forms_inputs')->insertGetId([
            'forms_id' => $form->id,
        ]);

        // フォームのカラムデータ
        $forms_columns = FormsColumns::where('forms_id', $form->id)->orderBy('display_sequence')->get();

        // forms_input_cols 登録
        foreach ( $forms_columns as $forms_column ) {
            if ($forms_column->column_type == "group") {
                continue;
            }

            $value = "";
            if (is_array($request->forms_columns_value[$forms_column->id])) {
                $value = implode(',', $request->forms_columns_value[$forms_column->id]);
            }
            else {
                $value = $request->forms_columns_value[$forms_column->id];
            }
            DB::table('forms_input_cols')->insertGetId([
                'forms_inputs_id' => $forms_inputs_id,
                'forms_columns_id' => $forms_column['id'],
                'value' => $value,
            ]);
        }

        // 表示テンプレートを呼び出す。
        return $this->view(
            'forms_thanks', [
        ]);
    }

    /**
     * 編集画面の表示関数
     */
    public function edit($request, $page_id, $frame_id, $id = null)
    {
        /* Session の構造

            forms[frame_id][forms_id][row_no]['column_type']  型
            forms[frame_id][forms_id][row_no]['column_name']  項目名
            forms[frame_id][forms_id][row_no]['frame_col']    幅

            ※ forms_id = 0 は新規
               row_no は画面に表示するときに連番を設定したものが、POSTされてきて、Sessionに保存される。
        */

        /*
          セッションデータがあればセッションデータから編集画面を表示
          セッションデータがなければ、データベースから編集画面を表示
          データベースになければ、項目追加のプルダウンのみ
        */

        // --- 基本データの取得

        // Session データ
        $forms_session = session('forms');

        // --- フォームデータの取得

        // フレームに紐づくフォームID を探して取得
        $form_db = $this->getForms($frame_id);

        // フォームのID。まだフォームがない場合は0
        $forms_id = 0;
        if (!empty($form_db)) {
            $forms_id = $form_db->id;
        }

        // カラムデータ取得
        $columns_db = DB::table('forms_columns')
            ->where('forms_columns.forms_id', '=', $forms_id)
            ->orderBy('display_sequence')
            ->get();

        // --- 画面に値を渡す準備
        $rows = [];


        // カラムの選択肢用データ
        $forms_columns_id_select = $this->getFormsColumnsSelects($forms_id);


        // Session データに該当フォームのデータがあるか確認
        $form_my_frame = $forms_session[$frame_id];
        if (empty($form_my_frame)) {

            // データベースから画面の値を作成
            $index = 1;
            foreach($columns_db as $record) {
                $rows[$index]['columns_id']  = $record->id;
                $rows[$index]['delete_flag'] = 0;
                $rows[$index]['column_type'] = $record->column_type;
                $rows[$index]['column_name'] = $record->column_name;
                $rows[$index]['required']    = $record->required;
                $rows[$index]['frame_col']   = $record->frame_col;
//                $rows[$index]['size']        = $record->frame_col;

                if (array_key_exists($rows[$index]['columns_id'], $forms_columns_id_select)) {
                    $rows[$index]['select'] = $forms_columns_id_select[$rows[$index]['columns_id']];
                }
                $index++;
            }
        }
        // Session データあり。(画面で編集中)
        else {
            if (!empty($forms_session) && array_key_exists($frame_id, $forms_session) && array_key_exists($forms_id, $forms_session[$frame_id])) {
//                $rows = $forms_session[$frame_id][$forms_id];



                $index = 1;
                foreach($forms_session[$frame_id][$forms_id] as $record) {
                    $rows[$index]['columns_id']  = $record["columns_id"];
                    $rows[$index]['delete_flag'] = 0;
                    $rows[$index]['column_type'] = $record["column_type"];
                    $rows[$index]['column_name'] = $record["column_name"];
                    $rows[$index]['required']    = ( array_key_exists('required', $record) ? $record['required'] : 0 );
                    $rows[$index]['frame_col']   = ( array_key_exists('frame_col', $record) ? $record['frame_col'] : 0 );
//                    $rows[$index]['size']        = ( array_key_exists('frame_col', $record) ? $record['frame_col'] : 0 );
                    $rows[$index]['delete_flag'] = $record["delete_flag"];

                    if (array_key_exists("select", $record)) {
                        $rows[$index]['select'] = $record["select"];
                    }
                    $index++;
                }
            }
        }

//Log::debug($rows);

        // セッションに保持しなおしておく。
        //（保存時にセッションを見る、詳細画面でセッションを使用するなど、操作の度にセッションを使用するため）
        $forms = array();
        foreach($rows as $key => $row) {
            $forms[$frame_id][$forms_id][$key] = $row;
        }
        session(['forms' => $forms]);

//Log::debug($forms);

        // 編集画面テンプレートを呼び出す。
        return $this->view(
            'forms_edit', [
            'forms_id' => $forms_id,
            'rows'     => $rows,
        ]);
    }

    /**
     * カラム再設定関数
     * POPUP からアクションした場合などに呼び出す。
     */
    public function reloadColumn($request, $page_id, $frame_id, $id = null)
    {
        // フレームに紐づくフォームID を探して取得
        $form_db = $this->getForms($frame_id);

        // フォームのID。まだフォームがない場合は0
        $forms_id = 0;
        if (!empty($form_db)) {
            $forms_id = $form_db->id;
        }

        // 画面の項目を詰めなおして、再度編集画面へ。
        foreach($request->forms[$frame_id] as $row_no => $row) {

            // 追加用の行は無視
            if ($row_no == 0) {
                continue;
            }

            $forms[$frame_id][$forms_id][$row_no] = $row;
        }

        // Session に保持している詳細画面情報も付与する。
        $forms = $this->formSessionMarge($request, $forms);

        session(['forms' => $forms]);

//Log::debug($forms);

        // 編集画面へ戻る。
        return $this->edit($request, $page_id, $frame_id, $id);
    }

    /**
     * カラム追加関数
     */
    public function settingColumn($request, $page_id, $frame_id, $id = null)
    {
        // フレームに紐づくフォームID を探して取得
        $form_db = $this->getForms($frame_id);

        // フォームのID。まだフォームがない場合は0
        $forms_id = 0;
        if (!empty($form_db)) {
            $forms_id = $form_db->id;
        }

        // 画面の項目を詰めなおして、再度編集画面へ。
        foreach($request->forms[$frame_id] as $row_no => $row) {
            $forms[$frame_id][$forms_id][$row_no] = $row;
        }

        // Session に保持している詳細画面情報も付与する。
        $forms = $this->formSessionMarge($request, $forms);

        session(['forms' => $forms]);

//Log::debug($forms);

        // 編集画面へ戻る。
        return $this->edit($request, $page_id, $frame_id, $id);
    }

    /**
     * カラム削除関数
     */
    public function destroyColumn($request, $page_id, $frame_id, $id = null)
    {
        // フレームに紐づくフォームID を探して取得
        $form_db = $this->getForms($frame_id);

        // フォームのID。まだフォームがない場合は0
        $forms_id = 0;
        if (!empty($form_db)) {
            $forms_id = $form_db->id;
        }

        // 画面の項目を詰めなおして、再度編集画面へ。
        $forms = array();
        foreach($request->forms[$frame_id] as $row_no => $row) {
            // 追加用の行は無視
            if ($row_no == 0) {
                continue;
            }
            // 削除された項目
            if ($request->destroy_no == $row_no) {
                $row['delete_flag'] = 1;
            }
            $forms[$frame_id][$forms_id][$row_no] = $row;
        }
        session(['forms' => $forms]);

        // 編集画面へ戻る。
        return $this->edit($request, $page_id, $frame_id, $id);
    }

    /**
     *  メインの画面内容に詳細画面の内容をSessionから追加する。
     */
    public function formSessionMarge($request, $forms, $from_row_no = null, $to_row_no = null)
    {
        // 位置移動用変数
        $tmp_frame_id = 0;
        $tmp_form_id = 0;

        // Session データ
        $forms_session = session('forms');

        // 階層を手繰ってselect があれば追加
        foreach($forms_session as $frame_id => $frame) {

            $tmp_frame_id = $frame_id;
            foreach($frame as $form_id => $form) {

                $tmp_form_id = $form_id;
                foreach($form as $row_no => $row) {

                    // リクエストがあれば優先（画面で入力してリロードのケースなので）
                    $select_array = null;
                    foreach ($request->forms as $request_frame) {
                        foreach ($request_frame as $request_row) {
                            if ( $row['columns_id'] == $request_row['columns_id'] && array_key_exists('select', $request_row) ) {
                                $select_array = $request_row['select'];
                            }
                        }
                    }

                    // リクエストに選択肢がなく、セッションにある場合はセッションからセット
                    if (empty($select_array) && array_key_exists('select', $forms_session[$frame_id][$form_id][$row_no])) {
                        $select_array = $row['select'];
                    }

                    if ($select_array) {
                        $forms[$frame_id][$form_id][$row_no]['select'] = $select_array;
                    }
                }
            }
        }

        // 位置移動がある場合
        if (!empty($to_row_no)) {

            $tmp_row_select = null;
            // 先を退避
            if (array_key_exists('select', $forms[$frame_id][$form_id][$to_row_no])) {
                $tmp_row_select = $forms[$frame_id][$form_id][$to_row_no]['select'];
                unset($forms[$frame_id][$form_id][$to_row_no]['select']);
            }
            // 元->先
            if (array_key_exists('select', $forms[$frame_id][$form_id][$from_row_no])) {
                $forms[$frame_id][$form_id][$to_row_no]['select'] = $forms[$frame_id][$form_id][$from_row_no]['select'];
            }
            // 退避->先
            if (!empty($tmp_row_select)) {
                $forms[$frame_id][$form_id][$from_row_no]['select'] = $tmp_row_select;
            }
        }
        //Log::debug($forms);

        return $forms;
    }

    /**
     *  カラム上移動
     */
    public function sequenceUp($request, $page_id, $frame_id, $columns_id)
    {
        // フレームに紐づくフォームID を探して取得
        $form_db = $this->getForms($frame_id);

        // フォームのID。まだフォームがない場合は0
        $forms_id = 0;
        if (!empty($form_db)) {
            $forms_id = $form_db->id;
        }

        // 移動する行番号
        $from_row_no = null;
        $to_row_no = null;

        // 画面の項目を詰めなおして、再度編集画面へ。
        $forms = array();
        foreach($request->forms[$frame_id] as $row_no => $row) {

            // 追加用の行は無視
            if ($row_no == 0) {
                continue;
            }

            // ループで合致した対象行を一つ上に移動。
            if ( $row['columns_id'] == $columns_id ) {
                $forms[$frame_id][$forms_id][$row_no] = $forms[$frame_id][$forms_id][$row_no - 1];
                $forms[$frame_id][$forms_id][$row_no - 1] = $row;

                $from_row_no = $row_no;
                $to_row_no = ($row_no - 1);
            }
            else {
                $forms[$frame_id][$forms_id][$row_no] = $row;
            }
        }
        //Log::debug($forms);

        // Session に保持している詳細画面情報も付与する。
        $forms = $this->formSessionMarge($request, $forms, $from_row_no, $to_row_no);

        session(['forms' => $forms]);

        // 編集画面へ戻る。
        return $this->edit($request, $page_id, $frame_id, null);
    }

    /**
     *  カラム下移動
     */
    public function sequenceDown($request, $page_id, $frame_id, $columns_id)
    {
        // フレームに紐づくフォームID を探して取得
        $form_db = $this->getForms($frame_id);

        // フォームのID。まだフォームがない場合は0
        $forms_id = 0;
        if (!empty($form_db)) {
            $forms_id = $form_db->id;
        }

        // 移動する行番号
        $from_row_no = null;
        $to_row_no = null;

        // 画面の項目を詰めなおして、再度編集画面へ。
        $skip = false;
        $forms = array();
        foreach($request->forms[$frame_id] as $row_no => $row) {

            if ($skip == true ) {
                $forms[$frame_id][$forms_id][$row_no - 1] = $row;
                $skip = false;
                continue;
            }

            // 追加用の行は無視
            if ($row_no == 0) {
                continue;
            }
            // ループで合致した対象行を一つ下に移動。
            if ( $row['columns_id'] == $columns_id ) {
                $forms[$frame_id][$forms_id][$row_no + 1] = $row;
                $skip = true;

                $from_row_no = $row_no;
                $to_row_no = ($row_no + 1);
            }
            else {
                $forms[$frame_id][$forms_id][$row_no] = $row;
            }
        }

        ksort($forms[$frame_id][$forms_id]);

        // Session に保持している詳細画面情報も付与する。
        $forms = $this->formSessionMarge($request, $forms, $from_row_no, $to_row_no);

        session(['forms' => $forms]);

        // 編集画面へ戻る。
        return $this->edit($request, $page_id, $frame_id, null);
    }

    /**
     * カラム編集キャンセル関数
     */
    public function cancel($request, $page_id, $frame_id, $id = null)
    {
        // 関連するセッションクリア
        $request->session()->forget('forms');

        return;
    }

    /**
     * カラム保存関数
     */
    public function save($request, $page_id, $frame_id, $id = null)
    {
        // 対象のフォームID
        $forms_id = $request->forms_id;

        // 新規登録
        if ($request->forms_id == 0) {

            // バケツの登録
            $bucket_id = DB::table('buckets')->insertGetId([
                  'bucket_name' => '無題',
                  'plugin_name' => 'forms'
            ]);

            // フォームの登録
            $forms_id = DB::table('forms')->insertGetId(
                ['bucket_id' => $bucket_id, 'form_name' => '無題']
            );
        }

        /* Session の構造

            forms[frame_id][forms_id][row_no]['column_type']  型
            forms[frame_id][forms_id][row_no]['column_name']  項目名
            forms[frame_id][forms_id][row_no]['frame_col']          幅

            ※ forms_id = 0 は新規
               row_no は画面に表示するときに連番を設定したものが、POSTされてきて、Sessionに保存される。
        */

        // カラムデータ取得
        $columns_db = DB::table('forms_columns')
            ->where('forms_columns.forms_id', '=', $forms_id)
            ->get();
/*
$forms_session = session('forms');
Log::debug("--- forms_session");
Log::debug($forms_session);
Log::debug("--- request->forms");
Log::debug($request->forms);
*/
        // forms_columnsテーブルの保存
        foreach($request->forms[$frame_id] as $row_no => $row) {

            // frame_col は画面にない場合がある。
            $frame_col = 0;
            if ( array_key_exists( 'frame_col', $row ) ) {
                $frame_col = $row['frame_col'];
            }

            // 保存時、追加行は対象外
            if ( $row_no == 0 ) {
                continue;
            }

            // 削除
            if ($row['delete_flag'] == '1') {
                $id = DB::table('forms_columns')->where('id', $row['columns_id'])->delete();
            }
            // 更新
            else if ($row['columns_id']) {
                $id = DB::table('forms_columns')->where('id', $row['columns_id'])->update([
                    'forms_id' => $forms_id,
                    'column_type' => $row['column_type'],
                    'column_name' => $row['column_name'],
                    'required' => ( array_key_exists('required', $row) ? $row['required'] : 0 ),
                    'frame_col' => $frame_col,
                    'display_sequence' => $row_no
                ]);
            }
            // 追加
            else {
                $id = DB::table('forms_columns')->insert([
                    'forms_id' => $forms_id,
                    'column_type' => $row['column_type'],
                    'column_name' => $row['column_name'],
                    'required' => ( array_key_exists('required', $row) ? $row['required'] : 0 ),
                    'frame_col' => $frame_col,
                    'display_sequence' => $row_no
                ]);
            }
        }

        // Session データ
        $forms_session = session('forms');

        // forms_columns_selects テーブルの保存
        foreach($forms_session[$frame_id][$forms_id] as $column) {

            if (array_key_exists('select', $column)) {

                // forms_columns_selects テーブルは delete->insert
                DB::table('forms_columns_selects')->where('forms_columns_id', $column['columns_id'])->delete();

                foreach($column['select'] as $select) {

                    // forms_columns_selects の登録
                    $bucket_id = DB::table('forms_columns_selects')->insertGetId([
                          'forms_columns_id' => $column['columns_id'],
                          'value' => $select['value'],
                    ]);
                }
            }
        }

        // 新規登録時
        if ($request->forms_id == 0) {

            // FrameのバケツIDの更新
            Frame::where('id', $frame_id)
                      ->update(['bucket_id' => $bucket_id]);
        }


        // 関連するセッションクリア
        $request->session()->forget('forms');

        // 編集画面へ戻る。
        return $this->edit($request, $page_id, $frame_id, $id);
    }
}
