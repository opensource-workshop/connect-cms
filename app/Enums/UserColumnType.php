<?php

namespace App\Enums;

use App\Enums\EnumsBase;

/**
 * ユーザ項目区分
 */
final class UserColumnType extends EnumsBase
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
}
