<?php

namespace App\Enums;

use App\Enums\EnumsBase;

/**
 * データベース カラム権限名
 */
final class DatabaseColumnRoleName extends EnumsBase
{
    // 定数メンバ
    const role_article = 'role_article';
    const role_reporter = 'role_reporter';
    const no_role = 'no_role';
    const not_login = 'not_login';

    // key/valueの連想配列
    const enum = [
        self::role_article => 'モデレータ',
        self::role_reporter => '編集者',
        self::no_role => '権限なし',
        self::not_login => '未ログイン（ホームページ観覧者）',
    ];

    /*
     * 権限毎に登録・編集で表示にする指定のkey/valueの連想配列を返す
     */
    public static function getRegistEditDisplayMembers()
    {
        $regist_edit_displays = static::enum;

        // 登録・編集で、権限なし・未ログインは使わないので、取り除く
        unset($regist_edit_displays[self::no_role]);
        unset($regist_edit_displays[self::not_login]);

        return $regist_edit_displays;
    }
}
