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
use App\Models\User\Forms\FormsColumnsSelects;
use App\Models\User\Forms\FormsInputs;
use App\Models\User\Forms\FormsInputCols;

use App\Rules\CustomVali_AlphaNumForMultiByte;
use App\Rules\CustomVali_CheckWidthForString;
use App\Rules\CustomVali_Confirmed;
use App\Rules\CustomVali_TimeFromTo;
use App\Rules\CustomVali_BothRequired;

use App\Mail\ConnectMail;
use App\Plugins\User\UserPluginBase;

/**
 * フォーム・プラグイン
 *
 * フォームの作成＆データ収集用プラグイン。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>, 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>
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
        $functions['get']  = [
            'editColumnDetail',
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
     *  ※まとめ行の設定が不正な場合はリテラル「frame_setting_error」を返す
     *  ※フォーム設定で「登録者にメール送信あり」設定にも関わらず、項目内にメールアドレス型が存在しない場合はリテラル「mail_setting_error」を返す
     */
    private function getFormsColumns($form)
    {
        // フォームのカラムデータ
        $form_columns = [];
        if (!empty($form)) {
            $forms_columns = FormsColumns::where('forms_id', $form->id)->orderBy('display_sequence')->get();
            if ($form->user_mail_send_flag == '1' && empty($forms_columns->where('column_type', \FormColumnType::mail)->first())) {
                return 'mail_setting_error';
            }
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
                    // dd(count($forms_columns), $i, $j);
                    if (count($forms_columns) >= (1 + $i + $j)) {
                        $group_row[] = $forms_columns[$i + $j];
                    } else {
                        return 'frame_setting_error';
                    }
                }
                $tmp_group->group = $group_row;

                $ret_array[] = $tmp_group;
                $i = $i + $forms_columns[$i]->frame_col;
            } else {
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
                                     ->orderBy('forms_columns_selects.forms_columns_id', 'asc')
                                     ->orderBy('forms_columns_selects.display_sequence', 'asc')
                                     ->get();
        // カラムID毎に詰めなおし
        $forms_columns_id_select = array();
        $index = 1;
        $before_forms_columns_id = null;
        foreach ($forms_columns_selects as $forms_columns_select) {
            if ($before_forms_columns_id != $forms_columns_select->forms_columns_id) {
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


        $setting_error_messages = null;
        $forms_columns = null;
        $forms_columns_id_select = null;
        if ($form) {
            $forms_columns_id_select = $this->getFormsColumnsSelects($form->id);
            if (FormsColumns::query()
                ->where('forms_id', $form->id)
                ->where('column_type', \FormColumnType::group)
                ->whereNull('frame_col')
                ->get()
                ->count() > 0) {
                // データ型が「まとめ行」で、まとめ数の設定がないデータが存在する場合
                $setting_error_messages[] = 'フレームの設定画面から、項目データ（まとめ行のまとめ数）を設定してください。';
            }

            /**
             * フォームのカラムデータを取得
             * ※まとめ行の設定が不正な場合はリテラル「frame_setting_error」が返る
             * ※フォーム設定で「登録者にメール送信あり」設定にも関わらず、項目内にメールアドレス型が存在しない場合はリテラル「mail_setting_error」が返る
             */
            $forms_columns = $this->getFormsColumns($form);

            if ($forms_columns == 'frame_setting_error') {
                // 項目データはあるが、まとめ行の設定（まとめ行の位置とまとめ数の設定）が不正な場合
                $setting_error_messages[] = 'まとめ行の設定が不正です。フレームの設定画面からまとめ行の位置、又は、まとめ数の設定を見直してください。';
            } elseif ($forms_columns == 'mail_setting_error') {
                // フォーム設定で「登録者にメール送信あり」設定にも関わらず、項目内にメールアドレス型が存在しない場合
                $setting_error_messages[] = 'メールアドレス型の項目を設定してください。（フォームの設定「登録者にメール送信する」と関連）';
            } elseif (!$forms_columns) {
                // 項目データがない場合
                $setting_error_messages[] = 'フレームの設定画面から、項目データを作成してください。';
            }
        } else {
            // フレームに紐づくフォーム親データがない場合
            $setting_error_messages[] = 'フレームの設定画面から、使用するフォームを選択するか、作成してください。';
        }

        // 表示テンプレートを呼び出す。
        return $this->view(
            'forms', [
            'request' => $request,
            'frame_id' => $frame_id,
            'form' => $form,
            'forms_columns' => $forms_columns,
            'forms_columns_id_select' => $forms_columns_id_select,
            'errors' => $errors,
            'setting_error_messages' => $setting_error_messages,
            ]
        )->withInput($request->all);
    }

    /**
     * （再帰関数）入力値の前後をトリムする
     *
     * @param $request
     * @return void
     */
    public static function trimInput($value)
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
     * @param [App\Models\User\Forms\FormsColumns] $forms_column
     * @param Request $request
     * @return void
     */
    private function getValidatorRule($validator_array, $forms_column, $request)
    {

        $validator_rule = null;
        // 必須チェック
        if ($forms_column->required) {
            $validator_rule[] = 'required';
        }
        // メールアドレスチェック
        if ($forms_column->column_type == \FormColumnType::mail) {
            $validator_rule[] = 'nullable';
            $validator_rule[] = 'email';
            // 同値チェック
            $validator_rule[] = new CustomVali_Confirmed($forms_column->column_name, $request->forms_columns_value_confirmation[$forms_column->id]);
        }
        // 数値チェック
        if ($forms_column->rule_allowed_numeric) {
            if ($request->forms_columns_value[$forms_column->id]) {
                // 入力値があった場合
                if (is_numeric(mb_convert_kana($request->forms_columns_value[$forms_column->id], 'n'))) {
                    // 全角→半角変換した結果が数値の場合
                    $tmp_array = $request->forms_columns_value;
                    // 全角→半角へ丸める
                    $tmp_array[$forms_column->id] = mb_convert_kana($request->forms_columns_value[$forms_column->id], 'n');
                    $request->merge([
                        "forms_columns_value" => $tmp_array,
                    ]);
                } else {
                    // 全角→半角変換した結果が数値ではない場合
                    $validator_rule[] = 'numeric';
                }
            }
        }
        // 英数値チェック
        if ($forms_column->rule_allowed_alpha_numeric) {
            $validator_rule[] = 'nullable';
            $validator_rule[] = new CustomVali_AlphaNumForMultiByte();
        }
        // 最大文字数チェック
        if ($forms_column->rule_word_count) {
            $validator_rule[] = new CustomVali_CheckWidthForString($forms_column->column_name, $forms_column->rule_word_count);
        }
        // 指定桁数チェック
        if ($forms_column->rule_digits_or_less) {
            $validator_rule[] = 'digits:' . $forms_column->rule_digits_or_less;
        }
        // 最大値チェック
        if ($forms_column->rule_max) {
            $validator_rule[] = 'nullable';
            $validator_rule[] = 'numeric';
            $validator_rule[] = 'max:' . $forms_column->rule_max;
        }
        // 最小値チェック
        if ($forms_column->rule_min) {
            $validator_rule[] = 'nullable';
            $validator_rule[] = 'numeric';
            $validator_rule[] = 'min:' . $forms_column->rule_min;
        }
        // ～日以降を許容
        if ($forms_column->rule_date_after_equal) {
            $comparison_date = \Carbon::now()->addDay($forms_column->rule_date_after_equal)->format('Y/m/d');
            $validator_rule[] = 'after_or_equal:' . $comparison_date;
        }
        // 日付チェック
        if ($forms_column->column_type == \FormColumnType::date) {
            $validator_rule[] = 'nullable';
            $validator_rule[] = 'date';
        }
        // 「時間From~To型」チェック
        if ($forms_column->column_type == \FormColumnType::time_from_to) {
            $time_from = $request->forms_columns_value_for_time_from[$forms_column->id];
            $time_to = $request->forms_columns_value_for_time_to[$forms_column->id];
            // request内の入力値（配列）を一旦取り出して、時間型（From~To）の入力値をセットする
            $tmp_array = $request->forms_columns_value;
            $tmp_array[$forms_column->id] = $time_from . '~' . $time_to;
            $request->merge([
                "forms_columns_value" => $tmp_array,
            ]);
            if ($time_from && $time_to) {
                // 両方入力時、時間の前後チェック
                $validator_rule[] = new CustomVali_TimeFromTo(
                    \Carbon::createFromTimeString($time_from . ':00'),
                    \Carbon::createFromTimeString($time_to . ':00')
                );
            } elseif ($time_from || $time_to) {
                // いづれか入力時、条件必須チェック（いづれか入力時、両方必須）
                $validator_rule[] = new CustomVali_BothRequired(
                    $request->forms_columns_value_for_time_from[$forms_column->id],
                    $request->forms_columns_value_for_time_to[$forms_column->id]
                );
            }
        }
        // バリデータールールをセット
        if ($validator_rule) {
            $validator_array['column']['forms_columns_value.' . $forms_column->id] = $validator_rule;
            $validator_array['message']['forms_columns_value.' . $forms_column->id] = $forms_column->column_name;
        }

        return $validator_array;
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

        foreach ($forms_columns as $forms_column) {
            // まとめ行であれば、ネストされた配列をさらに展開
            if ($forms_column->group) {
                foreach ($forms_column->group as $group_item) {
                    // まとめ行で指定している項目について、バリデータールールをセット
                    $validator_array = self::getValidatorRule($validator_array, $group_item, $request);
                }
            }
            // まとめ行以外の項目について、バリデータールールをセット
            $validator_array = self::getValidatorRule($validator_array, $forms_column, $request);
        }

        // 入力値をトリム
        $request->merge(self::trimInput($request->all()));

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
            ]
        );
    }

    /**
     * データ登録
     */
    public function publicStore($request, $page_id, $frame_id, $id = null)
    {
        // Forms、Frame データ
        $form = $this->getForms($frame_id);

        // forms_inputs 登録
        $forms_inputs = new FormsInputs();
        $forms_inputs->forms_id = $form->id;
        $forms_inputs->save();

        // フォームのカラムデータ
        $forms_columns = FormsColumns::where('forms_id', $form->id)->orderBy('display_sequence')->get();

        // メールの送信文字列
        $contents_text = '';

        // 登録者のメールアドレス
        $user_mailaddresses = array();

        // forms_input_cols 登録
        foreach ($forms_columns as $forms_column) {
            if ($forms_column->column_type == "group") {
                continue;
            }

            $value = "";
            if (is_array($request->forms_columns_value[$forms_column->id])) {
                $value = implode(',', $request->forms_columns_value[$forms_column->id]);
            } else {
                $value = $request->forms_columns_value[$forms_column->id];
            }

            // データ登録フラグを見て登録
            if ($form->data_save_flag) {
                $forms_input_cols = new FormsInputCols();
                $forms_input_cols->forms_inputs_id = $forms_inputs->id;
                $forms_input_cols->forms_columns_id = $forms_column['id'];
                $forms_input_cols->value = $value;
                $forms_input_cols->save();
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

        // 採番 ※[採番プレフィックス文字列] + [ゼロ埋め採番6桁]
        $number = $form->numbering_use_flag ? $form->numbering_prefix . sprintf('%06d', $this->getNo('forms', $form->bucket_id, $form->numbering_prefix)) : null;

        // 登録後メッセージ内の採番文字列を置換
        $after_message = str_replace('[[number]]', $number, $form->after_message);

        // メール送信
        if ($form->mail_send_flag || $form->user_mail_send_flag) {
            // メール本文の組み立て
            $mail_format = $form->mail_format;
            $mail_text = str_replace('[[body]]', $contents_text, $mail_format);

            // メール本文内の採番文字列を置換
            $mail_text = str_replace('[[number]]', $number, $mail_text);

            // メール送信（管理者側）
            if ($form->mail_send_flag) {
                $mail_addresses = explode(',', $form->mail_send_address);
                foreach ($mail_addresses as $mail_address) {
                    Mail::to(trim($mail_address))->send(new ConnectMail(['subject' => $form->mail_subject, 'template' => 'mail.send'], ['content' => $mail_text]));
                }
            }

            // メール送信（ユーザー側）
            if ($form->user_mail_send_flag) {
                foreach ($user_mailaddresses as $user_mailaddress) {
                    if (!empty($user_mailaddress)) {
                        Mail::to(trim($user_mailaddress))->send(new ConnectMail(['subject' => $form->mail_subject, 'template' => 'mail.send'], ['content' => $mail_text]));
                    }
                }
            }
        }

        // 表示テンプレートを呼び出す。
        return $this->view(
            'forms_thanks', [
            'after_message' => $after_message
            ]
        );
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
                       ->paginate(10, ["*"], "frame_{$frame_id}_page");

        // 表示テンプレートを呼び出す。
        return $this->view(
            'forms_datalist', [
            'plugin_frame' => $plugin_frame,
            'plugins'      => $plugins,
            ]
        );
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

        if (!empty($forms_id)) {
            // forms_id が渡ってくればforms_id が対象
            $form = Forms::where('id', $forms_id)->first();
        } elseif (!empty($form_frame->bucket_id) && $create_flag == false) {
            // Frame のbucket_id があれば、bucket_id からフォームデータ取得、なければ、新規作成か選択へ誘導
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
            ]
        )->withInput($request->all);
    }

    /**
     *  フォーム登録処理
     */
    public function saveBuckets($request, $page_id, $frame_id, $forms_id = null)
    {

        // デフォルトで必須
        $validator_values['forms_name'] = ['required'];
        $validator_attributes['forms_name'] = 'フォーム名';

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
            if (empty($forms_id)) {
                $create_flag = true;
                return $this->createBuckets($request, $page_id, $frame_id, $forms_id, $create_flag, $message, $validator->errors());
            } else {
                $create_flag = false;
                return $this->editBuckets($request, $page_id, $frame_id, $forms_id, $create_flag, $message, $validator->errors());
            }
        }

        // 更新後のメッセージ
        $message = null;

        // 画面から渡ってくるforms_id が空ならバケツとブログを新規登録
        if (empty($request->forms_id)) {
            // バケツの登録
            $bucket = new Buckets();
            $bucket->bucket_name = '無題';
            $bucket->plugin_name = 'forms';
            $bucket->save();

            // ブログデータ新規オブジェクト
            $forms = new Forms();
            $forms->bucket_id = $bucket->id;

            // Frame のBuckets を見て、Buckets が設定されていなければ、作成したものに紐づける。
            // Frame にBuckets が設定されていない ＞ 新規のフレーム＆ブログ作成
            // Frame にBuckets が設定されている ＞ 既存のフレーム＆ブログ更新
            // （表示フォーム選択から遷移してきて、内容だけ更新して、フレームに紐づけないケースもあるため）
            $frame = Frame::where('id', $frame_id)->first();
            if (empty($frame->bucket_id)) {
                // FrameのバケツIDの更新
                $frame = Frame::where('id', $frame_id)->update(['bucket_id' => $bucket->id]);
            }

            $message = 'フォーム設定を追加しました。<br />　 フォームで使用する項目を設定してください。［ <a href="/plugin/forms/editColumn/' . $page_id . '/' . $frame_id . '/">項目設定</a> ］';
        } else {
            // forms_id があれば、フォームを更新

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
        $forms->numbering_use_flag  = (empty($request->numbering_use_flag))      ? 0 : $request->numbering_use_flag;
        $forms->numbering_prefix   = $request->numbering_prefix;

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
        if ($forms_id) {
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
            return $this->editColumn($request, $page_id, $frame_id, $request->forms_id, null, $errors);
        }

        // 新規登録時の表示順を設定
        $max_display_sequence = FormsColumns::query()->where('forms_id', $request->forms_id)->max('display_sequence');
        $max_display_sequence = $max_display_sequence ? $max_display_sequence + 1 : 1;

        // 項目の登録処理
        $column = new FormsColumns();
        $column->forms_id = $request->forms_id;
        $column->column_name = $request->column_name;
        $column->column_type = $request->column_type;
        $column->required = $request->required ? \Required::on : \Required::off;
        $column->display_sequence = $max_display_sequence;
        $column->caption_color = \Bs4TextColor::dark;
        $column->save();
        $message = '項目【 '. $request->column_name .' 】を追加しました。';
        
        // 編集画面へ戻る。
        return $this->editColumn($request, $page_id, $frame_id, $request->forms_id, $message, $errors);
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
        $form_db = $this->getForms($frame_id);

        // フォームのID。まだフォームがない場合は0
        $forms_id = 0;
        if (!empty($form_db)) {
            $forms_id = $form_db->id;
        }

        // --- 画面に値を渡す準備
        $column = FormsColumns::query()->where('id', $column_id)->first();
        $selects = FormsColumnsSelects::query()->where('forms_columns_id', $column->id)->orderBy('display_sequence', 'asc')->get();

        // 編集画面テンプレートを呼び出す。
        return $this->view(
            'forms_edit_row_detail',
            [
                'forms_id' => $forms_id,
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
        if ($errors) {
            // エラーあり：入力値をフラッシュデータとしてセッションへ保存
            $request->flash();
        } else {
            // エラーなし：セッションから入力値を消去
            $request->flush();
        }

        // フレームに紐づくフォームID を探して取得
        $form_db = $this->getForms($frame_id);

        // フォームのID。まだフォームがない場合は0
        $forms_id = 0;
        if (!empty($form_db)) {
            $forms_id = $form_db->id;
        }

        // 項目データ取得
        // 予約項目データ
        $columns = FormsColumns::query()
            ->select(
                'forms_columns.id',
                'forms_columns.forms_id',
                'forms_columns.column_type',
                'forms_columns.column_name',
                'forms_columns.required',
                'forms_columns.frame_col',
                'forms_columns.caption',
                'forms_columns.caption_color',
                'forms_columns.display_sequence',
                DB::raw('count(forms_columns_selects.id) as select_count'),
                DB::raw('GROUP_CONCAT(forms_columns_selects.value order by forms_columns_selects.display_sequence SEPARATOR \',\') as select_names')
            )
            ->where('forms_columns.forms_id', $forms_id)
            // 予約項目の子データ（選択肢）
            ->leftjoin('forms_columns_selects', function ($join) {
                $join->on('forms_columns.id', '=', 'forms_columns_selects.forms_columns_id');
            })
            ->groupby(
                'forms_columns.id',
                'forms_columns.forms_id',
                'forms_columns.column_type',
                'forms_columns.column_name',
                'forms_columns.required',
                'forms_columns.frame_col',
                'forms_columns.caption',
                'forms_columns.caption_color',
                'forms_columns.display_sequence'
            )
            ->orderby('forms_columns.display_sequence')
            ->get();

        // 編集画面テンプレートを呼び出す。
        return $this->view(
            'forms_edit',
            [
                'forms_id'   => $forms_id,
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
        FormsColumns::query()->where('id', $request->column_id)->delete();
        // 項目に紐づく選択肢の削除
        $this->deleteColumnsSelects($request->column_id);
        $message = '項目【 '. $request->$str_column_name .' 】を削除しました。';

        // 編集画面へ戻る。
        return $this->editColumn($request, $page_id, $frame_id, $request->forms_id, $message, null);
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
            return $this->editColumn($request, $page_id, $frame_id, $request->forms_id, null, $errors);
        }

        // 項目の更新処理
        $column = FormsColumns::query()->where('id', $request->column_id)->first();
        $column->column_name = $request->column_name;
        $column->column_type = $request->column_type;
        $column->required = $request->required ? \Required::on : \Required::off;
        $column->save();
        $message = '項目【 '. $request->column_name .' 】を更新しました。';

        // 編集画面を呼び出す
        return $this->editColumn($request, $page_id, $frame_id, $request->forms_id, $message, $errors);
    }

    /**
     * 項目の表示順の更新
     */
    public function updateColumnSequence($request, $page_id, $frame_id)
    {
        // ボタンが押された行の施設データ
        $target_column = FormsColumns::query()
            ->where('forms_id', $request->forms_id)
            ->where('id', $request->column_id)
            ->first();

        // ボタンが押された前（後）の施設データ
        $query = FormsColumns::query()
            ->where('forms_id', $request->forms_id);
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
        return $this->editColumn($request, $page_id, $frame_id, $request->forms_id, $message, null);
    }

    /**
     * 項目に紐づく詳細情報の更新
     */
    public function updateColumnDetail($request, $page_id, $frame_id)
    {
        $column = FormsColumns::query()->where('id', $request->column_id)->first();

        $validator_values = null;
        $validator_attributes = null;

        // データ型が「まとめ行」の場合はまとめ数について必須チェック
        if ($column->column_type == \FormColumnType::group) {
            $validator_values['frame_col'] = [
                'required'
            ];
            $validator_attributes['frame_col'] = 'まとめ数';
        }
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
            $validator_attributes['rule_word_count'] = '入力文字数';
        }
        // ～日以降許容を指定時、入力値が数値であるかチェック
        if ($request->rule_date_after_equal) {
            $validator_values['rule_date_after_equal'] = [
                'numeric',
            ];
            $validator_attributes['rule_date_after_equal'] = '～日以降を許容';
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

        // 項目の更新処理
        $column->caption = $request->caption;
        $column->caption_color = $request->caption_color;
        $column->frame_col = $request->frame_col;
        // 分刻み指定
        if ($column->column_type == \FormColumnType::time) {
            $column->minutes_increments = $request->minutes_increments;
        }
        // 分刻み指定（FromTo）
        if ($column->column_type == \FormColumnType::time_from_to) {
            $column->minutes_increments_from = $request->minutes_increments_from;
            $column->minutes_increments_to = $request->minutes_increments_to;
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
        $max_display_sequence = FormsColumnsSelects::query()->where('forms_columns_id', $request->column_id)->max('display_sequence');
        $max_display_sequence = $max_display_sequence ? $max_display_sequence + 1 : 1;

        // 施設の登録処理
        $select = new FormsColumnsSelects();
        $select->forms_columns_id = $request->column_id;
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
        $select = FormsColumnsSelects::query()->where('id', $request->select_id)->first();
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
        $target_select = FormsColumnsSelects::query()
            ->where('id', $request->select_id)
            ->first();

        // ボタンが押された前（後）の施設データ
        $query = FormsColumnsSelects::query()
            ->where('forms_columns_id', $request->column_id);
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
        FormsColumnsSelects::query()->where('id', $request->select_id)->delete();

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
            DB::table('forms_columns_selects')->where('forms_columns_id', $columns_id)->delete();
        }
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
        foreach ($columns as $column) {
            $csv_array[0][$column->id] = $column->column_name;
            $copy_base[$column->id] = '';
        }

        // データ
        foreach ($input_cols as $input_col) {
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
}
