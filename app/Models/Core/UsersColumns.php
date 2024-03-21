<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

use App\UserableNohistory;
use App\Enums\UserColumnType;

class UsersColumns extends Model
{
    // 保存時のユーザー関連データの保持（履歴なしUserable）
    use UserableNohistory;

    // 更新する項目の定義
    protected $fillable = [
        'columns_set_id',
        'column_type',
        'column_name',
        'use_variable',
        'variable_name',
        'is_fixed_column',
        'is_show_auto_regist',
        'is_show_my_page',
        'is_edit_my_page',
        'required',
        'caption',
        'caption_color',
        'place_holder',
        'rule_allowed_numeric',
        'rule_allowed_alpha_numeric',
        'rule_digits_or_less',
        'rule_max',
        'rule_min',
        'rule_regex',
        'rule_word_count',
        'display_sequence',
    ];

    /**
     * 選択肢タイプのカラム型か
     */
    public static function isSelectColumnType($column_type): bool
    {
        if ($column_type == UserColumnType::radio ||
                $column_type == UserColumnType::checkbox ||
                $column_type == UserColumnType::select) {
            return true;
        }
        return false;
    }

    /**
     * 選択肢系のカラム型か
     */
    public static function isChoicesColumnType($column_type): bool
    {
        // ラジオとチェックボックスは選択肢にラベルを使っているため、項目名のラベルにforを付けない
        if ($column_type == UserColumnType::radio ||
                $column_type == UserColumnType::checkbox ||
                $column_type == UserColumnType::agree) {
            return true;
        }
        return false;
    }

    /**
     * 固定項目のカラム型か
     */
    public static function isFixedColumnType($column_type): bool
    {
        if ($column_type == UserColumnType::user_name ||
                $column_type == UserColumnType::login_id ||
                $column_type == UserColumnType::user_email ||
                $column_type == UserColumnType::user_password) {
            return true;
        }
        return false;
    }

    /**
     * ループで非表示のカラム型か
     */
    public static function isLoopNotShowColumnType($column_type): bool
    {
        if (in_array($column_type, UserColumnType::loopNotShowColumnTypes())) {
            return true;
        }
        return false;
    }

    /**
     * ループで非表示のカラム型 取得
     */
    public static function loopNotShowColumnTypes(): array
    {
        return UserColumnType::loopNotShowColumnTypes();
    }

    /**
     * メール埋め込みタグのループで 既に取得済みのため、表示しないカラム型か
     */
    public static function isLoopNotShowEmbeddedTagColumnType($column_type): bool
    {
        if (in_array($column_type, UserColumnType::loopNotShowEmbeddedTagColumnTypes())) {
            return true;
        }
        return false;
    }

    /**
     * 検索で完全一致のカラム型か
     */
    public static function isSearchExactMatchColumnType($column_type): bool
    {
        if (in_array($column_type, UserColumnType::searchExactMatchColumnTypes())) {
            return true;
        }
        return false;
    }

    /**
     * 表示のみのカラム型か
     */
    public static function isShowOnlyColumnType($column_type): bool
    {
        if (in_array($column_type, UserColumnType::showOnlyColumnTypes())) {
            return true;
        }
        return false;
    }

    /**
     * 自動登録で表示のみのカラム型か
     */
    public static function isShowOnlyAutoRegistColumnType($column_type): bool
    {
        if ($column_type == UserColumnType::created_at || $column_type == UserColumnType::updated_at) {
            return true;
        }
        return false;
    }

    /**
     * 自動登録のみ表示するカラム型か
     */
    public static function isAutoRegistOnlyColumnTypes($column_type): bool
    {
        if (in_array($column_type, UserColumnType::autoRegistOnlyColumnTypes())) {
            return true;
        }
        return false;
    }

    /**
     * コレクションからラベル名取得
     */
    private static function getLabelFromCollection(Collection $users_columns, string $column_type, string $default): string
    {
        $column = $users_columns->firstWhere('column_type', $column_type);
        return $column->column_name ?? $default;
    }

    /**
     * ログインIDのラベル取得
     */
    public static function getLabelLoginId(Collection $users_columns): string
    {
        return self::getLabelFromCollection($users_columns, UserColumnType::login_id, 'ログインID');
    }

    /**
     * ユーザ名のラベル取得
     */
    public static function getLabelUserName(Collection $users_columns): string
    {
        return self::getLabelFromCollection($users_columns, UserColumnType::user_name, 'ユーザ名');
    }

    /**
     * メールアドレスのラベル取得
     */
    public static function getLabelUserEmail(Collection $users_columns): string
    {
        return self::getLabelFromCollection($users_columns, UserColumnType::user_email, 'メールアドレス');
    }

    /**
     * パスワードのラベル取得
     */
    public static function getLabelUserPassword(Collection $users_columns): string
    {
        return self::getLabelFromCollection($users_columns, UserColumnType::user_password, 'パスワード');
    }

    /**
     * 登録日時のラベル取得
     */
    public static function getLabelCreatedAt(Collection $users_columns): string
    {
        return self::getLabelFromCollection($users_columns, UserColumnType::created_at, '登録日時');
    }

    /**
     * 更新日時のラベル取得
     */
    public static function getLabelUpdatedAt(Collection $users_columns): string
    {
        return self::getLabelFromCollection($users_columns, UserColumnType::updated_at, '更新日時');
    }

    /**
     * UsersColumnsSet登録時のUsersColumns初期登録
     */
    public static function initInsertForRegistUsersColumnsSet(int $columns_set_id): void
    {
        self::insert([
            [
                'columns_set_id'        => $columns_set_id,
                'column_type'           => UserColumnType::user_name,
                'column_name'           => UserColumnType::getDescriptionFixed(UserColumnType::user_name),
                'is_fixed_column'       => 1,
                'is_show_auto_regist'   => 1,
                'is_show_my_page'       => 1,
                'is_edit_my_page'       => 0,
                'required'              => 1,
                'display_sequence'      => 1,
            ],
            [
                'columns_set_id'        => $columns_set_id,
                'column_type'           => UserColumnType::login_id,
                'column_name'           => UserColumnType::getDescriptionFixed(UserColumnType::login_id),
                'is_fixed_column'       => 1,                           // (画面からいじれない項目)
                'is_show_auto_regist'   => 1,
                'is_show_my_page'       => 1,
                'is_edit_my_page'       => 0,
                'required'              => 1,                           // 設定するけど is_fixed_column=1 はおそらくrequiredは参照しない
                'display_sequence'      => 2,
            ],
            [
                'columns_set_id'        => $columns_set_id,
                'column_type'           => UserColumnType::user_email,
                'column_name'           => UserColumnType::getDescriptionFixed(UserColumnType::user_email),
                'is_fixed_column'       => 1,
                'is_show_auto_regist'   => 1,
                'is_show_my_page'       => 1,
                'is_edit_my_page'       => 1,
                'required'              => 1,
                'display_sequence'      => 3,
            ],
            [
                'columns_set_id'        => $columns_set_id,
                'column_type'           => UserColumnType::user_password,
                'column_name'           => UserColumnType::getDescriptionFixed(UserColumnType::user_password),
                'is_fixed_column'       => 1,
                'is_show_auto_regist'   => 1,
                'is_show_my_page'       => 0,
                'is_edit_my_page'       => 1,
                'required'              => 1,
                'display_sequence'      => 4,
            ],
            [
                'columns_set_id'        => $columns_set_id,
                'column_type'           => UserColumnType::created_at,
                'column_name'           => UserColumnType::getDescriptionFixed(UserColumnType::created_at),
                'is_fixed_column'       => 0,
                'is_show_auto_regist'   => 0,
                'is_show_my_page'       => 1,
                'is_edit_my_page'       => 0,
                'required'              => 0,
                'display_sequence'      => 5,
            ],
        ]);
    }
}
