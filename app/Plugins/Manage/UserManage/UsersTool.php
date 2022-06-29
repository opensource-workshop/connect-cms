<?php

namespace App\Plugins\Manage\UserManage;

use Illuminate\Validation\Rule;

use App\User;
use App\Models\Core\Configs;
use App\Models\Core\UsersColumns;
use App\Models\Core\UsersColumnsSelects;
use App\Models\Core\UsersInputCols;

use App\Rules\CustomValiAlphaNumForMultiByte;
use App\Rules\CustomValiCheckWidthForString;
use App\Rules\CustomValiUserEmailUnique;

use App\Enums\UserColumnType;
use App\Enums\UserRegisterNoticeEmbeddedTag;

/**
 * ユーザーの便利関数
 */
class UsersTool
{
    const CHECKBOX_SEPARATOR = '|';

    /**
     * ユーザーのカラム取得
     */
    public static function getUsersColumns()
    {
        // ユーザーのカラム
        $users_columns = UsersColumns::orderBy('display_sequence')->get();

        // カラムデータがない場合
        if (empty($users_columns)) {
            return null;
        }

        return $users_columns;
    }

    /**
     * カラムの選択肢 取得
     */
    public static function getUsersColumnsSelects()
    {
        // カラムの選択肢
        $users_columns_selects = UsersColumnsSelects::select('users_columns_selects.*')
                ->join('users_columns', 'users_columns.id', '=', 'users_columns_selects.users_columns_id')
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
        $input_cols = UsersInputCols::select('users_input_cols.*', 'users_columns.column_type', 'users_columns.column_name', 'uploads.client_original_name')
            ->leftJoin('users_columns', 'users_columns.id', '=', 'users_input_cols.users_columns_id')
            ->leftJoin('uploads', 'uploads.id', '=', 'users_input_cols.value')
            ->whereIn('users_id', $users_ids)
            ->orderBy('users_id', 'asc')
            ->orderBy('users_columns_id', 'asc')
            ->get();
        return $input_cols;
    }

    /**
     * セットすべきバリデータールールが存在する場合、受け取った配列にセットして返す
     *
     * @param [array] $validator_array 二次元配列
     * @param [App\Models\User\Databases\DatabasesColumns] $users_column
     * @param [int] $user_id
     * @return array
     */
    public static function getValidatorRule($validator_array, $users_column, $user_id = null)
    {
        $validator_rule = null;
        // 必須チェック
        if ($users_column->required) {
            $validator_rule[] = 'required';
        }
        // メールアドレスチェック
        if ($users_column->column_type == UserColumnType::mail) {
            $validator_rule[] = 'email';
            $validator_rule[] = new CustomValiUserEmailUnique($user_id);
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

        $default = [
            UserRegisterNoticeEmbeddedTag::site_name => Configs::getConfigsValue($configs, 'base_site_name'),
            UserRegisterNoticeEmbeddedTag::to_datetime => date("Y/m/d H:i:s"),
            UserRegisterNoticeEmbeddedTag::body => self::getMailContentsText($configs, $user),
            UserRegisterNoticeEmbeddedTag::user_name => $user->name,
            UserRegisterNoticeEmbeddedTag::login_id => $user->userid,
            // [TODO] 仮登録⇒本登録時にちゃんと動作する？
            UserRegisterNoticeEmbeddedTag::initial_password => session('initial_password'),
            UserRegisterNoticeEmbeddedTag::email => $user->email,
            UserRegisterNoticeEmbeddedTag::user_register_requre_privacy => Configs::getConfigsValue($configs, 'user_register_requre_privacy') ? '以下の内容に同意します。' : '',
        ];

        // ユーザーのカラム
        $users_columns = self::getUsersColumns();
        // ユーザーカラムの登録データ
        $users_input_cols = UsersInputCols::where('users_id', $user->id)
            ->get()
            // keyをusers_input_colsにした結果をセット
            ->mapWithKeys(function ($item) {
                return [$item['users_columns_id'] => $item];
            });

        foreach ($users_columns as $users_column) {
            // [TODO] 同意型対応する方向かなぁ。
            // 除外する埋め込みタグはセットしない
            // if (DatabasesColumns::isNotEmbeddedTagsColumnType($databases_column->column_type)) {
            //     continue;
            // }

            $value = "";
            if (is_array($users_input_cols[$users_column->id])) {
                $value = implode(self::CHECKBOX_SEPARATOR, $users_input_cols[$users_column->id]->value);
            } else {
                $value = $users_input_cols[$users_column->id]->value;
            }

            $default["X-{$users_column->column_name}"] = $value;
        }

        return $default;
    }

    /**
     * メール本文取得
     */
    public static function getMailContentsText($configs, $user)
    {
        // メールの内容
        $contents_text = '';
        $contents_text .= "ユーザ名： " . $user->name . "\n";
        $contents_text .= "ログインID： " . $user->userid . "\n";
        $contents_text .= "eメールアドレス： " . $user->email . "\n";

        // ユーザーのカラム
        $users_columns = self::getUsersColumns();

        // ユーザーカラムの登録データ
        $users_input_cols = UsersInputCols::where('users_id', $user->id)
            ->get()
            // keyをusers_input_colsにした結果をセット
            ->mapWithKeys(function ($item) {
                return [$item['users_columns_id'] => $item];
            });

        foreach ($users_columns as $users_column) {
            $value = "";
            if (is_array($users_input_cols[$users_column->id])) {
                $value = implode(self::CHECKBOX_SEPARATOR, $users_input_cols[$users_column->id]->value);
            } else {
                $value = $users_input_cols[$users_column->id]->value;
            }

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
}
