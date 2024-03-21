<?php

namespace App\Enums;

use App\Enums\EnumsBase;

/**
 * ユーザ項目区分
 */
class UserColumnType extends EnumsBase
{
    // 定数メンバ
    const text = 'text';
    const textarea = 'textarea';
    const radio = 'radio';
    const checkbox = 'checkbox';
    const select = 'select';
    const mail = 'mail';
    const agree = 'agree';
    const affiliation = 'affiliation';
    const created_at = 'created_at';
    const updated_at = 'updated_at';
    // 以下固定カラム. 追加・削除不可
    const user_name = 'user_name';
    const login_id = 'login_id';
    const user_email = 'user_email';
    const user_password = 'password';

    // key/valueの連想配列
    const enum = [
        self::text => '1行文字列型',
        self::textarea => '複数行文字列型',
        self::radio => '単一選択型',
        self::checkbox => '複数選択型',
        self::select => 'リストボックス型',
        self::mail => 'メールアドレス型',
        self::agree => '同意型',
        self::affiliation => '所属型',
        self::created_at => '登録日時型（自動更新）',
        self::updated_at => '更新日時型（自動更新）',
    ];

    // key/valueの連想配列
    const enum_fixed = [
        self::user_name     => 'ユーザ名',
        self::login_id      => 'ログインID',
        self::user_email    => 'メールアドレス',
        self::user_password => 'パスワード',
        self::created_at    => '登録日時',
    ];

    /**
     * 固定項目の対応した和名を返す
     */
    public static function getDescriptionFixed($key): string
    {
        return static::enum_fixed[$key];
    }

    /**
     * ループで非表示のカラム型 取得
     */
    public static function loopNotShowColumnTypes(): array
    {
        $class_name = self::getOptionClass();
        // オプションクラス有＋メソッド有なら呼ぶ
        if ($class_name) {
            if (method_exists($class_name, 'loopNotShowColumnTypes')) {
                return $class_name::loopNotShowColumnTypes();
            }
        }

        return [
            self::user_name,
            self::login_id,
            self::user_email,
            self::user_password,
            self::created_at,
            self::updated_at,
        ];
    }

    /**
     * メール埋め込みタグのループで 既に取得済みのため、表示しないカラム型 取得
     */
    public static function loopNotShowEmbeddedTagColumnTypes(): array
    {
        return [
            self::user_name,
            self::login_id,
            self::user_email,
            self::user_password,
            self::created_at,
            self::updated_at,
        ];
    }

    /**
     * 検索で完全一致のカラム型 取得
     */
    public static function searchExactMatchColumnTypes(): array
    {
        $class_name = self::getOptionClass();
        // オプションクラス有＋メソッド有なら呼ぶ
        if ($class_name) {
            if (method_exists($class_name, 'searchExactMatchColumnTypes')) {
                return $class_name::searchExactMatchColumnTypes();
            }
        }

        return [];
    }

    /**
     * 表示のみのカラム型 取得
     */
    public static function showOnlyColumnTypes(): array
    {
        $class_name = self::getOptionClass();
        // オプションクラス有＋メソッド有なら呼ぶ
        if ($class_name) {
            if (method_exists($class_name, 'showOnlyColumnTypes')) {
                return $class_name::showOnlyColumnTypes();
            }
        }

        return [
            self::created_at,
            self::updated_at,
        ];
    }

    /**
     * 自動登録のみ表示するカラム型 取得
     */
    public static function autoRegistOnlyColumnTypes(): array
    {
        $class_name = self::getOptionClass();
        // オプションクラス有＋メソッド有なら呼ぶ
        if ($class_name) {
            if (method_exists($class_name, 'autoRegistOnlyColumnTypes')) {
                return $class_name::autoRegistOnlyColumnTypes();
            }
        }

        return [];
    }
}
