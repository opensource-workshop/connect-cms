<?php

namespace App\Enums;

use App\Enums\EnumsBase;

/**
 * データベース 権限名
 * @see \App\Enums\DatabaseColumnRoleName よりコピー。
 */
final class DatabaseRoleName extends EnumsBase
{
    // 定数メンバ
    const role_article = 'role_article';
    const role_reporter = 'role_reporter';

    // key/valueの連想配列
    const enum = [
        self::role_article => 'モデレータ',
        self::role_reporter => '編集者',
    ];

    /*
     * 権限毎に登録・編集で表示にする指定のkey/valueの連想配列を返す
     */
    public static function getRegistEditDisplayMembers()
    {
        return static::enum;
    }
}
