<?php

namespace App\Plugins\User\Forms;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

use Carbon\Carbon;

use App\Models\Core\Configs;
use App\Models\Common\Buckets;
use App\Models\Common\Frame;
use App\Models\Common\Uploads;
use App\Models\User\Forms\Forms;
use App\Models\User\Forms\FormsColumns;
use App\Models\User\Forms\FormsColumnsSelects;
use App\Models\User\Forms\FormsInputs;
use App\Models\User\Forms\FormsInputCols;

use App\Rules\CustomValiAlphaNumForMultiByte;
use App\Rules\CustomValiCheckWidthForString;
use App\Rules\CustomValiConfirmed;
use App\Rules\CustomValiTimeFromTo;
use App\Rules\CustomValiBothRequired;
use App\Rules\CustomValiTokenExists;
use App\Rules\CustomValiEmails;
use App\Rules\CustomValiWysiwygMax;

use App\Plugins\User\UserPluginBase;

use App\Utilities\String\StringUtils;
use App\Utilities\Token\TokenUtils;

use App\Enums\Bs4TextColor;
use App\Enums\FormAccessLimitType;
use App\Enums\FormColumnType;
use App\Enums\FormMode;
use App\Enums\FormsRegisterTargetPlugin;
use App\Enums\FormStatusType;
use App\Enums\PluginName;
use App\Enums\Required;
use App\Enums\StatusType;
use App\Models\User\Bbses\Bbs;
use App\Models\User\Bbses\BbsPost;
use App\Models\User\Blogs\Blogs;
use App\Models\User\Blogs\BlogsPosts;
use App\Models\User\Faqs\Faqs;
use App\Models\User\Faqs\FaqsPosts;
use App\Utilities\Csv\CsvUtils;

/**
 * フォーム・プラグイン
 *
 * フォームの作成＆データ収集用プラグイン。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @author 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category フォーム・プラグイン
 * @package Controller
 * @plugin_title フォーム
 * @plugin_desc フォームを作成できるプラグインです。項目を自由に作成できます。
 */
class FormsPlugin extends UserPluginBase
{
    const CHECKBOX_SEPARATOR = '|';

    /**
     * @var string フォーム名の最大長
     * @see App\Providers\AppServiceProvider::boot
     */
    const FORM_NAME_SIZE = 191;

    /* オブジェクト変数 */

    /**
     * POST チェックに使用する getPost() 関数を使うか
     */
    public $use_getpost = false;

    /* コアから呼び出す関数 */

    /**
     *  関数定義（コアから呼び出す）
     */
    public function getPublicFunctions()
    {
        // 標準関数以外で画面などから呼ばれる関数の定義
        $functions = array();
        $functions['get']  = [
            'index',
            'aggregate',
            'publicConfirmToken',
            'listInputs',
            'editInput',
            'thanks',
        ];
        $functions['post'] = [
            'index',
            'publicConfirm',
            'publicStore',
            'publicStoreToken',
            'publicPassword',
            'publicCaptcha',
            'cancel',
            'copyColumn',
            'storeInput',
            'copyForm',
            'downloadCsvAggregate',
            'registerOtherPlugins',
            'updateSelectSequenceAll',
            'updateColumnSequenceAll',
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
        $role_check_table = [];

        $role_check_table["copyColumn"]           = ['buckets.saveColumn'];
        $role_check_table["listInputs"]           = ['frames.edit'];
        $role_check_table["editInput"]            = ['frames.edit'];
        $role_check_table["storeInput"]           = ['frames.create'];
        $role_check_table["copyForm"]             = ['buckets.create'];
        $role_check_table["aggregate"]            = ['role_article'];
        $role_check_table["downloadCsvAggregate"] = ['role_article'];
        $role_check_table["registerOtherPlugins"] = ['role_article'];
        $role_check_table['updateSelectSequenceAll'] = ['buckets.upColumnSequence', 'buckets.downColumnSequence'];
        $role_check_table['updateColumnSequenceAll'] = ['buckets.upColumnSequence', 'buckets.downColumnSequence'];
        return $role_check_table;
    }

    /* private関数 */

    /**
     *  データ取得
     */
    private function getForms($frame_id)
    {
        // Forms、Frame データ
        $form = Forms::select('forms.*')
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
        $forms_columns = [];
        if (!empty($form)) {
            $forms_columns = FormsColumns::where('forms_id', $form->id)->orderBy('display_sequence')->get();
            if ($form->user_mail_send_flag == '1' && empty($forms_columns->where('column_type', FormColumnType::mail)->first())) {
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
            if ($forms_columns[$i]->column_type == FormColumnType::group) {
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
        $forms_columns_selects = FormsColumnsSelects::
            join('forms_columns', 'forms_columns.id', '=', 'forms_columns_selects.forms_columns_id')
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
        $frame = Frame::select('frames.*', 'forms.id as forms_id')
            ->leftJoin('forms', 'forms.bucket_id', '=', 'frames.bucket_id')
            ->where('frames.id', $frame_id)
            ->first();
        return $frame;
    }

    /**
     * ファイル系の詳細データの取得
     */
    private function getUploadsInputCols($inputs_id)
    {
        $records = FormsInputCols::select('uploads.*', 'forms_columns.column_type', 'forms_input_cols.forms_columns_id as columns_id', 'forms_input_cols.value')
                                    ->join('forms_columns', 'forms_columns.id', '=', 'forms_input_cols.forms_columns_id')
                                    ->leftJoin('uploads', 'uploads.id', '=', 'forms_input_cols.value')
                                    ->where('forms_inputs_id', $inputs_id)
                                    ->whereIn('forms_columns.column_type', [FormColumnType::file])
                                    ->orderBy('forms_inputs_id', 'asc')
                                    ->orderBy('forms_columns_id', 'asc')
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
     * データ初期表示関数
     * コアがページ表示の際に呼び出す関数
     *
     * @method_title 入力
     * @method_desc 設定した項目で入力画面が表示されます。
     * @method_detail
     */
    public function index($request, $page_id, $frame_id, $errors = null)
    {
        // Forms、Frame データ
        $form = $this->getForms($frame_id);

        // フォーム送信時Captcha認証の場合、入力画面表示時に毎回セッションを初期化
        // 確認画面からのキャンセルやブラウザ戻るなど、どんな経路でも新しい状態から開始
        if ($form && $form->access_limit_type == FormAccessLimitType::captcha_form_submit) {
            session()->forget("captcha_validated_{$frame_id}");
        }

        $setting_error_messages = null;
        $forms_columns = null;
        $forms_columns_id_select = null;
        if ($form) {
            $forms_columns_id_select = $this->getFormsColumnsSelects($form->id);
            if (FormsColumns::query()
                ->where('forms_id', $form->id)
                ->where('column_type', FormColumnType::group)
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

            // 表示期間外か
            if ($this->isOutOfTermDisplay($form)) {
                // 表示しない
                return false;
            }

            // 登録期間外か
            if ($this->isOutOfTermRegist($form)) {
                // エラー画面へ
                return $this->commonView('error_messages', [
                    'error_messages' => ['登録期間外のため、登録出来ません。'],
                ]);
            }

            // 登録制限数オーバーか
            if ($this->isOverEntryLimit($form->id, $form->entry_limit)) {
                // $setting_error_messages[] = '制限数に達したため登録を終了しました。';
                // エラー画面へ
                return $this->commonView('error_messages', [
                    'error_messages' => [$form->entry_limit_over_message],
                ]);
            }

            ////
            //// 項目名でGETパラメータ取得して、対応する項目にリクエストセット
            ////
            // URLのなかに'/plugin/forms/index'が含まれている場合（getのパラメータを含める時のみindexをURLに含められてる想定）
            // 同一ページに複数フォームある場合、１つのフォームを登録して確認画面を表示するが、他は初期表示のため、リクエストが上書きされてしまうことを防ぐ。
            if ($request->isMethod('post') && strpos($request->url(), '/plugin/forms/index') !== false) {
                // まとめ行に対応してない素の状態のFormsColumnsが欲しいため、再取得
                $tmp_forms_columns = FormsColumns::where('forms_id', $form->id)->orderBy('display_sequence')->get();

                $forms_columns_value = $request->forms_columns_value;
                // 同じメールアドレス用
                $forms_columns_value_confirmation = $request->forms_columns_value_confirmation;
                // 時間型（FromTo）用
                $forms_columns_value_for_time_from = $request->forms_columns_value_for_time_from;
                $forms_columns_value_for_time_to = $request->forms_columns_value_for_time_to;

                foreach ($tmp_forms_columns as $tmp_forms_column) {

                    if (! isset($forms_columns_value[$tmp_forms_column->id])) {
                        // 入力なし
                        // 初期は入力なしのため、getでカラム名あればその値、なければnullで配列埋める

                        if ($tmp_forms_column->column_type == FormColumnType::mail) {
                            // メール
                            // 同じメールアドレスを埋める
                            $forms_columns_value_confirmation[$tmp_forms_column->id] = $request->input($tmp_forms_column->column_name, null);
                            $forms_columns_value[$tmp_forms_column->id] = $request->input($tmp_forms_column->column_name, null);
                        } elseif ($tmp_forms_column->column_type == FormColumnType::checkbox) {
                            // チェックボックス
                            $checkbox = $request->input($tmp_forms_column->column_name, null);
                            $checkbox = explode(',', $checkbox);

                            $forms_columns_value[$tmp_forms_column->id] = $checkbox;
                        } elseif ($tmp_forms_column->column_type == FormColumnType::time_from_to) {
                            // 時間型(FromTo)
                            $from_to = $request->input($tmp_forms_column->column_name, null);
                            $from_to = str_replace('~', '～', $from_to);
                            $from_to = explode('～', $from_to);

                            $forms_columns_value_for_time_from[$tmp_forms_column->id] = isset($from_to[0]) ? $from_to[0] : null;
                            $forms_columns_value_for_time_to[$tmp_forms_column->id] = isset($from_to[1]) ? $from_to[1] : null;
                        } elseif ($tmp_forms_column->column_type == FormColumnType::group) {
                            // まとめ行(なにもしない)
                        } else {
                            // その他
                            $forms_columns_value[$tmp_forms_column->id] = $request->input($tmp_forms_column->column_name, null);
                        }

                        $request->merge([
                            "forms_columns_value" => $forms_columns_value,
                            "forms_columns_value_confirmation" => $forms_columns_value_confirmation,
                            "forms_columns_value_for_time_from" => $forms_columns_value_for_time_from,
                            "forms_columns_value_for_time_to" => $forms_columns_value_for_time_to,
                        ]);
                    }
                }
            }
        } else {
            // フレームに紐づくフォーム親データがない場合
            $setting_error_messages[] = 'フレームの設定画面から、使用するフォームを選択するか、作成してください。';
        }

        if (empty($setting_error_messages)) {

            if ($form->access_limit_type == FormAccessLimitType::password) {
                if (session('can_view_form_password' . $frame_id)) {
                    // 閲覧OKならパスワード画面を表示しない
                } else {
                    // 閲覧パスワード
                    return $this->view('index_password', [
                        'form' => $form,
                    ]);
                }
            } elseif ($form->access_limit_type == FormAccessLimitType::captcha) {
                if (session('can_view_form_captcha' . $frame_id)) {
                    // 閲覧OKならcaptcha画面を表示しない
                } else {
                    // 画像認証
                    return $this->view('index_captcha', [
                        'form' => $form,
                    ]);
                }
            }

            if ($form->form_mode == FormMode::form) {
                // フォーム
                return $this->view('forms', [
                    'request' => $request,
                    'form' => $form,
                    'forms_columns' => $forms_columns,
                    'forms_columns_id_select' => $forms_columns_id_select,
                    'errors' => $errors,
                ]);
            } else {
                // アンケート
                return $this->view('index_tandem', [
                    'request' => $request,
                    'form' => $form,
                    'forms_columns' => $forms_columns,
                    'forms_columns_id_select' => $forms_columns_id_select,
                    'errors' => $errors,
                ]);
            }
        } else {
            // エラーあり
            return $this->commonView('error_messages', [
                'setting_error_messages' => $setting_error_messages,
            ]);
        }
    }

    /**
     * 登録制限数オーバーか
     */
    private function isOverEntryLimit($form_id, $entry_limit)
    {
        // カウントは本登録でする
        $forms_inputs_count = FormsInputs::where('forms_id', $form_id)
                                            ->where('status', FormStatusType::active)
                                            ->count();

        // 登録制限数 が 空か 0 なら登録制限しない
        if ($entry_limit != null && $entry_limit !== 0) {
            if ($forms_inputs_count >= $entry_limit) {
                return true;
            }
        }

        return false;
    }

    /**
     * 表示期間外か
     */
    private function isOutOfTermDisplay($form)
    {
        if (! $form->display_control_flag) {
            // 制御フラグOFFなら、表示期間内として扱う
            return false;
        }

        // 値あり & 今から見てFromが未来か
        if ($form->display_from && $form->display_from->isFuture()) {
            // 期間外
            return true;
        }

        // 値あり & 今日から見てToが過去か
        if ($form->display_to && $form->display_to->isPast()) {
            // 期間外
            return true;
        }

        // 期間内
        return false;
    }

    /**
     * 登録期間外か
     */
    private function isOutOfTermRegist($form)
    {
        if (! $form->regist_control_flag) {
            // 制御フラグOFFなら、登録期間内として扱う
            return false;
        }

        // 値あり & 今から見てFromが未来か
        if ($form->regist_from && $form->regist_from->isFuture()) {
            // 期間外
            return true;
        }

        // 値あり & 今日から見てToが過去か
        if ($form->regist_to && $form->regist_to->isPast()) {
            // 期間外
            return true;
        }

        // 期間内
        return false;
    }

    /**
     * セットすべきバリデータールールが存在する場合、受け取った配列にセットして返す
     *
     * @param [array] $validator_array 二次元配列
     * @param [App\Models\User\Forms\FormsColumns] $forms_column
     * @param Request $request
     * @return array
     */
    private function getValidatorRule($validator_array, $forms_column, $request)
    {
        $validator_rule = null;
        // 必須チェック
        if ($forms_column->required) {
            $validator_rule[] = 'required';
        }
        // メールアドレスチェック
        if ($forms_column->column_type == FormColumnType::mail) {
            $validator_rule[] = 'nullable';
            $validator_rule[] = 'email';
            // 同値チェック
            $validator_rule[] = new CustomValiConfirmed($forms_column->column_name, $request->forms_columns_value_confirmation[$forms_column->id]);
        }
        // 数値チェック
        if ($forms_column->rule_allowed_numeric) {
            // 入力値があった場合（マイナスを意図した入力記号はすべて半角に置換する）＆ 全角→半角へ丸める
            $tmp_numeric_columns_value = StringUtils::convertNumericAndMinusZenkakuToHankaku($request->forms_columns_value[$forms_column->id]);

            $tmp_array = $request->forms_columns_value;
            $tmp_array[$forms_column->id] = $tmp_numeric_columns_value;

            $request->merge([
                "forms_columns_value" => $tmp_array,
            ]);

            $validator_rule[] = 'nullable';
            $validator_rule[] = 'numeric';
        }
        // 英数値チェック
        if ($forms_column->rule_allowed_alpha_numeric) {
            $validator_rule[] = 'nullable';
            $validator_rule[] = new CustomValiAlphaNumForMultiByte();
        }
        // 最大文字数チェック
        if ($forms_column->rule_word_count) {
            $validator_rule[] = new CustomValiCheckWidthForString($forms_column->column_name, $forms_column->rule_word_count);
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
        // 正規表現チェック
        if ($forms_column->rule_regex) {
            $validator_rule[] = 'nullable';
            $validator_rule[] = 'regex:' . $forms_column->rule_regex;
        }
        // ～日以降を許容
        if ($forms_column->rule_date_after_equal) {
            $comparison_date = Carbon::now()->addDay($forms_column->rule_date_after_equal)->format('Y/m/d');
            $validator_rule[] = 'after_or_equal:' . $comparison_date;
        }
        // 日付チェック
        if ($forms_column->column_type == FormColumnType::date) {
            $validator_rule[] = 'nullable';
            $validator_rule[] = 'date';
        }
        // 「時間From~To型」チェック
        if ($forms_column->column_type == FormColumnType::time_from_to) {
            $time_from = $request->forms_columns_value_for_time_from[$forms_column->id];
            $time_to = $request->forms_columns_value_for_time_to[$forms_column->id];
            // request内の入力値（配列）を一旦取り出して、時間型（From~To）の入力値をセットする
            $tmp_array = $request->forms_columns_value;
            $tmp_array[$forms_column->id] = null;
            if ($time_from && $time_to) {
                if (strtotime('1970-01-01 '. $time_from . ':00') !== false && strtotime('1970-01-01 '. $time_to . ':00') !== false) {
                    // 両方入力時、且つ、正常時間の場合、時間の前後チェック
                    $validator_rule[] = new CustomValiTimeFromTo(
                        Carbon::createFromTimeString($time_from . ':00'),
                        Carbon::createFromTimeString($time_to . ':00')
                    );
                } else {
                    // 不正時間の為、dateバリデーションで弾く
                    $validator_rule[] = 'date';
                }
                $tmp_array[$forms_column->id] = $time_from . '~' . $time_to;
            } elseif ($time_from || $time_to) {
                // いづれか入力時、条件必須チェック（いづれか入力時、両方必須）
                $validator_rule[] = new CustomValiBothRequired(
                    $request->forms_columns_value_for_time_from[$forms_column->id],
                    $request->forms_columns_value_for_time_to[$forms_column->id]
                );
                $tmp_array[$forms_column->id] = $time_from . '~' . $time_to;
            }
            $request->merge([
                "forms_columns_value" => $tmp_array,
            ]);
        }
        // バリデータールールをセット
        if ($validator_rule) {
            $validator_array['column']['forms_columns_value.' . $forms_column->id] = $validator_rule;
            $validator_array['message']['forms_columns_value.' . $forms_column->id] = $forms_column->column_name;
        }

        return $validator_array;
    }

    /**
     * 集計結果
     *
     * @method_title 集計結果
     * @method_desc 設定した権限の人は入力されたデータを一覧表示できます。登録一覧とは違い表示のみで、編集はできません。
     * @method_detail 入力されたデータのダウンロードもできます。
     */
    public function aggregate($request, $page_id, $frame_id, $forms_id = null)
    {
        // フォーム＆フレームデータ
        $form_frame = $this->getFormFrame($frame_id);

        $form = null;
        if (!empty($forms_id)) {
            // forms_id が渡ってくればforms_id が対象
            $form = Forms::where('id', $forms_id)->first();
        } elseif (!empty($form_frame->bucket_id)) {
            // Frame のbucket_id があれば、bucket_id からフォームデータ取得
            $form = Forms::where('bucket_id', $form_frame->bucket_id)->first();
        }

        if (empty($form)) {
            return $this->viewError("404_inframe", null, 'form is null');
        }
        if (!$form->can_view_inputs_moderator) {
            return $this->viewError("403_inframe", null, 'モデレータは集計結果を表示できる=off');
        }

        // カラムの取得
        $columns = FormsColumns::where('forms_id', $form->id)->orderBy('display_sequence', 'asc')->get();

        $inputs_query = FormsInputs::where('forms_id', $form->id);
        $inputs_query->orderBy('forms_inputs.created_at', 'desc');

        // データ取得
        $get_count = 10;
        $inputs = $inputs_query->paginate($get_count, ["*"], "frame_{$frame_id}_page");

        // 登録データ詳細の取得
        $input_cols = FormsInputCols::select('forms_input_cols.*', 'uploads.client_original_name')
            ->leftJoin('uploads', 'uploads.id', '=', 'forms_input_cols.value')
            ->whereIn('forms_inputs_id', $inputs->pluck('id'))
            ->orderBy('forms_inputs_id', 'asc')->orderBy('forms_columns_id', 'asc')
            ->get();

        // 表示テンプレートを呼び出す。
        return $this->view('aggregate', [
            'form' => $form,
            'columns' => $columns,
            'inputs' => $inputs,
            'input_cols' => $input_cols,
        ]);
    }

    /**
     * 閲覧パスワード確認
     */
    public function publicPassword($request, $page_id, $frame_id)
    {
        // Forms、Frame データ
        $form = $this->getForms($frame_id);

        // エラーチェック
        if ($form->form_password != $request->form_password) {
            return redirect()->back()->withErrors(['form_password' => '閲覧パスワードが異なります。'])->withInput();
        }

        // 一時セッションで閲覧を許可
        session()->flash('can_view_form_password' . $frame_id, 1);

        // リダイレクト先を指定しないため、画面から渡されたredirect_pathに飛ぶ
    }

    /**
     * 画像認証確認
     */
    public function publicCaptcha($request, $page_id, $frame_id)
    {
        // 項目のエラーチェック
        $validator = Validator::make($request->all(), [
            'captcha' => ['required', 'captcha'],
        ]);

        $validator->setAttributeNames([
            'captcha' => '画像認証',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // 一時セッションで閲覧を許可
        session()->flash('can_view_form_captcha' . $frame_id, 1);

        // リダイレクト先を指定しないため、画面から渡されたredirect_pathに飛ぶ
    }

    /**
     * 登録時の確認
     */
    public function publicConfirm($request, $page_id, $frame_id, $id = null)
    {
        // Forms、Frame データ
        $form = $this->getForms($frame_id);

        // 表示期間外か
        if ($this->isOutOfTermDisplay($form)) {
            // 表示しない
            return false;
        }

        // 登録期間外か
        if ($this->isOutOfTermRegist($form)) {
            // エラー画面へ
            return $this->commonView('error_messages', [
                'error_messages' => ['登録期間外のため、登録出来ません。'],
            ]);
        }

        // 登録制限数オーバーか
        if ($this->isOverEntryLimit($form->id, $form->entry_limit)) {
            // エラー画面へ
            return $this->commonView('error_messages', [
                'error_messages' => [$form->entry_limit_over_message],
            ]);
        }

        // フォームのカラムデータ
        $forms_columns = $this->getFormsColumns($form);

        // ファイル系の詳細データ
        $uploads = collect();
        if ($id) {
            $uploads = $this->getUploadsInputCols($id);
        }

        // エラーチェック配列
        $validator_array = array('column' => array(), 'message' => array());

        foreach ($forms_columns as $forms_column) {
            // まとめ行であれば、ネストされた配列をさらに展開
            if ($forms_column->group) {
                foreach ($forms_column->group as $group_item) {
                    // まとめ行で指定している項目について、バリデータールールをセット
                    $validator_array = $this->getValidatorRule($validator_array, $group_item, $request);
                }
            } else {
                // まとめ行以外の項目について、バリデータールールをセット
                $validator_array = $this->getValidatorRule($validator_array, $forms_column, $request);
            }
        }

        // 入力値をトリム
        // bugfix: 【データベース】（Laravel6テスト）ファイル型項目にファイルをアップするとシステムエラーと同じ対応 https://github.com/opensource-workshop/connect-cms/issues/732
        foreach ($forms_columns as $forms_column) {
            // ファイルタイプ以外の入力値をトリム
            if (! FormsColumns::isFileColumnType($forms_column->column_type)) {
                if (isset($request->forms_columns_value[$forms_column->id])) {
                    // 一度配列にして、trim後、また文字列に戻す。
                    $tmp_columns_value = StringUtils::trimInput($request->forms_columns_value[$forms_column->id]);

                    $tmp_array = $request->forms_columns_value;
                    $tmp_array[$forms_column->id] = $tmp_columns_value;
                    $request->merge([
                        "forms_columns_value" => $tmp_array,
                    ]);
                }
            }
        }

        // フォーム送信時Captcha認証の場合、入力画面でCaptcha検証を実行
        if ($form->access_limit_type == FormAccessLimitType::captcha_form_submit) {
            $validator_array['column']['captcha'] = ['required', 'captcha'];
            $validator_array['message']['captcha'] = '画像認証';
        }

        // 項目のエラーチェック
        $validator = Validator::make($request->all(), $validator_array['column']);
        $validator->setAttributeNames($validator_array['message']);

        // エラーがあった場合は入力画面に戻る。
        if ($validator->fails()) {

            // ファイル項目を探してセッション対象から除く
            $flashExcepts = [];
            foreach ($forms_columns as $forms_column) {
                if (FormsColumns::isFileColumnType($forms_column->column_type)) {
                    $flashExcepts[] = 'forms_columns_value.' . $forms_column->id;
                }
            }
            // 入力をフラッシュデータとして保存
            $request->flashExcept($flashExcepts);

            return $this->index($request, $page_id, $frame_id, $validator->errors());
        }

        // フォーム送信時Captcha認証の場合、認証成功フラグをセッションに保存
        // mews/captchaライブラリのCache::pull()による一回限り制限を回避するため
        // 入力画面での認証成功をセッションに記録し、最終送信時の重複検証を防ぐ
        if ($form->access_limit_type == FormAccessLimitType::captcha_form_submit) {
            $captcha_success_key = "captcha_validated_{$frame_id}";
            session()->put($captcha_success_key, [
                'session_id' => session()->getId()
            ]);
        }

        // ファイル項目を探して保存
        foreach ($forms_columns as $forms_column) {
            if (FormsColumns::isFileColumnType($forms_column->column_type)) {
                // ファイルのリクエスト名
                $req_filename = 'forms_columns_value.' . $forms_column->id;

                // ファイルがアップロードされた。
                if ($request->hasFile($req_filename)) {
                    // ファイルチェック

                    // uploads テーブルに情報追加、ファイルのid を取得する
                    $upload = Uploads::create([
                        'client_original_name' => $request->file($req_filename)->getClientOriginalName(),
                        'mimetype'             => $request->file($req_filename)->getClientMimeType(),
                        'extension'            => $request->file($req_filename)->getClientOriginalExtension(),
                        'size'                 => $request->file($req_filename)->getSize(),
                        'plugin_name'          => 'forms',
                        'check_method'         => 'canDownload',
                        'page_id'              => $page_id,
                        'temporary_flag'       => 1,
                        'created_id'           => empty(Auth::user()) ? null : Auth::user()->id,
                    ]);

                    // ファイル保存
                    $directory = $this->getDirectory($upload->id);
                    $upload_path = $request->file($req_filename)->storeAs($directory, $upload->id . '.' . $request->file($req_filename)->getClientOriginalExtension());

                    // 項目とファイルID の関連保持
                    $upload->column_type = $forms_column->column_type;
                    $upload->columns_id = $forms_column->id;

                    // ここで、put でキー指定でセットすることで、紐づくファイル情報が変更される。
                    $uploads->put($forms_column->id, $upload);
                }
            }
        }

        // 表示テンプレートを呼び出す
        if ($form->form_mode == FormMode::form) {
            // フォーム
            return $this->view('forms_confirm', [
                'request' => $request,
                'frame_id' => $frame_id,
                'form' => $form,
                'forms_columns' => $forms_columns,
                'uploads' => $uploads,
            ]);
        } else {
            // アンケート
            return $this->view('forms_confirm_tandem', [
                'request' => $request,
                'frame_id' => $frame_id,
                'form' => $form,
                'forms_columns' => $forms_columns,
                'uploads' => $uploads,
            ]);
        }
    }

    /**
     * データ登録
     */
    public function publicStore($request, $page_id, $frame_id, $id = null)
    {
        // Forms、Frame データ
        $form = $this->getForms($frame_id);

        // フォーム送信時Captcha認証の場合、セッションフラグをチェック
        if ($form->access_limit_type == FormAccessLimitType::captcha_form_submit) {
            $captcha_success_key = "captcha_validated_{$frame_id}";
            $captcha_validated = session()->get($captcha_success_key);

            if ($captcha_validated) {
                // セッション整合性検証（セッションID）
                $current_session_id = session()->getId();
                $session_id = $captcha_validated['session_id'] ?? '';

                if ($session_id !== $current_session_id) {
                    // セッション整合性エラー：入力画面にリダイレクト
                    session()->forget($captcha_success_key);
                    return collect(['redirect_path' => url($this->page->permanent_link)]);
                }

                // 入力画面で認証済み：認証フラグを削除して処理続行
                session()->forget($captcha_success_key);

            } else {
                // 認証フラグがない場合は入力画面にリダイレクト
                return collect(['redirect_path' => url($this->page->permanent_link)]);
            }
        }

        // 表示期間外か
        if ($this->isOutOfTermDisplay($form)) {
            // 初期表示にリダイレクトして、初期表示処理にまかせる（表示しない）
            return collect(['redirect_path' => url($this->page->permanent_link)]);
        }

        // 登録期間外か
        if ($this->isOutOfTermRegist($form)) {
            // 初期表示にリダイレクトして、初期表示処理にまかせる（エラー表示）
            return collect(['redirect_path' => url($this->page->permanent_link)]);
        }

        // 登録制限数オーバーか
        if ($this->isOverEntryLimit($form->id, $form->entry_limit)) {
            // 初期表示にリダイレクトして、初期表示処理にまかせる（エラー表示）
            return collect(['redirect_path' => url($this->page->permanent_link)]);
        }

        // forms_inputs 登録
        $forms_inputs = new FormsInputs();
        $forms_inputs->forms_id = $form->id;

        $user_token = null;
        $number = null;
        if ($form->use_temporary_regist_mail_flag) {
            // 仮登録
            // トークン生成 (メール送信用でユーザのみ知る. DB保存しない)
            $user_token = TokenUtils::createNewToken();
            // トークンをハッシュ化（DB保存用）
            $record_token = TokenUtils::makeHashToken($user_token);

            $forms_inputs->status = FormStatusType::temporary;
            $forms_inputs->add_token = $record_token;
            $forms_inputs->add_token_created_at = new Carbon();
        } else {
            // 本登録
            $forms_inputs->status = FormStatusType::active;
            if ($form->numbering_use_flag) {
                // 採番は本登録の時のみする ※[採番プレフィックス文字列] + [ゼロ埋め採番6桁]
                $number = $form->numbering_prefix . sprintf('%06d', $this->getNo('forms', $form->bucket_id, $form->numbering_prefix));
                $forms_inputs->number_with_prefix = $number;
            }
        }

        $forms_inputs->save();

        // フォームのカラムデータ
        $forms_columns = FormsColumns::where('forms_id', $form->id)->orderBy('display_sequence')->get();

        // メールの送信文字列
        $contents_text = '';

        // 登録者のメールアドレス
        $user_mailaddresses = array();

        // 添付ファイルID
        $attach_uploads_ids = [];

        $no = 1;

        // forms_input_cols 登録
        foreach ($forms_columns as $forms_column) {
            if ($forms_column->column_type == FormColumnType::group) {
                continue;
            }

            $value = "";
            if (is_array($request->forms_columns_value[$forms_column->id])) {
                $value = implode(self::CHECKBOX_SEPARATOR, $request->forms_columns_value[$forms_column->id]);
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

                // ファイルタイプがファイル系の場合は、uploads テーブルの一時フラグを更新
                if (FormsColumns::isFileColumnType($forms_column->column_type)) {
                    $uploads_count = Uploads::where('id', $value)->update(['temporary_flag' => 0]);
                }
            }

            // ファイルタイプがファイル系 で 値がある事
            if (FormsColumns::isFileColumnType($forms_column->column_type) && $value) {
                $attach_uploads_ids[] = $value;

                // データ登録フラグOFFでも、一時的にファイルは持っているため、検索可能
                $upload = Uploads::where('id', $value)->first();

                // メールの内容(ファイル系は、ファイル名を載せる)
                if ($form->form_mode == FormMode::form) {
                    // フォーム
                    $contents_text .= $forms_column->column_name . "：" . $upload->client_original_name . "\n";
                } else {
                    // アンケート
                    $column_name = strip_tags($forms_column->column_name);
                    $contents_text .= "Q{$no} {$column_name}：\n{$upload->client_original_name}\n\n";
                }

            } else {
                // メールの内容
                if ($form->form_mode == FormMode::form) {
                    // フォーム
                    $contents_text .= $forms_column->column_name . "：" . $value . "\n";
                } else {
                    // アンケート
                    $column_name = strip_tags($forms_column->column_name);
                    $contents_text .= "Q{$no} {$column_name}：\n$value\n\n";
                }
            }

            // メール型
            if ($forms_column->column_type == FormColumnType::mail) {
                $user_mailaddresses[] = $value;
            }

            $no++;
        }
        // 最後の改行を除去
        $contents_text = trim($contents_text);

        if ($form->use_temporary_regist_mail_flag) {
            // *** 仮登録
            // ユーザ側のみメール送信する

            $after_message = $form->temporary_regist_after_message;
            session()->flash("after_message{$frame_id}", $after_message);

            // メール送信
            // メール件名の組み立て
            $subject = $form->temporary_regist_mail_subject;

            // メール件名内のサイト名文字列を置換
            $subject = str_replace('[[site_name]]', Configs::where('name', 'base_site_name')->first()->value, $subject);
            // メール件名内のフォーム名文字列を置換
            $subject = str_replace('[[form_name]]', $form->forms_name, $subject);
            // メール件名内の登録日時を置換
            $todatetime = date("Y/m/d H:i:s");
            $subject = str_replace('[[to_datetime]]', $todatetime, $subject);

            // メール本文の組み立て
            $mail_format = $form->temporary_regist_mail_format;
            $mail_text = str_replace('[[body]]', $contents_text, $mail_format);

            // 本登録URL
            $entry_url = url('/') . "/plugin/forms/publicConfirmToken/{$page_id}/{$frame_id}/{$forms_inputs->id}?token={$user_token}#frame-{$frame_id}";
            $mail_text = str_replace('[[entry_url]]', $entry_url, $mail_text);
            // メール本文内のサイト名文字列を置換
            $mail_text = str_replace('[[site_name]]', Configs::where('name', 'base_site_name')->first()->value, $mail_text);
            // メール本文内のフォーム名文字列を置換
            $mail_text = str_replace('[[form_name]]', $form->forms_name, $mail_text);
            // メール本文内の登録日時を置換
            $mail_text = str_replace('[[to_datetime]]', $todatetime, $mail_text);

            // メールオプション
            $mail_options = ['subject' => $subject, 'template' => 'mail.send'];
            // メールオプションに添付ファイルをセットする
            $mail_options = $this->setMailOptionsAttach($attach_uploads_ids, $form->mail_attach_flag, $mail_options);

            // メール送信（ユーザー側）
            foreach ($user_mailaddresses as $user_mailaddress) {
                if (!empty($user_mailaddress)) {
                    // メール送信はログ出力の追加に伴いTrait のメソッドに移行
                    $this->sendMail($user_mailaddress, $mail_options, ['content' => $mail_text], $this->getPluginName());
                }
            }
        } else {
            // *** 本登録
            // 登録後メッセージ内の採番文字列を置換
            $after_message = str_replace('[[number]]', $number, $form->after_message);
            session()->flash("after_message{$frame_id}", $after_message);

            // メール送信
            if ($form->mail_send_flag || $form->user_mail_send_flag) {
                // メール件名の組み立て
                $subject = $form->mail_subject;

                // メール本文内の採番文字列を置換
                $subject = str_replace('[[number]]', $number, $subject);
                // メール件名内のサイト名文字列を置換
                $subject = str_replace('[[site_name]]', Configs::where('name', 'base_site_name')->first()->value, $subject);
                // メール件名内のフォーム名文字列を置換
                $subject = str_replace('[[form_name]]', $form->forms_name, $subject);
                // メール件名内の登録日時を置換
                $todatetime = date("Y/m/d H:i:s");
                $subject = str_replace('[[to_datetime]]', $todatetime, $subject);

                // メール本文の組み立て
                $mail_format = $form->mail_format;
                $mail_text = str_replace('[[body]]', $contents_text, $mail_format);

                // メール本文内の採番文字列を置換
                $mail_text = str_replace('[[number]]', $number, $mail_text);
                // メール本文内のサイト名文字列を置換
                $mail_text = str_replace('[[site_name]]', Configs::where('name', 'base_site_name')->first()->value, $mail_text);
                // メール本文内のフォーム名文字列を置換
                $mail_text = str_replace('[[form_name]]', $form->forms_name, $mail_text);
                // メール本文内の登録日時を置換
                $mail_text = str_replace('[[to_datetime]]', $todatetime, $mail_text);

                // メールオプション
                $mail_options = ['subject' => $subject, 'template' => 'mail.send'];
                // メールオプションに添付ファイルをセットする
                $mail_options = $this->setMailOptionsAttach($attach_uploads_ids, $form->mail_attach_flag, $mail_options);

                // メール送信（管理者側）
                if ($form->mail_send_flag) {
                    $mail_addresses = explode(',', $form->mail_send_address);
                    foreach ($mail_addresses as $mail_address) {
                        // メール送信はログ出力の追加に伴いTrait のメソッドに移行
                        $this->sendMail($mail_address, $mail_options, ['content' => $mail_text], $this->getPluginName());
                    }
                }

                // メール送信（ユーザー側）
                if ($form->user_mail_send_flag) {
                    foreach ($user_mailaddresses as $user_mailaddress) {
                        if (!empty($user_mailaddress)) {
                            // メール送信はログ出力の追加に伴いTrait のメソッドに移行
                            $this->sendMail($user_mailaddress, $mail_options, ['content' => $mail_text], $this->getPluginName());
                        }
                    }
                }
            }
        }

        // データ登録フラグOFFなら、添付ファイルを削除
        if (! $form->data_save_flag) {
            // アップロードファイルあり
            if (! empty($attach_uploads_ids)) {
                // 削除するファイルデータ
                $delete_uploads = Uploads::whereIn('id', $attach_uploads_ids)->get();

                foreach ($delete_uploads as $delete_upload) {
                    // ファイルの削除
                    $directory = $this->getDirectory($delete_upload->id);
                    Storage::delete($directory . '/' . $delete_upload->id . '.' .$delete_upload->extension);

                    // uploadの削除
                    $delete_upload->delete();
                }
            }
        }

        // 登録後のリダイレクト表示
        return collect(['redirect_path' => url('/') . "/plugin/forms/thanks/{$page_id}/{$frame_id}#frame-{$frame_id}"]);
    }

    /**
     * 登録後画面
     */
    public function thanks($request, $page_id, $frame_id, $id = null)
    {
        return $this->view('forms_thanks', [
            'after_message' => session("after_message{$frame_id}")
        ]);
    }

    /**
     * メールオプションに添付ファイルをセットする
     */
    private function setMailOptionsAttach($attach_uploads_ids, $mail_attach_flag, $mail_options)
    {
        // メール添付OFF
        if (empty($mail_attach_flag)) {
            // なにもしない
            return $mail_options;
        }

        // アップロードファイルIDなし
        if (empty($attach_uploads_ids)) {
            // なにもしない
            return $mail_options;
        }

        $attach_uploads = Uploads::whereIn('id', $attach_uploads_ids)->get();

        foreach ($attach_uploads as $attach_upload) {
            // 添付ファイルパスの取得
            $directory = $this->getDirectory($attach_upload->id);
            $mail_options['attachs'][] = [
                'file_path' => storage_path('app/' . $directory . '/' . $attach_upload->id . '.' .$attach_upload->extension),
                'file_name' => $attach_upload->client_original_name,
                'mime' => $attach_upload->mimetype,
            ];
        }

        return $mail_options;
    }

    /**
     * トークンを使った本登録の確定画面表示
     */
    public function publicConfirmToken($request, $page_id, $frame_id, $id)
    {
        // Forms、Frame データ
        $form = $this->getForms($frame_id);

        // 表示期間外か
        if ($this->isOutOfTermDisplay($form)) {
            // 表示しない
            return false;
        }

        // 登録期間外か
        if ($this->isOutOfTermRegist($form)) {
            // エラー画面へ
            return $this->commonView('error_messages', [
                'error_messages' => ['登録期間外のため、登録出来ません。'],
            ]);
        }

        // 登録制限数オーバーか
        if ($this->isOverEntryLimit($form->id, $form->entry_limit)) {
            // エラー画面へ
            return $this->commonView('error_messages', [
                'error_messages' => [$form->entry_limit_over_message],
            ]);
        }

        // $id がなかったら、エラー画面へ
        // $forms_inputs がなかったら、エラー画面へ
        $forms_inputs = FormsInputs::find($id);
        if (empty($forms_inputs)) {
            return $this->commonView('error_messages', [
                'error_messages' => ['有効期限切れのため、そのURLはご利用できません。'],
            ]);
        }

        // 項目のエラーチェック
        $validator = Validator::make($request->all(), [
            'token' => new CustomValiTokenExists($forms_inputs->add_token, $forms_inputs->add_token_created_at),
        ]);

        // getで日付形式エラーは表示しない（通常URLをコピペミス等でいじらなければエラーにならない想定）
        if ($validator->fails()) {
            return $this->commonView('error_messages', [
                'error_messages' => $validator->errors()->all(),
            ]);
        }

        // 表示テンプレートを呼び出す。
        return $this->view('forms_confirm_token', [
            'id' => $id,
            'token' => $request->token,
        ]);
    }

    /**
     * トークンを使ったデータ本登録
     */
    public function publicStoreToken($request, $page_id, $frame_id, $id)
    {
        // Forms、Frame データ
        $form = $this->getForms($frame_id);

        // 表示期間外か
        if ($this->isOutOfTermDisplay($form)) {
            // 初期表示にリダイレクトして、初期表示処理にまかせる（表示しない）
            return collect(['redirect_path' => url($this->page->permanent_link)]);
        }

        // 登録期間外か
        if ($this->isOutOfTermRegist($form)) {
            // 初期表示にリダイレクトして、初期表示処理にまかせる（エラー表示）
            return collect(['redirect_path' => url($this->page->permanent_link)]);
        }

        // 登録制限数オーバーか
        if ($this->isOverEntryLimit($form->id, $form->entry_limit)) {
            // 初期表示にリダイレクトして、初期表示処理にまかせる（エラー表示）
            return collect(['redirect_path' => url($this->page->permanent_link)]);
        }

        // $id がなかったら、エラー画面へ
        // $forms_inputs がなかったら、エラー画面へ
        $forms_inputs = FormsInputs::find($id);
        if (empty($forms_inputs)) {
            session()->flash("error_messages{$frame_id}", '有効期限切れのため、そのURLはご利用できません。');

            // 初期表示にリダイレクトして、初期表示処理にまかせる（エラー表示）
            return collect(['redirect_path' => url($this->page->permanent_link)]);
        }

        // 項目のエラーチェック
        $validator = Validator::make($request->all(), [
            'token' => new CustomValiTokenExists($forms_inputs->add_token, $forms_inputs->add_token_created_at),
        ]);

        // getで日付形式エラーは表示しない（通常URLをコピペミス等でいじらなければエラーにならない想定）
        if ($validator->fails()) {
            $error_messages = $validator->errors()->all();
            session()->flash("error_messages{$frame_id}", $error_messages[0]);

            // 初期表示にリダイレクトして、初期表示処理にまかせる（エラー表示）
            return collect(['redirect_path' => url($this->page->permanent_link)]);
        }

        // forms_inputs 更新
        // 本登録
        $forms_inputs->status = FormStatusType::active;
        $number = null;
        if ($form->numbering_use_flag) {
            // 採番は本登録の時のみする ※[採番プレフィックス文字列] + [ゼロ埋め採番6桁]
            $number = $form->numbering_prefix . sprintf('%06d', $this->getNo('forms', $form->bucket_id, $form->numbering_prefix));
            $forms_inputs->number_with_prefix = $number;
        }
        $forms_inputs->save();

        // フォームのカラムデータ
        $forms_columns = FormsColumns::where('forms_id', $form->id)->orderBy('display_sequence')->get();

        // フォームの登録データ
        $forms_input_cols = FormsInputCols::where('forms_inputs_id', $id)
            ->get()
            // keyをforms_columns_idにした結果をセット
            ->mapWithKeys(function ($item) {
                return [$item['forms_columns_id'] => $item];
            });

        // メールの送信文字列
        $contents_text = '';

        // 登録者のメールアドレス
        $user_mailaddresses = array();

        // 添付ファイルID
        $attach_uploads_ids = [];

        foreach ($forms_columns as $forms_column) {
            if ($forms_column->column_type == FormColumnType::group) {
                continue;
            }

            $value = "";
            if (is_array($forms_input_cols[$forms_column->id])) {
                $value = implode(self::CHECKBOX_SEPARATOR, $forms_input_cols[$forms_column->id]->value);
            } else {
                $value = $forms_input_cols[$forms_column->id]->value;
            }

            // ファイルタイプがファイル系 で 値がある事
            if (FormsColumns::isFileColumnType($forms_column->column_type) && $value) {
                $attach_uploads_ids[] = $value;

                // データ登録フラグOFFでも、一時的にファイルは持っているため、検索可能
                $upload = Uploads::where('id', $value)->first();

                // メールの内容(ファイル系は、ファイル名を載せる)
                $contents_text .= $forms_column->column_name . "：" . $upload->client_original_name . "\n";
            } else {
                // メールの内容
                $contents_text .= $forms_column->column_name . "：" . $value . "\n";
            }

            // メール型
            if ($forms_column->column_type == FormColumnType::mail) {
                $user_mailaddresses[] = $value;
            }
        }
        // 最後の改行を除去
        $contents_text = trim($contents_text);

        // *** 本登録
        // 登録後メッセージ内の採番文字列を置換
        $after_message = str_replace('[[number]]', $number, $form->after_message);
        session()->flash("after_message{$frame_id}", $after_message);

        // メール送信
        if ($form->mail_send_flag || $form->user_mail_send_flag) {
            // メール件名の組み立て
            $subject = $form->mail_subject;

            // メール本文内の採番文字列を置換
            $subject = str_replace('[[number]]', $number, $subject);
            // メール件名内のサイト名文字列を置換
            $subject = str_replace('[[site_name]]', Configs::where('name', 'base_site_name')->first()->value, $subject);
            // メール件名内のフォーム名文字列を置換
            $subject = str_replace('[[form_name]]', $form->forms_name, $subject);
            // メール件名内の登録日時を置換
            $todatetime = date("Y/m/d H:i:s");
            $subject = str_replace('[[to_datetime]]', $todatetime, $subject);

            // メール本文の組み立て
            $mail_format = $form->mail_format;
            $mail_text = str_replace('[[body]]', $contents_text, $mail_format);

            // メール本文内の採番文字列を置換
            $mail_text = str_replace('[[number]]', $number, $mail_text);
            // メール本文内のサイト名文字列を置換
            $mail_text = str_replace('[[site_name]]', Configs::where('name', 'base_site_name')->first()->value, $mail_text);
            // メール本文内のフォーム名文字列を置換
            $mail_text = str_replace('[[form_name]]', $form->forms_name, $mail_text);
            // メール本文内の登録日時を置換
            $mail_text = str_replace('[[to_datetime]]', $todatetime, $mail_text);

            // メールオプション
            $mail_options = ['subject' => $subject, 'template' => 'mail.send'];
            // メールオプションに添付ファイルをセットする
            $mail_options = $this->setMailOptionsAttach($attach_uploads_ids, $form->mail_attach_flag, $mail_options);

            // メール送信（管理者側）
            if ($form->mail_send_flag) {
                $mail_addresses = explode(',', $form->mail_send_address);
                foreach ($mail_addresses as $mail_address) {
                    // メール送信はログ出力の追加に伴いTrait のメソッドに移行
                    $this->sendMail($mail_address, $mail_options, ['content' => $mail_text], $this->getPluginName());
                }
            }

            // メール送信（ユーザー側）
            if ($form->user_mail_send_flag) {
                foreach ($user_mailaddresses as $user_mailaddress) {
                    if (!empty($user_mailaddress)) {
                        // メール送信はログ出力の追加に伴いTrait のメソッドに移行
                        $this->sendMail($user_mailaddress, $mail_options, ['content' => $mail_text], $this->getPluginName());
                    }
                }
            }
        }

        // 登録後のリダイレクト表示
        return collect(['redirect_path' => url('/') . "/plugin/forms/thanks/{$page_id}/{$frame_id}#frame-{$frame_id}"]);
    }

    /**
     * フォーム選択表示関数
     *
     * @method_title 選択
     * @method_desc このフレームに表示するフォームを選択します。
     * @method_detail
     */
    public function listBuckets($request, $page_id, $frame_id, $id = null)
    {
        // Frame データ
        $plugin_frame = Frame::where('id', $frame_id)->first();

        // データ取得（1ページの表示件数指定）
        $query = Forms::
            select(
                'forms.id',
                'forms.bucket_id',
                'forms.data_save_flag',
                'forms.created_at',
                'forms.forms_name as plugin_bucket_name',
                // 本登録数
                DB::raw('count(forms_inputs.forms_id) as active_entry_count')
            )
            ->leftJoin('forms_inputs', function ($leftJoin) {
                $leftJoin->on('forms.id', '=', 'forms_inputs.forms_id')
                    ->where('forms_inputs.status', FormStatusType::active);
            })
            ->leftJoin('frames', function ($leftJoin) use ($frame_id) {
                $leftJoin->on('forms.bucket_id', '=', 'frames.bucket_id')
                    ->where('frames.id', $frame_id);
            })
            ->join('buckets', function ($leftJoin) {
                $leftJoin->on('forms.bucket_id', '=', 'buckets.id')
                    ->where('buckets.plugin_name', 'forms');
            })
            ->groupBy(
                'forms.id',
                'forms.bucket_id',
                'forms.data_save_flag',
                'forms.created_at',
                'forms.forms_name',
                'forms_inputs.forms_id'
            )
            ->orderBy('frames.bucket_id', 'desc')
            ->orderBy('forms.created_at', 'desc');

        // フレームを配置したページから親を遡ってコンテナページを取得
        $container_page = $this->choiceContainerPageByGoingBackParentPageOrFramePage($this->page, $request->attributes->get('page_tree'), $this->frame);
        if ($container_page) {
            $query->where('buckets.container_page_id', $container_page->id);
        } else {
            $query->whereNull('buckets.container_page_id');
        }

        $plugins = $query->paginate(10, ["*"], "frame_{$frame_id}_page");

        // 仮登録件数
        $forms_tmp_entry = Forms::
            select(
                'forms.id',
                DB::raw('count(forms_inputs.forms_id) as tmp_entry_count')
            )
            ->whereIn('forms.id', $plugins->pluck('id'))
            ->leftJoin('forms_inputs', function ($leftJoin) {
                $leftJoin->on('forms.id', '=', 'forms_inputs.forms_id')
                            ->where('forms_inputs.status', FormStatusType::temporary);
            })
            ->groupBy(
                'forms.id',
                'forms_inputs.forms_id'
            )
            ->get()
            // keyをidにした結果をセット
            ->mapWithKeys(function ($item) {
                return [$item['id'] => $item];
            });

        foreach ($plugins as $plugin) {
            // $plugin->idから $forms_tmp_entry を取得しているため、配列に必ずある想定
            $plugin->tmp_entry_count = $forms_tmp_entry[$plugin->id]->tmp_entry_count;
        }

        // 表示テンプレートを呼び出す。
        return $this->view('forms_list_buckets', [
            'plugin_frame' => $plugin_frame,
            'plugins' => $plugins,
        ]);
    }

    /**
     * フォーム新規作成画面
     *
     * @method_title 作成
     * @method_desc フォームを新しく作成します。
     * @method_detail フォーム名やデータ保存条件の設定などを行います。
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

        // 仮登録件数
        $tmp_entry_count = FormsInputs::where('forms_id', $form->id)
            ->where('forms_inputs.status', FormStatusType::temporary)
            ->count();

        // 本登録数
        $active_entry_count = FormsInputs::where('forms_id', $form->id)
            ->where('forms_inputs.status', FormStatusType::active)
            ->count();

        $form->tmp_entry_count = $tmp_entry_count;
        $form->active_entry_count = $active_entry_count;

        // 選択できるフレームの一覧
        $target_plugins_frames = $this->getTargetPluginsFrames();

        // 表示テンプレートを呼び出す。
        return $this->view('forms_edit_form', [
            'form_frame'  => $form_frame,
            'form'        => $form,
            'create_flag' => $create_flag,
            'target_plugins_frames' => $target_plugins_frames,
            'message'     => $message,
            'errors'      => $errors,
        ])->withInput($request->all);
    }

    /**
     *  フォーム登録処理
     */
    public function saveBuckets($request, $page_id, $frame_id, $forms_id = null)
    {
        // 入力値変換
        // ・登録制限数
        //   全角→半角変換
        $entry_limit = StringUtils::convertNumericAndMinusZenkakuToHankaku($request->entry_limit);
        if (is_numeric($entry_limit)) {
            $request->merge([
                // 小終点の入力があったら、小数点切り捨てて整数に
                "entry_limit" => floor($entry_limit),
            ]);
        }

        // デフォルトで必須
        $validator_values['forms_name'] = ['required'];
        $validator_attributes['forms_name'] = 'フォーム名';

        $validator_values['after_message'] = ['required'];
        $validator_attributes['after_message'] = '本登録後のメッセージ';

        $validator_values['entry_limit'] = ['nullable', 'numeric', 'min:0'];
        $validator_attributes['entry_limit'] = '登録制限数';

        $validator_values['mail_send_address'] = ['nullable', new CustomValiEmails()];
        $validator_attributes['mail_send_address'] = '送信するメールアドレス';

        // 「以下のアドレスにメール送信する」がONの場合、送信するメールアドレスは必須
        if ($request->mail_send_flag) {
            $validator_values['mail_send_address'] = ['required', new CustomValiEmails()];
        }

        $validator_attributes['data_save_flag'] = 'データを保存する';
        $validator_attributes['user_mail_send_flag'] = '登録者にメール送信する';
        $validator_attributes['temporary_regist_mail_format'] = '仮登録メールフォーマット';
        $validator_attributes['display_from'] = '表示開始日時';
        $validator_attributes['display_to'] = '表示終了日時';
        $validator_attributes['regist_from'] = '登録開始日時';
        $validator_attributes['regist_to'] = '登録終了日時';

        $messages = [
            'data_save_flag.accepted' => '仮登録メールを送信する場合、:attribute にチェックを付けてください。',
            'user_mail_send_flag.accepted' => '仮登録メールを送信する場合、:attribute にチェックを付けてください。',
            'temporary_regist_mail_format.regex' => '仮登録メールを送信する場合、:attribute に[[entry_url]]を含めてください。',
        ];

        // 項目のエラーチェック
        $validator = Validator::make($request->all(), $validator_values, $messages);
        $validator->setAttributeNames($validator_attributes);

        // 仮登録メールがONならvalidate
        $validator->sometimes("data_save_flag", 'accepted', function ($input) {
            // 仮登録メールがONなら、上記の データ保存 ONであること
            return $input->use_temporary_regist_mail_flag;
        });
        $validator->sometimes("user_mail_send_flag", 'accepted', function ($input) {
            // 仮登録メールがONなら、上記の 登録者にメール送信する ONであること
            return $input->use_temporary_regist_mail_flag;
        });
        $validator->sometimes("temporary_regist_mail_format", 'regex:/\[\[entry_url\]\]/', function ($input) {
            // 仮登録メールがONなら、上記の 登録者にメール送信する ONであること
            return $input->use_temporary_regist_mail_flag;
        });

        $validator->sometimes("display_from", 'before:display_to', function ($input) {
            // 表示期間のFrom Toが両方入力ありなら、上記の 表示期間のFrom が To より前であること
            return $input->display_from && $input->display_to;
        });

        $validator->sometimes("regist_from", 'before:regist_to', function ($input) {
            // 登録期間のFrom Toが両方入力ありなら、上記の 登録期間のFrom が To より前であること
            return $input->regist_from && $input->regist_to;
        });

        // エラーがあった場合は入力画面に戻る。
        $message = null;
        if ($validator->fails()) {
            if (empty($forms_id)) {
                $create_flag = true;
                return $this->createBuckets($request, $page_id, $frame_id, $forms_id, $create_flag, $message, $validator->errors());
            } else {
                // var_dump($request->use_temporary_regist_mail_flag, $validator->errors()->first("use_temporary_regist_mail_flag"));
                // var_dump($request->data_save_flag, old('data_save_flag'));
                $create_flag = false;
                return $this->editBuckets($request, $page_id, $frame_id, $forms_id, $create_flag, $message, $validator->errors());
            }
        }

        // 更新後のメッセージ
        $message = null;

        // 画面から渡ってくるforms_id が空ならバケツとフォームを新規登録
        if (empty($forms_id)) {
            // バケツの登録
            $bucket = $this->saveFormBucket($request->forms_name, $request->attributes->get('page_tree'));

            // フォームデータ新規オブジェクト
            $forms = new Forms();
            $forms->bucket_id = $bucket->id;

            // Frame のBuckets を見て、Buckets が設定されていなければ、作成したものに紐づける。
            // Frame にBuckets が設定されていない ＞ 新規のフレーム＆フォーム作成
            // Frame にBuckets が設定されている ＞ 既存のフレーム＆フォーム更新
            // （表示フォーム選択から遷移してきて、内容だけ更新して、フレームに紐づけないケースもあるため）
            $frame = Frame::where('id', $frame_id)->first();
            if (empty($frame->bucket_id)) {
                // FrameのバケツIDの更新
                $frame = Frame::where('id', $frame_id)->update(['bucket_id' => $bucket->id]);
            }

            // bugfix: フォーム作成後のメッセージを実際の動きと合わせたものに見直し
            // $message = 'フォーム設定を追加しました。<br />　 フォームで使用する項目を設定してください。［ <a href="' . url('/') . '/plugin/forms/editColumn/' . $page_id . '/' . $frame_id . '/">項目設定</a> ］';
            $message = 'フォーム設定を追加しました。<br />' .
                        '　 [ <a href="' . url('/') . '/plugin/forms/listBuckets/' . $page_id . '/' . $frame_id . '/#frame-' . $frame_id . '">フォーム選択</a> ]から作成したフォームを選択後、［ 項目設定 ］で使用する項目を設定してください。';
        } else {
            // forms_id があれば、フォームを更新

            // フォームデータ取得
            $forms = Forms::where('id', $forms_id)->first();

            // バケツ名を入力されたフォーム名で更新
            $bucket = $this->saveFormBucket($request->forms_name, $request->attributes->get('page_tree'), $forms->bucket_id);

            $message = 'フォーム設定を変更しました。';
        }

        // フォーム設定
        $forms->forms_name          = $request->forms_name;
        $forms->form_mode           = $request->form_mode;
        $forms->access_limit_type   = $request->access_limit_type;
        $forms->form_password       = $request->form_password;
        $forms->entry_limit         = $request->entry_limit;
        $forms->entry_limit_over_message = $request->entry_limit_over_message;
        $forms->display_control_flag = empty($request->display_control_flag) ? 0 : $request->display_control_flag;
        $forms->display_from        = empty($request->display_from) ? null : new Carbon($request->display_from);
        $forms->display_to          = empty($request->display_to) ? null : new Carbon($request->display_to);
        $forms->regist_control_flag = empty($request->regist_control_flag) ? 0 : $request->regist_control_flag;
        $forms->regist_from         = empty($request->regist_from) ? null : new Carbon($request->regist_from);
        $forms->regist_to           = empty($request->regist_to) ? null : new Carbon($request->regist_to);
        $forms->can_view_inputs_moderator = empty($request->can_view_inputs_moderator) ? 0 : $request->can_view_inputs_moderator;
        $forms->mail_send_flag      = empty($request->mail_send_flag) ? 0 : $request->mail_send_flag;
        $forms->mail_send_address   = $request->mail_send_address;
        $forms->user_mail_send_flag = empty($request->user_mail_send_flag) ? 0 : $request->user_mail_send_flag;
        $forms->mail_attach_flag    = empty($request->mail_attach_flag) ? 0 : $request->mail_attach_flag;
        $forms->use_temporary_regist_mail_flag = (empty($request->use_temporary_regist_mail_flag)) ? 0 : $request->use_temporary_regist_mail_flag;
        $forms->temporary_regist_mail_subject = $request->temporary_regist_mail_subject;
        $forms->temporary_regist_mail_format = $request->temporary_regist_mail_format;
        $forms->temporary_regist_after_message = $request->temporary_regist_after_message;
        $forms->from_mail_name      = $request->from_mail_name;
        $forms->mail_subject        = $request->mail_subject;
        $forms->mail_format         = $request->mail_format;
        $forms->data_save_flag      = empty($request->data_save_flag) ? 0 : $request->data_save_flag;
        $forms->after_message       = $this->clean($request->after_message);
        $forms->numbering_use_flag  = empty($request->numbering_use_flag) ? 0 : $request->numbering_use_flag;
        $forms->numbering_prefix    = $request->numbering_prefix;
        $forms->other_plugins_register_use_flag  = empty($request->other_plugins_register_use_flag) ? 0 : $request->other_plugins_register_use_flag;
        $forms->target_frame_ids = empty($request->target_frame_ids) ? "": implode(',', $request->target_frame_ids);

        // データ保存
        $forms->save();

        // 新規作成フラグを付けてフォーム設定変更画面を呼ぶ
        $create_flag = false;

        // bugfix: 登録後は登録後の$forms->idを渡す。
        // return $this->editBuckets($request, $page_id, $frame_id, $forms_id, $create_flag, $message);
        return $this->editBuckets($request, $page_id, $frame_id, $forms->id, $create_flag, $message);
    }

    /**
     * フォーム削除処理
     */
    public function destroyBuckets($request, $page_id, $frame_id, $forms_id)
    {
        // forms_id がある場合、データを削除
        if ($forms_id) {
            $forms_columns = FormsColumns::where('forms_id', $forms_id)->orderBy('display_sequence')->get();

            ////
            //// 添付ファイルの削除
            ////
            $file_column_type_ids = [];
            foreach ($forms_columns as $forms_column) {
                // ファイルタイプ
                if (FormsColumns::isFileColumnType($forms_column->column_type)) {
                    $file_column_type_ids[] = $forms_column->id;
                }
            }

            // 削除するファイル情報が入っている詳細データの特定
            $del_file_ids = FormsInputCols::whereIn('forms_columns_id', $file_column_type_ids)
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


            foreach ($forms_columns as $forms_column) {
                // 詳細データ値を削除する。
                FormsInputCols::where('forms_columns_id', $forms_column->id)->delete();

                // カラムに紐づく選択肢の削除
                $this->deleteColumnsSelects($forms_column->id);
            }

            // 入力行データを削除する。
            FormsInputs::where('forms_id', $forms_id)->delete();

            // カラムデータを削除する。
            FormsColumns::where('forms_id', $forms_id)->delete();

            // bugfix: backetsは $frame->bucket_id で消さない。選択したフォームのbucket_idで消す
            $forms = Forms::find($forms_id);

            // backetsの削除
            Buckets::where('id', $forms->bucket_id)->delete();

            // change: このバケツを表示している全ページのフレームのバケツIDを消す
            // // バケツIDの取得のためにFrame を取得(Frame を更新する前に取得しておく)
            // $frame = Frame::where('id', $frame_id)->first();
            // // bugfix: フレームのbucket_idと削除するフォームのbucket_idが同じなら、FrameのバケツIDの更新する
            // if ($frame->bucket_id == $forms->bucket_id) {
            //     // FrameのバケツIDの更新
            //     Frame::where('bucket_id', $frame->bucket_id)->update(['bucket_id' => null]);
            // }
            // FrameのバケツIDの更新. このバケツを表示している全ページのフレームのバケツIDを消す
            Frame::where('bucket_id', $forms->bucket_id)->update(['bucket_id' => null]);

            // フォーム設定を削除する。
            Forms::destroy($forms_id);
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

        // 表示フォーム選択画面を呼ぶ
        return $this->listBuckets($request, $page_id, $frame_id, $id);
    }

    /**
     * 項目の追加
     */
    public function addColumn($request, $page_id, $frame_id, $id = null)
    {
        // エラーチェック
        $validator = Validator::make($request->all(), [
            'column_name'  => ['required', new CustomValiWysiwygMax()],
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
        $column->required = $request->required ? Required::on : Required::off;
        $column->display_sequence = $max_display_sequence;
        $column->caption_color = Bs4TextColor::dark;
        $column->save();
        $message = '項目【 '. $request->column_name .' 】を追加しました。';

        // 編集画面へ戻る。
        return $this->editColumn($request, $page_id, $frame_id, $request->forms_id, $message, $errors);
    }

    /**
     * 項目の詳細画面の表示
     *
     * @method_title カラム詳細編集
     * @method_desc カラムの詳細設定を行います。
     * @method_detail 入力チェック、キャプションやプレースホルダなどを設定できます。
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
        $form_mode = null;
        if (!empty($form_db)) {
            $forms_id  = $form_db->id;
            $form_mode = $form_db->form_mode;
        }

        // --- 画面に値を渡す準備
        $column = FormsColumns::query()->where('id', $column_id)->first();
        $selects = FormsColumnsSelects::query()->where('forms_columns_id', $column->id)->orderBy('display_sequence', 'asc')->get();

        // 編集画面テンプレートを呼び出す。
        return $this->view('forms_edit_row_detail', [
            'forms_id'  => $forms_id,
            'form_mode' => $form_mode,
            'column'    => $column,
            'selects'   => $selects,
            'message'   => $message,
            'errors'    => $errors,
        ]);
    }

    /**
     * カラム編集画面の表示
     *
     * @method_title カラム編集
     * @method_desc カラムの設定を行います。
     * @method_detail カラム名と型を指定してカラムを作成します。
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
        $use_temporary_regist_mail_flag = null;
        $form_mode = FormMode::form;
        if (!empty($form_db)) {
            $forms_id = $form_db->id;
            $use_temporary_regist_mail_flag = $form_db->use_temporary_regist_mail_flag;
            $form_mode = $form_db->form_mode;
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
                'forms_columns.place_holder',
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
                'forms_columns.place_holder',
                'forms_columns.display_sequence'
            )
            ->orderby('forms_columns.display_sequence')
            ->get();

        // 仮登録設定時のワーニングメッセージ
        $warning_message = null;
        if ($use_temporary_regist_mail_flag) {
            $is_exist = false;
            foreach ($columns as $column) {
                if ($column->required && $column->column_type == FormColumnType::mail) {
                    $is_exist = true;
                    break;
                }
            }
            if (! $is_exist) {
                $warning_message = "仮登録メールが設定されています。必須のメールアドレス型の項目を設定してください。";
            }
        }

        // 編集画面テンプレートを呼び出す。
        return $this->view('forms_edit', [
            'forms_id'        => $forms_id,
            'form_mode'       => $form_mode,
            'columns'         => $columns,
            'message'         => $message,
            'warning_message' => $warning_message,
            'errors'          => $errors,
        ]);
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
        $str_column_name = "column_name_".$request->column_id;
        $str_column_type = "column_type_".$request->column_id;
        $str_required = "required_".$request->column_id;

        $validate_value = [
            'column_name_'.$request->column_id => ['required'],
            'column_type_'.$request->column_id => ['required'],
        ];

        $validate_attribute = [
            'column_name_'.$request->column_id  => '項目名',
            'column_type_'.$request->column_id  => '型',
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
        $column->column_name = $request->$str_column_name;
        $column->column_type = $request->$str_column_type;
        $column->required = $request->$str_required ? Required::on : Required::off;
        $column->save();
        $message = '項目【 '. $request->$str_column_name .' 】を更新しました。';

        // 編集画面を呼び出す
        return $this->editColumn($request, $page_id, $frame_id, $request->forms_id, $message, $errors);
    }

    /**
     * 項目のコピー
     */
    public function copyColumn($request, $page_id, $frame_id)
    {
        // コピー対象の取得
        $src_column = FormsColumns::query()->where('id', $request->column_id)->first();
        $src_selects = FormsColumnsSelects::query()->where('forms_columns_id', $request->column_id)->get();

        /**
         * 項目のコピー処理
         */
        // 新規登録時の表示順を設定
        $max_display_sequence = FormsColumns::query()->where('forms_id', $request->forms_id)->max('display_sequence');
        $max_display_sequence = $max_display_sequence ? $max_display_sequence + 1 : 1;

        // 項目（親）コピー
        $dist_column = $src_column->replicate();
        $dist_column->column_name = $dist_column->column_name . '_copy';
        $dist_column->display_sequence = $max_display_sequence;
        $dist_column->save();

        // 項目（子）コピー
        foreach ($src_selects as $src_select) {
            $dist_select = $src_select->replicate();
            $dist_select->forms_columns_id = $dist_column->id;
            $dist_select->save();
        }

        // メッセージ設定
        $message = '項目【 '. $src_column->column_name .' 】をコピーしました。';

        // 編集画面を呼び出す
        return $this->editColumn($request, $page_id, $frame_id, $request->forms_id, $message);
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
     * つまんで移動した項目の表示順を更新
     */
    public function updateColumnSequenceAll($request, $page_id, $frame_id)
    {
        DB::beginTransaction();
        try {
            // まとめて取得
            $columns = FormsColumns::whereIn('id', $request->column_ids_order)->get();

            foreach ($request->column_ids_order as $key => $column_id) {
                $column = $columns->firstWhere('id', $column_id);
                if ($column) {
                    // display_sequenceを1から順に全項目を振り直し
                    $column->display_sequence = $key + 1;
                    $column->save();
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        $message = '項目の表示順を更新しました。';

        // 編集画面を呼び出す
        return $this->editColumn($request, $page_id, $frame_id, $request->forms_id, $message, null);
    }

    /**
     * 項目に紐づく詳細情報の更新
     */
    public function updateColumnDetail($request, $page_id, $frame_id)
    {
        // フォームデータ取得
        $form = Forms::where('id', $request->forms_id)->first();
        if (empty($form)) {
            // バケツ空テンプレートを呼び出す。
            return $this->commonView('empty_bucket_setting');
        }

        $column = FormsColumns::query()->where('id', $request->column_id)->first();

        $validator_values = null;
        $validator_attributes = null;

        // データ型が「まとめ行」の場合はまとめ数について必須チェック
        if ($column->column_type == FormColumnType::group) {
            $validator_values['frame_col'] = ['required'];
            $validator_attributes['frame_col'] = 'まとめ数';
        }
        // 桁数チェックの指定時、入力値が数値であるかチェック
        if ($request->rule_digits_or_less) {
            $validator_values['rule_digits_or_less'] = ['numeric'];
            $validator_attributes['rule_digits_or_less'] = '入力桁数';
        }
        // 最大値の指定時、入力値が数値であるかチェック
        if ($request->rule_max) {
            $validator_values['rule_max'] = ['numeric'];
            $validator_attributes['rule_max'] = '最大値';
        }
        // 最小値の指定時、入力値が数値であるかチェック
        if ($request->rule_min) {
            $validator_values['rule_min'] = ['numeric'];
            $validator_attributes['rule_min'] = '最小値';
        }
        // 入力文字数の指定時、入力値が数値であるかチェック
        if ($request->rule_word_count) {
            $validator_values['rule_word_count'] = ['numeric'];
            $validator_attributes['rule_word_count'] = '入力文字数';
        }
        // ～日以降許容を指定時、入力値が数値であるかチェック
        if ($request->rule_date_after_equal) {
            $validator_values['rule_date_after_equal'] = ['numeric'];
            $validator_attributes['rule_date_after_equal'] = '～日以降を許容';
        }
        // アンケートの場合、項目名のwysiwygチェック
        if ($form->form_mode == FormMode::questionnaire) {
            $validator_values['column_name'] = ['required', new CustomValiWysiwygMax()];
            $validator_attributes['column_name'] = '項目名';
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
        $column->place_holder = $request->place_holder;
        $column->frame_col = $request->frame_col;
        // 分刻み指定
        if ($column->column_type == FormColumnType::time) {
            $column->minutes_increments = $request->minutes_increments;
        }
        // 分刻み指定（FromTo）
        if ($column->column_type == FormColumnType::time_from_to) {
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
        // 正規表現
        $column->rule_regex = $request->rule_regex;
        // ～日以降を許容
        $column->rule_date_after_equal = $request->rule_date_after_equal;
        // アンケートの場合、項目名の更新
        if ($form->form_mode == FormMode::questionnaire) {
            $column->column_name = $request->column_name;
        }

        $column->save();
        if ($form->form_mode == FormMode::form) {
            // フォーム
            $message = '項目【 '. $column->column_name .' 】を更新しました。';
        } else {
            // アンケート
            $message = '項目を更新しました。';
        }

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
     * つまんで移動した選択肢の表示順を更新
     */
    public function updateSelectSequenceAll($request, $page_id, $frame_id)
    {
        DB::beginTransaction();
        try {
            // まとめて取得
            $selects = FormsColumnsSelects::whereIn('id', $request->select_ids_order)->get();

            foreach ($request->select_ids_order as $key => $select_id) {
                $select = $selects->firstWhere('id', $select_id);
                if ($select) {
                    // display_sequenceを1から順に全選択肢を振り直し
                    $select->display_sequence = $key + 1;
                    $select->save();
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        $message = '選択肢の表示順を更新しました。';

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
     * 集計結果ダウンロード
     */
    public function downloadCsvAggregate($request, $page_id, $frame_id, $id)
    {
        return $this->downloadCsv($request, $page_id, $frame_id, $id);
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

        // debug:確認したいSQLの前にこれを仕込んで
        // \DB::enableQueryLog();

        // 登録データの取得
        // change: 基本ないが、forms_input_colsにデータが無い場合にも対応(登録一覧の表示は、forms_input_colsがない場合にも対応していた)
        // $input_cols = FormsInputCols::
        //                             select(
        //                                 'forms_input_cols.*',
        //                                 'forms_inputs.status as inputs_status'
        //                             )
        //                             ->leftjoin('forms_inputs', 'forms_inputs.id', '=', 'forms_input_cols.forms_inputs_id')
        //                             ->whereIn('forms_inputs_id', FormsInputs::select('id')->where('forms_id', $id)->where('status', '!=', FormStatusType::delete))
        //                             ->orderBy('forms_inputs_id', 'asc')->orderBy('forms_columns_id', 'asc')
        //                             ->get();
        $input_cols = FormsInputs::
                select(
                    'forms_inputs.id as inputs_id',
                    'forms_inputs.status as inputs_status',
                    'forms_inputs.number_with_prefix as number_with_prefix',
                    'forms_inputs.created_at as inputs_created_at',
                    'forms_inputs.created_name as inputs_created_name',
                    'forms_input_cols.*'
                )
                ->leftjoin('forms_input_cols', 'forms_inputs.id', '=', 'forms_input_cols.forms_inputs_id')
                ->where('forms_inputs.forms_id', $id)
                // 削除データは出力しない
                ->where('forms_inputs.status', '!=', FormStatusType::delete)
                ->orderBy('forms_inputs.id', 'asc')
                ->orderBy('forms_input_cols.forms_columns_id', 'asc')
                ->get();

        // debug: sql dumpする
        // \Log::debug(var_export(\DB::getQueryLog(), true));

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

        // 見出し行-頭（固定項目）
        $csv_array[0]['status'] = '状態(0:本登録,1:仮登録)';
        $copy_base['status'] = '';
        // 見出し行
        foreach ($columns as $column) {
            $csv_array[0][$column->id] = strip_tags($column->column_name);
            $copy_base[$column->id] = '';
        }
        if ($form->numbering_use_flag) {
            // 見出し行-行末１つ手前（採番項目）
            $csv_array[0]['number_with_prefix'] = '採番';
            $copy_base['number_with_prefix'] = '';
        }
        // 見出し行-行末（固定項目）
        $csv_array[0]['created_name'] = '登録ユーザ';
        $copy_base['created_name'] = '';
        $csv_array[0]['created_at'] = '登録日時';
        $copy_base['created_at'] = '';

        // データ
        foreach ($input_cols as $input_col) {
            if (!array_key_exists($input_col->inputs_id, $csv_array)) {
                // 初回のみベースをセット
                $csv_array[$input_col->inputs_id] = $copy_base;

                // 初回で固定項目をセット
                $csv_array[$input_col->inputs_id]['status'] = $input_col->inputs_status;
                if ($form->numbering_use_flag) {
                    // 採番項目
                    $csv_array[$input_col->inputs_id]['number_with_prefix'] = $input_col->number_with_prefix;
                }
                $csv_array[$input_col->inputs_id]['created_name'] = $input_col->inputs_created_name;
                $csv_array[$input_col->inputs_id]['created_at'] = $input_col->inputs_created_at;
            }
            $csv_array[$input_col->inputs_id][$input_col->forms_columns_id] = $input_col->value;
        }

        // レスポンス版
        $filename = $form->forms_name . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        // データ
        $csv_data = CsvUtils::getResponseCsvData($csv_array, $request->character_code);

        return response()->make($csv_data, 200, $headers);
    }

    /**
     * 登録一覧
     *
     * @method_title 登録一覧
     * @method_desc 入力されたデータを一覧表示できます。
     * @method_detail 入力されたデータのダウンロードもできます。
     */
    public function listInputs($request, $page_id, $frame_id, $forms_id = null)
    {
        // フォーム＆フレームデータ
        $form_frame = $this->getFormFrame($frame_id);

        $form = null;
        if (!empty($forms_id)) {
            // forms_id が渡ってくればforms_id が対象
            $form = Forms::where('id', $forms_id)->first();
        } elseif (!empty($form_frame->bucket_id)) {
            // Frame のbucket_id があれば、bucket_id からフォームデータ取得
            $form = Forms::where('bucket_id', $form_frame->bucket_id)->first();
        }

        if (empty($form)) {
            // ワーニング画面へ
            return $this->view('forms_edit_warning_messages', [
                'warning_messages' => ["フォーム選択から選択するか、フォーム作成で作成してください。"],
            ]);
        }

        // カラムの取得
        $columns = FormsColumns::where('forms_id', $form->id)->orderBy('display_sequence', 'asc')->get();

        $inputs_query = FormsInputs::where('forms_id', $form->id);
        // $inputs_query->orderBy('forms_inputs.id', 'asc');
        $inputs_query->orderBy('forms_inputs.created_at', 'desc');

        // データ取得
        $get_count = 10;
        $inputs = $inputs_query->paginate($get_count, ["*"], "frame_{$frame_id}_page");

        // 登録データ詳細の取得
        $input_cols = FormsInputCols::select('forms_input_cols.*', 'uploads.client_original_name')
                                        ->leftJoin('uploads', 'uploads.id', '=', 'forms_input_cols.value')
                                        ->whereIn('forms_inputs_id', $inputs->pluck('id'))
                                        ->orderBy('forms_inputs_id', 'asc')->orderBy('forms_columns_id', 'asc')
                                        ->get();

        // bucktsで開いていたページの保持
        // $frame_page = "frame_{$frame_id}_buckets_page";

        // 表示テンプレートを呼び出す。
        return $this->view('forms_list_inputs', [
            'form' => $form,
            'columns' => $columns,
            'inputs' => $inputs,
            'input_cols' => $input_cols,
        ]);
    }

    /**
     * 登録一覧からの編集画面表示
     */
    public function editInput($request, $page_id, $frame_id, $inputs_id)
    {
        $input = FormsInputs::find($inputs_id);
        if (empty($input)) {
            // ワーニング画面へ
            return $this->view('forms_edit_warning_messages', [
                'warning_messages' => ["登録データがありません。"],
            ]);
        }

        // 登録データ詳細の取得
        $input_cols = FormsInputCols::select('forms_input_cols.*', 'uploads.client_original_name')
                                        ->leftJoin('uploads', 'uploads.id', '=', 'forms_input_cols.value')
                                        ->where('forms_inputs_id', $inputs_id)
                                        ->orderBy('forms_inputs_id', 'asc')->orderBy('forms_columns_id', 'asc')
                                        ->get();

        // Formsデータ
        $form = Forms::find($input->forms_id);

        // フォームのカラムデータ
        $forms_columns = $this->getFormsColumns($form);

        // 表示テンプレートを呼び出す。
        return $this->view('forms_edit_input', [
            'input' => $input,
            'input_cols' => $input_cols,
            'frame_id' => $frame_id,
            'form' => $form,
            'forms_columns' => $forms_columns,
        ]);
    }

    /**
     * 登録一覧＞編集からのデータ登録
     */
    public function storeInput($request, $page_id, $frame_id, $inputs_id)
    {
        $input = FormsInputs::find($inputs_id);
        if (empty($input)) {
            // ワーニング画面へ
            return $this->view('forms_edit_warning_messages', [
                'warning_messages' => ["登録データがありません。"],
            ]);
        }

        // forms_inputs 更新
        $input->status = $request->status;
        $input->save();

        $request->flash_message = '変更しました。';

        // redirect_path指定して自動遷移するため、returnで表示viewの指定不要。
    }

    /**
     * フォームのコピー機能
     */
    public function copyForm($request, $page_id, $frame_id, $form_id)
    {
        // formsのコピー
        $form = Forms::find($form_id);

        // パラメータ不正
        if (!isset($form)) {
            Log::debug('forms_id is not found.');
            return;
        }

        $copy_form = $form->replicate();
        $forms_name = $form->forms_name . '_copy';
        // 桁あふれでエラーにならないようにするため上限で切り捨て
        if (strlen($forms_name) > self::FORM_NAME_SIZE) {
            $forms_name = mb_strcut($forms_name, 0, self::FORM_NAME_SIZE);
        }

        // バケツの登録
        $bucket = $this->saveFormBucket($forms_name, $request->attributes->get('page_tree'));

        $copy_form->forms_name = $forms_name;
        $copy_form->bucket_id = $bucket->id;
        $copy_form->save();

        // forms_columnsのコピー
        $form_columns = FormsColumns::where('forms_id', $form->id)->get();
        foreach ($form_columns as $form_column) {
            $copy_form_column = $form_column->replicate();
            $copy_form_column->forms_id = $copy_form->id;
            $copy_form_column->save();

            // forms_columns_selectsのコピー
            $form_column_selects = FormsColumnsSelects::where('forms_columns_id', $form_column->id)->get();
            foreach ($form_column_selects as $form_column_select) {
                $copy_form_column_select = $form_column_select->replicate();
                $copy_form_column_select->forms_columns_id = $copy_form_column->id;
                $copy_form_column_select->save();
            }
        }
    }

    /**
     * 他プラグイン連携機能
     */
    public function registerOtherPlugins($request, $page_id, $frame_id, $form_inputs_id)
    {
        // 有効のステータスのみ連携可能
        $form_input = FormsInputs::where('id', $form_inputs_id)->where('status', FormStatusType::active)->first();

        // パラメータ不正
        if (!isset($form_input)) {
            Log::debug('form_inputs_id is not found or not active.');
            return;
        }

        $form = Forms::find($form_input->forms_id);
        // 他プラグイン連携未設定はどうしようもない
        if ($form->other_plugins_register_use_flag == 0 || empty($form->target_frame_ids)) {
            Log::debug($form->target_frame_ids);
            return;
        }

        // 登録処理
        foreach ($this->fetchTargetFrames(explode(',', $form->target_frame_ids)) as $frame) {
            // バケツ未指定は連携できない
            if (!$frame->bucket_id) {
                Log::debug('bucket_id is null.');
                continue;
            }

            switch ($frame->plugin_name) {
                case PluginName::getPluginName(PluginName::bbses):
                    $this->toBbs($frame->bucket_id, $this->fetchFormInputValues($form_inputs_id), $form);
                    break;
                case PluginName::getPluginName(PluginName::blogs):
                    $this->toBlog($frame->bucket_id, $this->fetchFormInputValues($form_inputs_id), $form);
                    break;
                case PluginName::getPluginName(PluginName::faqs):
                    $this->toFaq($frame->bucket_id, $this->fetchFormInputValues($form_inputs_id), $form);
                    break;
                default:
                    break;
            }
        }

        $request->flash_message = '登録内容を他プラグインへ連携しました。連携した情報は連携先で一時保存状態になっています。登録内容を確認して公開してください。';
    }

    /**
     * 他プラグイン連携対象のフレームを取得する
     *
     * @param array $target_frame_ids
     * @return Collection
     */
    private function fetchTargetFrames(array $target_frame_ids): Collection
    {
        return Frame::select('plugin_name', 'bucket_id')
            ->whereIn('id', $target_frame_ids)
            ->whereIn('plugin_name', FormsRegisterTargetPlugin::getKeysPluginsCanSpecifiedFrames())
            ->groupBy('plugin_name', 'bucket_id')
            ->get();
    }

    /**
     * 本文用にフォーム登録データを取得する
     *
     * @param array $target_frame_ids
     * @return Collection
     */
    private function fetchFormInputValues(int $form_inputs_id): Collection
    {
        $input_values = FormsInputs::select('forms_columns.column_name', 'forms_input_cols.value')
            ->leftJoin('forms_input_cols', 'forms_inputs.id', '=', 'forms_input_cols.forms_inputs_id')
            ->leftJoin('forms_columns', 'forms_input_cols.forms_columns_id', '=', 'forms_columns.id')
            ->where('forms_inputs.id', $form_inputs_id)
            ->orderBy('display_sequence')
            ->get();


        return $input_values;
    }

    /**
     * フォーム登録データを掲示板に登録する
     *
     * @param int $bucket_id 登録先のバケツID
     * @param Collection $input_values フォーム登録データ
     * @param Forms $form
     */
    private function toBbs(int $bucket_id, Collection $input_values, Forms $form)
    {
        $bbs_id = Bbs::where('bucket_id', $bucket_id)->first()->id;

        $post = BbsPost::create([
            'bbs_id' => $bbs_id,
            'title' => $this->getTitle($form->forms_name),
            'body' => $this->getBody($input_values),
            'status' => StatusType::temporary,
            'thread_updated_at' => date('Y-m-d H:i:s'),
            'created_id' => Auth::user()->id,
        ]);
        $post->thread_root_id = $post->id;
        $post->save();
    }

    /**
     * フォーム登録データをブログに登録する
     *
     * @param int $bucket_id 登録先のバケツID
     * @param Collection $input_values フォーム登録データ
     * @param Forms $form
     */
    private function toBlog(int $bucket_id, Collection $input_values, Forms $form)
    {
        $blogs_id = Blogs::where('bucket_id', $bucket_id)->first()->id;

        $post = BlogsPosts::create([
            'blogs_id' => $blogs_id,
            'post_title' => $this->getTitle($form->forms_name),
            'posted_at' => date('Y-m-d H:i:s'),
            'post_text' => $this->getBody($input_values),
            'read_more_flag' => 0,
            'status' => StatusType::temporary,
            'created_id' => Auth::user()->id,
        ]);

        $post->contents_id = $post->id;
        $post->save();
    }

    /**
     * フォーム登録データをFAQに登録する
     *
     * @param int $bucket_id 登録先のバケツID
     * @param Collection $input_values フォーム登録データ
     * @param Forms $form
     */
    private function toFaq(int $bucket_id, Collection $input_values, Forms $form)
    {
        $faqs_id = Faqs::where('bucket_id', $bucket_id)->first()->id;

        $max_display_sequence = FaqsPosts::where('faqs_id', $faqs_id)->max('display_sequence');
        $display_sequence = empty($max_display_sequence) ? 1 : $max_display_sequence + 1;

        $post = FaqsPosts::create([
            'faqs_id' => $faqs_id,
            'post_title' => $this->getTitle($form->forms_name),
            'posted_at' => date('Y-m-d H:i:s'),
            'post_text' => $this->getBody($input_values),
            'display_sequence' => $display_sequence,
            'status' => StatusType::temporary,
            'created_id' => Auth::user()->id,
        ]);

        $post->contents_id = $post->id;
        $post->save();
    }

    /**
     * 他プラグイン連携時の件名を取得する
     *
     * @param string $form_name
     * @return string
     */
    private function getTitle(string $form_name): string
    {
        return "フォーム「{$form_name}」から連携";
    }

    /**
     * 他プラグイン連携時の本文を取得する
     *
     * @param Collection $input_values フォーム登録データ
     * @return string
     */
    private function getBody(Collection $input_values)
    {
        $body = '';

        foreach ($input_values as $value) {
            $body .= '<h1>';
            $body .= htmlspecialchars($value->column_name);
            $body .= '</h1>';
            $body .= '<p>';
            $body .= htmlspecialchars($value->value);
            $body .= '</p>';
        }

        return $body;
    }

    /**
     * 他プラグイン連携対象のプラグインがあるフレームデータの取得
     */
    private function getTargetPluginsFrames()
    {
        // Frame データ
        $frames = Frame::select('frames.*', 'pages._lft', 'pages.page_name', 'buckets.bucket_name')
            // ->whereIn('frames.plugin_name', array('blogs'))
            ->whereIn('frames.plugin_name', FormsRegisterTargetPlugin::getKeysPluginsCanSpecifiedFrames())
            ->leftJoin('buckets', 'frames.bucket_id', '=', 'buckets.id')
            ->leftJoin('pages', 'frames.page_id', '=', 'pages.id')
            // ->where('disable_searchs', 0)
            ->orderBy('pages._lft', 'asc')
            ->get();

        return $frames;
    }

    /**
     * フォームのバケツを登録する
     *
     * @param string $form_name フォーム名
     * @param Collection $page_tree フォーム名
     * @param int $bucket_id バケツID
     * @return Buckets フォームのバケツ
     */
    private function saveFormBucket($form_name, ?Collection $page_tree, $bucket_id = null): Buckets
    {
        $bucket = Buckets::findOrNew($bucket_id);
        $bucket->bucket_name = $form_name;
        $bucket->plugin_name = PluginName::getPluginName(PluginName::forms);

        // フレームを配置したページから親を遡ってコンテナページを取得
        $container_page = $this->choiceContainerPageByGoingBackParentPageOrFramePage($this->page, $page_tree, $this->frame);
        if ($container_page) {
            $bucket->container_page_id = $container_page->id;
        } else {
            $bucket->container_page_id = null;
        }

        $bucket->save();
        return $bucket;
    }

    /**
     * ダウンロードできるか（ファイルダウンロード処理から呼ばれる）
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @param Uploads $upload アップロードファイル
     * @return array
     * @see \App\Http\Controllers\Core\UploadController callCheckMethod()
     */
    public static function canDownload($request, Uploads $upload): array
    {
        // コンテナはページ毎に表示バケツを絞る機能。
        // アップロードファイルは、バケツより上位のページ単位($upload->page_id)でファイルにアクセスできるかを UploadController でチェックしているため、コンテナかどうか判定しなくてOK。

        // 権限チェック
        if (Auth::check() && Auth::user()->can('role_article')) {
            // 権限あり
            return [true, 'OK'];
        } else {
            return [false, '対象ファイルに対する権限なし'];
        }
    }
}
