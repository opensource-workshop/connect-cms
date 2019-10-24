<?php

namespace App\Plugins\User\Forms;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

use DB;

use App\Models\Common\Buckets;
use App\Models\Common\Frame;
use App\Models\Common\Page;
use App\Models\Common\Uploads;
use App\Models\User\Forms\Forms;
use App\Models\User\Forms\FormsColumns;
use App\Models\User\Forms\FormsInputs;
use App\Models\User\Forms\FormsInputCols;

use App\Mail\ConnectMail;
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
    /* オブジェクト変数 */

    /* コアから呼び出す関数 */

    /**
     *  関数定義（コアから呼び出す）
     */
    public function getPublicFunctions()
    {
        // 標準関数以外で画面などから呼ばれる関数の定義
        $functions = array();
        $functions['get']  = [];
        $functions['post'] = ['index', 'publicConfirm', 'publicStore', 'cancel'];
        return $functions;
    }

    /**
     *  編集画面の最初のタブ
     *
     *  スーパークラスをオーバーライド
     */
    public function getFirstFrameEditAction()
    {
        // フォームの設定がまだの場合は、フォームの新規作成に遷移する。
        $form = $this->getForms($this->frame->id);
        if (empty($form)) {
            return "createBuckets";
        }

        // カラムの設定画面
        return "editColumn";
    }

    /* private関数 */

    /**
     *  データ取得
     */
    private function getForms($frame_id)
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
    private function getFormsColumns($form)
    {
        // フォームのカラムデータ
        $form_columns = [];
        if ( !empty($form) ) {
            $forms_columns = FormsColumns::where('forms_id', $form->id)->orderBy('display_sequence')->get();
        }

        // カラムデータがない場合
        if (empty($forms_columns)) {
            return null;
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
    private function getFormsColumnsSelects($forms_id)
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
     *  紐づくフォームID とフレームデータの取得
     */
    private function getFormFrame($frame_id)
    {
        // Frame データ
        $frame = DB::table('frames')
                 ->select('frames.*', 'forms.id as forms_id')
                 ->leftJoin('forms', 'forms.bucket_id', '=', 'frames.bucket_id')
                 ->where('frames.id', $frame_id)
                 ->first();
        return $frame;
    }

    /* 画面アクション関数 */

    /**
     *  データ初期表示関数
     *  コアがページ表示の際に呼び出す関数
     */
    public function index($request, $page_id, $frame_id, $errors = null)
    {
/*
$content = array();
$content["value1"] = "値その1-1";
$content["value2"] = "値その2";
Mail::to('nagahara@osws.jp')->send(new ConnectMail($content));
*/

        // セッション初期化などのLaravel 処理。
        $request->flash();

        // Forms、Frame データ
        $form = $this->getForms($frame_id);

        // フォームのカラムデータ
        $forms_columns = $this->getFormsColumns($form);

        // カラムの選択肢用データ
        $forms_columns_id_select = null;
        if ($form) {
            $forms_columns_id_select = $this->getFormsColumnsSelects($form->id);
        }

        // 表示テンプレートを呼び出す。
        return $this->view(
            'forms', [
            'request' => $request,
            'frame_id' => $frame_id,
            'form' => $form,
            'forms_columns' => $forms_columns,
            'forms_columns_id_select' => $forms_columns_id_select,
            'errors'      => $errors,
        ])->withInput($request->all);
    }

    /**
     * 登録時の確認
     */
    public function publicConfirm($request, $page_id, $frame_id, $id = null)
    {
        // Forms、Frame データ
        $form = $this->getForms($frame_id);

        // フォームのカラムデータ
        $forms_columns = $this->getFormsColumns($form);

        // エラーチェック配列
        $validator_array = array( 'column' => array(), 'message' => array());

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
    public function publicStore($request, $page_id, $frame_id, $id = null)
    {
        // Forms、Frame データ
        $form = $this->getForms($frame_id);

        // forms_inputs 登録
        $forms_inputs_id = DB::table('forms_inputs')->insertGetId([
            'forms_id' => $form->id,
        ]);

        // フォームのカラムデータ
        $forms_columns = FormsColumns::where('forms_id', $form->id)->orderBy('display_sequence')->get();

        // メールの送信文字列
        $contents_text = '';

        // 登録者のメールアドレス
        $user_mailaddresses = array();

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

            // データ登録フラグを見て登録
            if ($form->data_save_flag) {
                DB::table('forms_input_cols')->insertGetId([
                    'forms_inputs_id' => $forms_inputs_id,
                    'forms_columns_id' => $forms_column['id'],
                    'value' => $value,
                ]);
            }

            // メールの内容
            $contents_text .= $forms_column->column_name . "：" . $value . "\n";

            // メール型
            if ($forms_column->column_type == "mail") {
                $user_mailaddresses[] = $value;
            }
        }
        // 最後の改行を除去
        $contents_text = trim($contents_text);

        // メール送信
        if ($form->mail_send_flag) {

            // メール本文の組み立て
            $mail_format = $form->mail_format;
            $mail_text = str_replace( '[[body]]', $contents_text, $mail_format);

            // メール送信（管理者側）
            $mail_addresses = explode(',', $form->mail_send_address);
            foreach($mail_addresses as $mail_address) {
                Mail::to($mail_address)->send(new ConnectMail(['subject' => $form->mail_subject, 'template' => 'mail.send'], ['content' => $mail_text]));
            }

            // メール送信（ユーザー側）
            foreach($user_mailaddresses as $user_mailaddress) {
                if (!empty($user_mailaddress)) {
                    Mail::to($user_mailaddress)->send(new ConnectMail(['subject' => $form->mail_subject, 'template' => 'mail.send'], ['content' => $mail_text]));
                }
            }
        }

        // 表示テンプレートを呼び出す。
        return $this->view(
            'forms_thanks', [
            'after_message' => $form->after_message
        ]);
    }

    /**
     * フォーム選択表示関数
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
            'forms_datalist', [
            'plugin_frame' => $plugin_frame,
            'plugins'      => $plugins,
        ]);
    }

    /**
     * フォーム新規作成画面
     */
    public function createBuckets($request, $page_id, $frame_id, $forms_id = null, $create_flag = false, $message = null, $errors = null)
    {
        // 新規作成フラグを付けてフォーム設定変更画面を呼ぶ
        $create_flag = true;
        return $this->editBuckets($request, $page_id, $frame_id, $forms_id, $create_flag, $message, $errors);
    }

    /**
     * フォーム設定変更画面の表示
     */
    public function editBuckets($request, $page_id, $frame_id, $forms_id = null, $create_flag = false, $message = null, $errors = null)
    {
        // セッション初期化などのLaravel 処理。
        $request->flash();

        // フォーム＆フレームデータ
        $form_frame = $this->getFormFrame($frame_id);

        // フォームデータ
        $form = new Forms();

        // forms_id が渡ってくればforms_id が対象
        if (!empty($forms_id)) {
            $form = Forms::where('id', $forms_id)->first();
        }
        // Frame のbucket_id があれば、bucket_id からフォームデータ取得、なければ、新規作成か選択へ誘導
        else if (!empty($form_frame->bucket_id) && $create_flag == false) {
            $form = Forms::where('bucket_id', $form_frame->bucket_id)->first();
        }

        // 表示テンプレートを呼び出す。
        return $this->view(
            'forms_edit_form', [
            'form_frame'  => $form_frame,
            'form'        => $form,
            'create_flag' => $create_flag,
            'message'     => $message,
            'errors'      => $errors,
        ])->withInput($request->all);
    }

    /**
     *  フォーム登録処理
     */
    public function saveBuckets($request, $page_id, $frame_id, $forms_id = null)
    {
        // 項目のエラーチェック
        $validator = Validator::make($request->all(), [
            'forms_name'  => ['required'],
        ]);
        $validator->setAttributeNames([
            'forms_name'  => 'フォーム名',
        ]);

        // エラーがあった場合は入力画面に戻る。
        $message = null;
        if ($validator->fails()) {

            if (empty($forms_id)) {
                $create_flag = true;
                return $this->createBuckets($request, $page_id, $frame_id, $forms_id, $create_flag, $message, $validator->errors());
            }
            else  {
                $create_flag = false;
                return $this->editBuckets($request, $page_id, $frame_id, $forms_id, $create_flag, $message, $validator->errors());
            }
        }

        // 更新後のメッセージ
        $message = null;

        // 画面から渡ってくるforms_id が空ならバケツとブログを新規登録
        if (empty($request->forms_id)) {

            // バケツの登録
            $bucket_id = DB::table('buckets')->insertGetId([
                  'bucket_name' => '無題',
                  'plugin_name' => 'forms'
            ]);

            // ブログデータ新規オブジェクト
            $forms = new Forms();
            $forms->bucket_id = $bucket_id;

            // Frame のBuckets を見て、Buckets が設定されていなければ、作成したものに紐づける。
            // Frame にBuckets が設定されていない ＞ 新規のフレーム＆ブログ作成
            // Frame にBuckets が設定されている ＞ 既存のフレーム＆ブログ更新
            // （表示フォーム選択から遷移してきて、内容だけ更新して、フレームに紐づけないケースもあるため）
            $frame = Frame::where('id', $frame_id)->first();
            if (empty($frame->bucket_id)) {

                // FrameのバケツIDの更新
                $frame = Frame::where('id', $frame_id)->update(['bucket_id' => $bucket_id]);
            }

            $message = 'フォーム設定を追加しました。<br />　 カラムを設定してください。［ <a href="/plugin/forms/editColumn/' . $page_id . '/' . $frame_id . '/">カラム設定</a> ］';
        }
        // forms_id があれば、フォームを更新
        else {

            // フォームデータ取得
            $forms = Forms::where('id', $request->forms_id)->first();

            $message = 'フォーム設定を変更しました。';
        }

        // フォーム設定
        $forms->forms_name          = $request->forms_name;
        $forms->mail_send_flag      = (empty($request->mail_send_flag))      ? 0 : $request->mail_send_flag;
        $forms->mail_send_address   = $request->mail_send_address;
        $forms->user_mail_send_flag = (empty($request->user_mail_send_flag)) ? 0 : $request->user_mail_send_flag;
        $forms->from_mail_name      = $request->from_mail_name;
        $forms->mail_subject        = $request->mail_subject;
        $forms->mail_format         = $request->mail_format;
        $forms->data_save_flag      = (empty($request->data_save_flag))      ? 0 : $request->data_save_flag;
        $forms->after_message       = $request->after_message;

        // データ保存
        $forms->save();

        // 新規作成フラグを付けてフォーム設定変更画面を呼ぶ
        $create_flag = false;

        return $this->editBuckets($request, $page_id, $frame_id, $forms_id, $create_flag, $message);
    }

    /**
     *  フォーム削除処理
     */
    public function destroyBuckets($request, $page_id, $frame_id, $forms_id)
    {
        // forms_id がある場合、データを削除
        if ( $forms_id ) {

            // カラムデータを削除する。
            FormsColumns::where('forms_id', $forms_id)->delete();

            // フォーム設定を削除する。
            Forms::destroy($forms_id);

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
        $request->session()->forget('forms');

        // 表示ブログ選択画面を呼ぶ
        return $this->listBuckets($request, $page_id, $frame_id, $id);
    }

    /**
     * カラム追加関数
     */
    public function addColumn($request, $page_id, $frame_id, $id = null)
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

        // 編集画面へ戻る。
        return $this->editColumn($request, $page_id, $frame_id, $id);
    }

    /**
     * カラム編集画面の表示
     */
    public function editColumn($request, $page_id, $frame_id, $id = null)
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
        $form_my_frame = null;
        if (is_array($forms_session) && array_key_exists($frame_id, $forms_session)) {
            $form_my_frame = $forms_session[$frame_id];
        }
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

                if (array_key_exists($rows[$index]['columns_id'], $forms_columns_id_select)) {
                    $rows[$index]['select'] = $forms_columns_id_select[$rows[$index]['columns_id']];
                }
                $index++;
            }
        }
        // Session データあり。(画面で編集中)
        else {
            if (!empty($forms_session) && array_key_exists($frame_id, $forms_session) && array_key_exists($forms_id, $forms_session[$frame_id])) {

                $index = 1;
                foreach($forms_session[$frame_id][$forms_id] as $record) {
                    $rows[$index]['columns_id']  = $record["columns_id"];
                    $rows[$index]['delete_flag'] = 0;
                    $rows[$index]['column_type'] = $record["column_type"];
                    $rows[$index]['column_name'] = $record["column_name"];
                    $rows[$index]['required']    = ( array_key_exists('required', $record) ? $record['required'] : 0 );
                    $rows[$index]['frame_col']   = ( array_key_exists('frame_col', $record) ? $record['frame_col'] : 0 );
                    $rows[$index]['delete_flag'] = $record["delete_flag"];

                    if (array_key_exists("select", $record)) {
                        $rows[$index]['select'] = $record["select"];
                    }
                    $index++;
                }
            }
        }

        // セッションに保持しなおしておく。
        //（保存時にセッションを見る、詳細画面でセッションを使用するなど、操作の度にセッションを使用するため）
        $forms = array();
        foreach($rows as $key => $row) {
            $forms[$frame_id][$forms_id][$key] = $row;
        }
        session(['forms' => $forms]);

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

        // 編集画面へ戻る。
        return $this->editColumn($request, $page_id, $frame_id, $id);
    }

    /**
     * カラム削除関数
     */
    public function deleteColumn($request, $page_id, $frame_id, $id = null)
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

        // Session に保持している詳細画面情報も付与する。
        $forms = $this->formSessionMarge($request, $forms);

        session(['forms' => $forms]);

        // 編集画面へ戻る。
        return $this->editColumn($request, $page_id, $frame_id, $id);
    }

    /**
     *  メインの画面内容に詳細画面の内容をSessionから追加する。
     */
    private function formSessionMarge($request, $forms, $from_row_no = null, $to_row_no = null)
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
                        foreach ($request_frame as $request_row_no => $request_row) {

//                            if ( $row['columns_id'] == $request_row && array_key_exists('select', $request_row) ) {
                            if ( $row_no == $request_row_no && array_key_exists('select', $request_row) ) {
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
    public function upColumnSequence($request, $page_id, $frame_id, $target_row_no)
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
//            if ( $row['columns_id'] == $columns_id ) {
            if ( $target_row_no == $row_no ) {
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
        return $this->editColumn($request, $page_id, $frame_id, null);
    }

    /**
     *  カラム下移動
     */
    public function downColumnSequence($request, $page_id, $frame_id, $target_row_no)
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
//            if ( $row['columns_id'] == $columns_id ) {
            if ( $target_row_no == $row_no ) {
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
        return $this->editColumn($request, $page_id, $frame_id, null);
    }

    /**
     * カラム編集キャンセル関数
     */
    public function cancel($request, $page_id, $frame_id, $id = null)
    {
        // 権限チェック(カラムの編集ができる権限が必要)
        if ($this->can('buckets.editColumn')) {
            return $this->view_error(403);
        }

        // 関連するセッションクリア
        $request->session()->forget('forms');

        return;
    }

    /**
     * カラム選択肢削除
     */
    private function deleteColumnsSelects($columns_id)
    {
        if (!empty($columns_id)) {
            DB::table('forms_columns_selects')->where('forms_columns_id', $columns_id)->delete();
        }
    }

    /**
     * カラム選択肢追加
     */
    private function insertColumnsSelects($columns_id, $column)
    {
        if (!empty($columns_id)) {
            if (!empty($column['select'])) {

                foreach($column['select'] as $select) {

                    // forms_columns_selects の登録
                    $bucket_id = DB::table('forms_columns_selects')->insertGetId([
                          'forms_columns_id' => $columns_id,
                          'value' => $select['value'],
                    ]);
                }
            }
        }
    }

    /**
     * カラム保存関数
     */
    public function saveColumn($request, $page_id, $frame_id, $id = null)
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
                ['bucket_id' => $bucket_id, 'forms_name' => '無題']
            );
        }

        // Session データ
        $forms_session = session('forms');

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

//Log::debug($request->forms);

// セッションからカラムデータを登録し、カラムID を取ったら、セッション配列に戻す。
// 選択肢データの保存はカラムID を使用

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
                // 選択肢の削除
                $this->deleteColumnsSelects($row['columns_id']);
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
                // 選択肢の削除・追加
                $this->deleteColumnsSelects($row['columns_id']);
                $this->insertColumnsSelects($row['columns_id'], $forms_session[$frame_id][$forms_id][$row_no]);
            }
            // 追加
            else {
                $id = DB::table('forms_columns')->insertGetId([
                    'forms_id' => $forms_id,
                    'column_type' => $row['column_type'],
                    'column_name' => $row['column_name'],
                    'required' => ( array_key_exists('required', $row) ? $row['required'] : 0 ),
                    'frame_col' => $frame_col,
                    'display_sequence' => $row_no
                ]);
                // 選択肢の追加
                $this->insertColumnsSelects($id, $forms_session[$frame_id][$forms_id][$row_no]);
            }
        }

        // Session データ
//        $forms_session = session('forms');

//Log::debug($forms_session);
/*
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
*/

        // 新規登録時
        if ($request->forms_id == 0) {

            // FrameのバケツIDの更新
            Frame::where('id', $frame_id)
                      ->update(['bucket_id' => $bucket_id]);
        }


        // 関連するセッションクリア
        $request->session()->forget('forms');

        // 編集画面へ戻る。
        return $this->editColumn($request, $page_id, $frame_id, $id);
    }

    /**
     * フォームデータダウンロード
     */
    public function downloadCsv($request, $page_id, $frame_id, $id)
    {

        // id で対象のデータの取得

        // フォームの取得
        $form = Forms::where('id', $id)->first();

        // カラムの取得
        $columns = FormsColumns::where('forms_id', $id)->orderBy('display_sequence', 'asc')->get();

        // 登録データの取得
        $input_cols = FormsInputCols::whereIn('forms_inputs_id', FormsInputs::select('id')->where('forms_id', $id))
                                      ->orderBy('forms_inputs_id', 'asc')->orderBy('forms_columns_id', 'asc')
                                      ->get();

/*
ダウンロード前の配列イメージ。
0行目をFormsColumns から生成して、1行目以降は0行目の キーのみのコピーを作成し、データを入れ込んでいく。
1行目以降の行番号は forms_inputs_id の値を使用

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

-- FormsInputCols のSQL
SELECT *
FROM forms_input_cols
WHERE forms_inputs_id IN (
    SELECT id FROM forms_inputs WHERE forms_id = 17
)
ORDER BY forms_inputs_id, forms_columns_id

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
            if (!array_key_exists($input_col->forms_inputs_id, $csv_array)) {
                $csv_array[$input_col->forms_inputs_id] = $copy_base;
            }
            $csv_array[$input_col->forms_inputs_id][$input_col->forms_columns_id] = $input_col->value;
        }

        // レスポンス版
        $filename = $form->forms_name . '.csv';
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
}
