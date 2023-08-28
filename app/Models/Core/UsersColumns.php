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
}
