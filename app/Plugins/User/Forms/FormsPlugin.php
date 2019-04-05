<?php

namespace App\Plugins\User\Forms;

use Illuminate\Support\Facades\Log;

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
     *  データ初期表示関数
     *  コアがページ表示の際に呼び出す関数
     */
    public function index($request, $page_id, $frame_id)
    {
        // Page データ
        $page = Page::where('id', $page_id)->first();

        // Forms、Frame データ
        $form = $this->getForms($frame_id);

        // フォームのカラムデータ
        $forms_columns = $this->getFormsColumns($form);

//        $form_columns = [];
//        if ( !empty($form) ) {
//            $forms_columns = FormsColumns::where('forms_id', $form->id)->orderBy('display_sequence')->get();
//        }



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



        // ページデータ
        $page = Page::where('id', $page_id)->first();

        // 表示テンプレートを呼び出す。
        return view('plugins.user.forms.forms', [
            'frame_id' => $frame_id,
            'page' => $page,
            'form' => $form,
//            'forms_columns' => $ret_array,
            'forms_columns' => $forms_columns,
        ]);
    }

    /**
     * 登録時の確認
     */
    public function confirm($request, $page_id, $frame_id, $id = null)
    {
        // Page データ
        $page = Page::where('id', $page_id)->first();

        // Forms、Frame データ
        $form = $this->getForms($frame_id);

        // フォームのカラムデータ
//        $forms_columns = FormsColumns::where('forms_id', $form->id)->orderBy('display_sequence')->get();

        // フォームのカラムデータ
        $forms_columns = $this->getFormsColumns($form);
//Log::debug($forms_columns);
        // 表示テンプレートを呼び出す。
        return view('plugins.user.forms.forms_confirm', [
            'request' => $request,
            'page' => $page,
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
            DB::table('forms_input_cols')->insertGetId([
                'forms_inputs_id' => $forms_inputs_id,
                'forms_columns_id' => $forms_column['id'],
                'value' => $request->forms_columns_value[$forms_column->id]
            ]);
        }

        // 表示テンプレートを呼び出す。
        return view('plugins.user.forms.forms_thanks', [
            'page_id' => $page_id,
            'frame_id' => $frame_id
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

        // Page データ
        $page = Page::where('id', $page_id)->first();

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
                $rows[$index]['size']        = $record->frame_col;
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
                    $rows[$index]['size']        = ( array_key_exists('frame_col', $record) ? $record['frame_col'] : 0 );
                    $rows[$index]['delete_flag'] = $record["delete_flag"];
                    $index++;
                }
            }
        }

        //Log::debug($rows);

        // 編集画面テンプレートを呼び出す。
        return view('plugins.user.forms.forms_edit', [
            'frame_id' => $frame_id,
            'page' => $page,
            'forms_id' => $forms_id,
            'rows' => $rows
        ]);
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
        session(['forms' => $forms]);

        // 編集画面へ戻る。
        return;
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
        return;
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
            }
            else {
                $forms[$frame_id][$forms_id][$row_no] = $row;
            }
        }
        session(['forms' => $forms]);

        return;
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
            }
            else {
                $forms[$frame_id][$forms_id][$row_no] = $row;
            }
        }

        ksort($forms[$frame_id][$forms_id]);
        session(['forms' => $forms]);

        return;
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

        // 新規登録時
        if ($request->forms_id == 0) {

            // FrameのバケツIDの更新
            Frame::where('id', $frame_id)
                      ->update(['bucket_id' => $bucket_id]);
        }

        // 関連するセッションクリア
        $request->session()->forget('forms');

        return;
    }
}
