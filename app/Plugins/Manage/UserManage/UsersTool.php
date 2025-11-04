<?php

namespace App\Plugins\Manage\UserManage;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

use App\User;
use App\Models\Core\Configs;
use App\Models\Core\UsersColumns;
use App\Models\Core\UsersColumnsSelects;
use App\Models\Core\UsersInputCols;

use App\Rules\CustomValiAlphaNumForMultiByte;
use App\Rules\CustomValiCheckWidthForString;
use App\Rules\CustomValiUserEmailUnique;

use App\Enums\ConditionalOperator;
use App\Enums\Required;
use App\Enums\ShowType;
use App\Enums\UserColumnType;
use App\Enums\UserRegisterNoticeEmbeddedTag;

/**
 * ユーザーの便利関数
 */
class UsersTool
{
    const CHECKBOX_SEPARATOR = '|';

    /** columns_set_id 初期値 1:基本 */
    const COLUMNS_SET_ID_DEFAULT = 1;

    /**
     * ユーザーのカラム取得
     */
    public static function getUsersColumns(?int $columns_set_id)
    {
        // ユーザーのカラム
        return UsersColumns::where('columns_set_id', $columns_set_id)->orderBy('display_sequence')->get();
    }

    /**
     * 自動ユーザ登録のユーザーのカラム取得
     */
    public static function getUsersColumnsRegister(int $columns_set_id)
    {
        // ユーザーのカラム
        return UsersColumns::where('columns_set_id', $columns_set_id)->where('is_show_auto_regist', ShowType::show)->orderBy('display_sequence')->get();
    }

    /**
     * カラムの選択肢 取得
     */
    public static function getUsersColumnsSelects(?int $columns_set_id)
    {
        // カラムの選択肢
        $users_columns_selects = UsersColumnsSelects::select('users_columns_selects.*')
            ->join('users_columns', 'users_columns.id', '=', 'users_columns_selects.users_columns_id')
            ->where('users_columns_selects.columns_set_id', $columns_set_id)
            ->orderBy('users_columns_selects.users_columns_id', 'asc')
            ->orderBy('users_columns_selects.display_sequence', 'asc')
            ->get();

        // カラムID毎に詰めなおし
        $users_columns_id_select = array();
        $index = 1;
        $before_users_columns_id = null;
        foreach ($users_columns_selects as $users_columns_select) {
            if ($before_users_columns_id != $users_columns_select->users_columns_id) {
                $index = 1;
                $before_users_columns_id = $users_columns_select->users_columns_id;
            }

            $users_columns_id_select[$users_columns_select->users_columns_id][$index]['value'] = $users_columns_select->value;
            $users_columns_id_select[$users_columns_select->users_columns_id][$index]['agree_description'] = $users_columns_select->agree_description;
            $index++;
        }

        return $users_columns_id_select;
    }

    /**
     * カラムの登録データの取得
     */
    public static function getUsersInputCols($users_ids)
    {
        // カラムの登録データ
        $input_cols = UsersInputCols::
            select(
                'users_input_cols.*',
                'users_columns.column_type',
                'users_columns.column_name',
                'users_columns.use_variable',
                'users_columns.variable_name',
                'uploads.client_original_name'
            )
            ->join('users_columns', 'users_columns.id', '=', 'users_input_cols.users_columns_id')
            ->leftJoin('uploads', 'uploads.id', '=', 'users_input_cols.value')
            ->whereIn('users_id', $users_ids)
            ->orderBy('users_id', 'asc')
            ->orderBy('users_columns_id', 'asc')
            ->get();
        return $input_cols;
    }

    /**
     * カラムの値 取得
     */
    public static function getUsersInputColValue(UsersInputCols $input_col)
    {
        $class_name = self::getOptionClass();
        // オプションクラス有＋メソッド有なら呼ぶ
        if ($class_name) {
            if (method_exists($class_name, 'getUsersInputColValue')) {
                return $class_name::getUsersInputColValue($input_col);
            }
        }

        // 通常の処理
        return $input_col->value;
    }

    /**
     * デフォルト項目の追加バリデーションルールを取得
     *
     * @param array $base_rules 既存の基本バリデーションルール
     * @param UsersColumns $users_column ユーザカラム情報
     * @return array 追加ルールが適用されたバリデーションルール
     */
    public static function getDefaultColumnAdditionalRules(array $base_rules, UsersColumns $users_column) : array
    {
        $additional_rules = [];
        
        // 正規表現チェック
        if ($users_column->rule_regex) {
            $additional_rules[] = 'regex:' . $users_column->rule_regex;
        }
        
        // 基本ルールと追加ルールをマージ
        return array_merge($base_rules, $additional_rules);
    }

    /**
     * セットすべきバリデータールールが存在する場合、受け取った配列にセットして返す
     *
     * @param array $validator_array 二次元配列
     * @param \App\Models\User\Databases\DatabasesColumns $users_column
     * @param int $user_id
     * @return array
     */
    public static function getValidatorRule($validator_array, $users_column, int $columns_set_id, $user_id = null)
    {
        $validator_rule = null;
        // 必須チェック
        if ($users_column->required) {
            $validator_rule[] = 'required';
        }
        // メールアドレスチェック
        if ($users_column->column_type == UserColumnType::mail) {
            $validator_rule[] = 'email';
            $validator_rule[] = new CustomValiUserEmailUnique($columns_set_id, $user_id);
            if ($users_column->required == 0) {
                $validator_rule[] = 'nullable';
            }
        }
        // 数値チェック
        if ($users_column->rule_allowed_numeric) {
            $validator_rule[] = 'nullable';
            $validator_rule[] = 'numeric';
        }
        // 英数値チェック
        if ($users_column->rule_allowed_alpha_numeric) {
            $validator_rule[] = 'nullable';
            $validator_rule[] = new CustomValiAlphaNumForMultiByte();
        }
        // 最大文字数チェック
        if ($users_column->rule_word_count) {
            $validator_rule[] = new CustomValiCheckWidthForString($users_column->column_name, $users_column->rule_word_count);
        }
        // 指定桁数チェック
        if ($users_column->rule_digits_or_less) {
            $validator_rule[] = 'digits:' . $users_column->rule_digits_or_less;
        }
        // 最大値チェック
        if ($users_column->rule_max) {
            $validator_rule[] = 'nullable';
            $validator_rule[] = 'numeric';
            $validator_rule[] = 'max:' . $users_column->rule_max;
        }
        // 最小値チェック
        if ($users_column->rule_min) {
            $validator_rule[] = 'nullable';
            $validator_rule[] = 'numeric';
            $validator_rule[] = 'min:' . $users_column->rule_min;
        }
        // 正規表現チェック
        if ($users_column->rule_regex) {
            $validator_rule[] = 'nullable';
            $validator_rule[] = 'regex:' . $users_column->rule_regex;
        }
        // 単一選択チェック
        // 複数選択チェック
        // リストボックスチェック
        if ($users_column->column_type == UserColumnType::radio ||
                $users_column->column_type == UserColumnType::checkbox ||
                $users_column->column_type == UserColumnType::select) {
            // カラムの選択肢用データ
            $selects = UsersColumnsSelects::where('users_columns_id', $users_column->id)
                ->orderBy('users_columns_id', 'asc')
                ->orderBy('display_sequence', 'asc')
                ->pluck('value')
                ->toArray();

            // 単一選択チェック
            if ($users_column->column_type == UserColumnType::radio) {
                $validator_rule[] = 'nullable';
                // Rule::inのみで、selectsの中の１つが入ってるかチェック
                $validator_rule[] = Rule::in($selects);
            }
            // 複数選択チェック
            if ($users_column->column_type == UserColumnType::checkbox) {
                $validator_rule[] = 'nullable';
                // array & Rule::in で、selectsの中の値に存在しているかチェック
                $validator_rule[] = 'array';
                $validator_rule[] = Rule::in($selects);
            }
            // リストボックスチェック
            if ($users_column->column_type == UserColumnType::select) {
                $validator_rule[] = 'nullable';
                // Rule::inのみで、selectsの中の１つが入ってるかチェック
                $validator_rule[] = Rule::in($selects);
            }
        }
        // 所属型マスタ存在チェック
        if ($users_column->column_type == UserColumnType::affiliation) {
            $validator_rule[] = 'nullable';
            $validator_rule[] = 'exists:sections,id';
        }

        // バリデータールールをセット
        if ($validator_rule) {
            $validator_array['column']['users_columns_value.' . $users_column->id] = $validator_rule;
            $validator_array['message']['users_columns_value.' . $users_column->id] = $users_column->column_name;
        }

        return $validator_array;
    }

    /**
     * 通知の埋め込みタグ値の配列
     */
    public static function getNoticeEmbeddedTags(User $user): array
    {
        $configs = Configs::getSharedConfigs();
        // category=general or category=user_register & columns_set_id に configs を絞る
        $configs = $configs->filter(function ($config, $key) use ($user) {
            return $config->category = 'general' || ($config->category = 'user_register' && $config->additional1 = $user->columns_set_id);
        });

        $default = [
            UserRegisterNoticeEmbeddedTag::site_name => Configs::getConfigsValue($configs, 'base_site_name'),
            UserRegisterNoticeEmbeddedTag::to_datetime => date("Y/m/d H:i:s"),
            UserRegisterNoticeEmbeddedTag::body => self::getMailContentsText($configs, $user),
            UserRegisterNoticeEmbeddedTag::user_name => $user->name,
            UserRegisterNoticeEmbeddedTag::login_id => $user->userid,
            UserRegisterNoticeEmbeddedTag::password => session('password'),
            UserRegisterNoticeEmbeddedTag::email => $user->email,
            UserRegisterNoticeEmbeddedTag::user_register_requre_privacy => Configs::getConfigsValue($configs, 'user_register_requre_privacy') ? '以下の内容に同意します。' : '',
        ];

        // ユーザーのカラム
        $users_columns = self::getUsersColumns($user->columns_set_id);
        // ユーザーカラムの登録データ
        $users_input_cols = UsersInputCols::where('users_id', $user->id)
            ->get()
            // keyをusers_input_colsにした結果をセット
            ->mapWithKeys(function ($item) {
                return [$item['users_columns_id'] => $item];
            });

        foreach ($users_columns as $users_column) {
            if (UsersColumns::isLoopNotShowEmbeddedTagColumnType($users_column->column_type)) {
                // 既に取得済みのため、ここでは取得しない
                continue;
            }

            // 埋め込みタグの値
            $value = self::getNoticeEmbeddedTagsValue($users_input_cols, $users_column, $users_columns);

            $default["X-{$users_column->column_name}"] = $value;
        }

        return $default;
    }

    /**
     * メール本文取得
     */
    public static function getMailContentsText($configs, $user)
    {
        // ユーザーのカラム
        $users_columns = self::getUsersColumns($user->columns_set_id);

        // メールの内容
        $contents_text = '';
        $contents_text .= UsersColumns::getLabelUserName($users_columns)  . "： {$user->name}\n";
        $contents_text .= UsersColumns::getLabelLoginId($users_columns)   . "： {$user->userid}\n";
        $contents_text .= UsersColumns::getLabelUserEmail($users_columns) . "： {$user->email}\n";

        // ユーザーカラムの登録データ
        $users_input_cols = UsersInputCols::where('users_id', $user->id)
            ->get()
            // keyをusers_input_colsにした結果をセット
            ->mapWithKeys(function ($item) {
                return [$item['users_columns_id'] => $item];
            });

        foreach ($users_columns as $users_column) {
            if (UsersColumns::isLoopNotShowEmbeddedTagColumnType($users_column->column_type)) {
                continue;
            }

            // 埋め込みタグの値
            $value = self::getNoticeEmbeddedTagsValue($users_input_cols, $users_column, $users_columns);

            // メールの内容
            $contents_text .= $users_column->column_name . "：" . $value . "\n";
        }

        if (Configs::getConfigsValue($configs, 'user_register_requre_privacy')) {
            // 同意設定ONの場合、同意は必須のため、必ず文字列をセットする。
            $contents_text .= "個人情報保護方針への同意 ： 以下の内容に同意します。\n";
        }

        // 最後の改行を除去
        $contents_text = trim($contents_text);
        return $contents_text;
    }

    /**
     * 埋め込みタグの値 取得
     */
    public static function getNoticeEmbeddedTagsValue(Collection $users_input_cols, UsersColumns $users_column, Collection $users_columns): ?string
    {
        $class_name = self::getOptionClass();
        // オプションクラス有＋メソッド有なら呼ぶ
        if ($class_name) {
            if (method_exists($class_name, 'getNoticeEmbeddedTagsValue')) {
                // $users_columns は、他項目と連動するカスタム型の値取得に利用
                return $class_name::getNoticeEmbeddedTagsValue($users_input_cols, $users_column, $users_columns);
            }
        }

        if (!isset($users_input_cols[$users_column->id])) {
            return "";
        }

        $value = "";
        if (is_array($users_input_cols[$users_column->id])) {
            $value = implode(self::CHECKBOX_SEPARATOR, $users_input_cols[$users_column->id]->value);
        } else {
            $value = $users_input_cols[$users_column->id]->value;
        }

        return $value;
    }

    /**
     * バリデーション配列の構築処理
     *
     * @param array $validator_array 既存のバリデーション配列
     * @param Collection $users_columns ユーザカラム情報
     * @param int $columns_set_id 項目セットID
     * @param int|null $user_id ユーザID（更新時のみ）
     * @param \Illuminate\Http\Request|array|null $request_data リクエストデータ（条件付き表示の評価用、Requestオブジェクトまたは配列）
     * @return array 構築されたバリデーション配列
     */
    public static function buildValidatorArray(array $validator_array, Collection $users_columns, int $columns_set_id, ?int $user_id = null, $request_data = null): array
    {
        foreach ($users_columns as $users_column) {
            // 【追加】条件付き表示の評価（$request_dataがある場合のみ）
            if ($request_data && !self::isColumnDisplayed($users_column, $request_data)) {
                // 非表示の項目はバリデーションをスキップ
                continue;
            }

            if (UsersColumns::isLoopNotShowColumnType($users_column->column_type)) {
                // デフォルト項目の場合、基本バリデーション＋追加バリデーションを設定
                if (UsersColumns::isFixedColumnType($users_column->column_type)) {
                    // カラムタイプとフィールド名のマッピング定義
                    $field_mapping = [
                        UserColumnType::user_name => 'name',      // ユーザ名
                        UserColumnType::login_id => 'userid',     // ログインID
                        UserColumnType::user_email => 'email',    // メールアドレス
                    ];

                    // マッピングに該当するデフォルト項目の場合のみ処理
                    if (isset($field_mapping[$users_column->column_type])) {
                        $field_name = $field_mapping[$users_column->column_type];

                        // 既に設定されている基本バリデーションルールを取得 (例: 'required', 'max:255' など)
                        $base_rules = $validator_array['column'][$field_name] ?? [];

                        // 文字列形式('required|max:255')の場合は配列形式に変換 ※ Laravelのバリデーションは配列形式と文字列形式の両方をサポートしている為
                        if (is_string($base_rules)) {
                            $base_rules = explode('|', $base_rules);
                        }

                        // 基本ルールが存在する場合、追加バリデーションルール（正規表現など）を適用
                        if (!empty($base_rules)) {
                            // 基本ルールに追加ルール（正規表現など）をマージ
                            $enhanced_rules = self::getDefaultColumnAdditionalRules($base_rules, $users_column);
                            // 拡張されたルールを元の配列に反映
                            $validator_array['column'][$field_name] = $enhanced_rules;
                        }
                    }
                }
                continue;
            }
            // 通常項目のバリデーションルールをセット
            $validator_array = self::getValidatorRule($validator_array, $users_column, $columns_set_id, $user_id);
        }

        return $validator_array;
    }


    /**
     * オプションクラスを返す
     */
    private static function getOptionClass(): ?string
    {
        $class_name = "App\PluginsOption\Manage\UserManage\UsersToolOption";
        // オプションあり
        if (class_exists($class_name)) {
            return $class_name;
        }
        return null;
    }

    /**
     * 条件付き表示の設定情報を取得
     *
     * @param int $columns_set_id カラムセットID
     * @return array 条件付き表示の設定情報の配列
     */
    public static function getConditionalDisplaySettings($columns_set_id)
    {
        $conditional_columns = UsersColumns::where('columns_set_id', $columns_set_id)
            ->where('conditional_display_flag', ShowType::show)
            ->whereNotNull('conditional_trigger_column_id')
            ->whereNotNull('conditional_operator')
            ->get();

        // トリガー項目を一括取得（N+1クエリ対策）
        $trigger_ids = $conditional_columns->pluck('conditional_trigger_column_id')->unique();
        $trigger_columns = UsersColumns::whereIn('id', $trigger_ids)->get()->keyBy('id');

        $settings = [];
        foreach ($conditional_columns as $column) {
            $trigger_column = $trigger_columns->get($column->conditional_trigger_column_id);

            $settings[] = [
                'target_column_id' => $column->id,
                'trigger_column_id' => $column->conditional_trigger_column_id,
                'trigger_column_type' => $trigger_column ? $trigger_column->column_type : null,
                'operator' => $column->conditional_operator,
                'value' => $column->conditional_value,
                'required' => $column->required == Required::on, // 必須フラグを追加
            ];
        }

        return $settings;
    }

    /**
     * 循環依存をチェックする
     *
     * 指定された項目をトリガーに設定した場合、循環依存が発生しないかをチェックします。
     * 例: A→B→C→A のような循環参照を検出
     *
     * @param int $column_id 条件付き表示を設定する項目のID
     * @param int $trigger_column_id トリガーとして設定しようとしている項目のID
     * @param int $columns_set_id 項目セットID
     * @return bool 循環依存がある場合true、ない場合false
     */
    public static function hasCyclicDependency($column_id, $trigger_column_id, $columns_set_id)
    {
        // トリガー項目が設定されていない場合は循環しない
        if (empty($trigger_column_id)) {
            return false;
        }

        // 訪問済みノードを記録（無限ループ防止）
        $visited = [];

        // 探索スタック（深さ優先探索）
        $stack = [$trigger_column_id];

        // 同一項目セット内の条件付き表示設定を一度に取得（パフォーマンス最適化）
        $conditional_columns = UsersColumns::where('columns_set_id', $columns_set_id)
            ->where('conditional_display_flag', ShowType::show)
            ->whereNotNull('conditional_trigger_column_id')
            ->get()
            ->keyBy('id');

        while (!empty($stack)) {
            $current_id = array_pop($stack);

            // 自分自身に到達したら循環依存を検出
            if ($current_id == $column_id) {
                return true;
            }

            // 既に訪問済みの場合はスキップ
            if (in_array($current_id, $visited)) {
                continue;
            }

            // 訪問済みとしてマーク
            $visited[] = $current_id;

            // 現在のノードがトリガーとして設定されているか確認
            $current_column = $conditional_columns->get($current_id);
            if ($current_column && $current_column->conditional_trigger_column_id) {
                // 次のトリガーをスタックに追加
                $stack[] = $current_column->conditional_trigger_column_id;
            }
        }

        // 循環依存なし
        return false;
    }

    /**
     * 条件付き表示項目が現在表示されているかを評価
     *
     * @param UsersColumns $column 評価対象の項目
     * @param \Illuminate\Http\Request|array $request_data リクエストデータ（Requestオブジェクトまたは配列）
     * @return bool 表示される場合true、非表示の場合false
     */
    public static function isColumnDisplayed($column, $request_data)
    {
        // 条件付き表示が設定されていない場合は常に表示
        if ($column->conditional_display_flag != ShowType::show) {
            return true;
        }

        // トリガー項目が存在しない場合は表示
        if (!$column->conditional_trigger_column_id) {
            return true;
        }

        // トリガー項目を取得
        $trigger_column = UsersColumns::find($column->conditional_trigger_column_id);
        if (!$trigger_column) {
            return true; // トリガーが見つからない場合は表示
        }

        // トリガー項目の値を取得
        $trigger_value = self::getTriggerValue($trigger_column, $request_data);

        // 条件演算子に基づいて評価
        return self::evaluateCondition(
            $column->conditional_operator,
            $trigger_value,
            $column->conditional_value
        );
    }

    /**
     * トリガー項目の値を取得
     *
     * @param UsersColumns $trigger_column トリガー項目
     * @param \Illuminate\Http\Request|array $request_data リクエストデータ（Requestオブジェクトまたは配列）
     * @return mixed トリガー項目の値
     */
    private static function getTriggerValue($trigger_column, $request_data)
    {
        // システム固定項目の場合
        if (UsersColumns::isFixedColumnType($trigger_column->column_type)) {
            switch ($trigger_column->column_type) {
                case UserColumnType::user_name:
                    return is_array($request_data) ? Arr::get($request_data, 'name') : $request_data->input('name');
                case UserColumnType::login_id:
                    return is_array($request_data) ? Arr::get($request_data, 'userid') : $request_data->input('userid');
                case UserColumnType::user_email:
                    return is_array($request_data) ? Arr::get($request_data, 'email') : $request_data->input('email');
                default:
                    return null;
            }
        }

        // カスタム項目の場合
        $value = is_array($request_data)
            ? Arr::get($request_data, 'users_columns_value.' . $trigger_column->id)
            : $request_data->input('users_columns_value.' . $trigger_column->id);

        // チェックボックス（複数選択）の場合、配列をカンマ区切りに変換
        if (is_array($value)) {
            return implode(',', $value);
        }

        return $value;
    }

    /**
     * 条件を評価
     *
     * @param string $operator 条件演算子
     * @param mixed $trigger_value トリガー項目の値
     * @param mixed $condition_value 条件値
     * @return bool 条件を満たす場合true
     */
    private static function evaluateCondition($operator, $trigger_value, $condition_value)
    {
        switch ($operator) {
            case ConditionalOperator::equals:
                return $trigger_value == $condition_value;

            case ConditionalOperator::not_equals:
                return $trigger_value != $condition_value;

            case ConditionalOperator::is_empty:
                return empty($trigger_value);

            case ConditionalOperator::is_not_empty:
                return !empty($trigger_value);

            default:
                return true; // 未知の演算子は表示
        }
    }
}
