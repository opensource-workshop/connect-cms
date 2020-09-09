<?php

namespace App\Enums;

/**
 * 表示する・表示しない区分
 */
final class ShowType
{
    // 定数メンバ
    const not_show = 0;
    const show = 1;

    // key/valueの連想配列
    const enum = [
        self::not_show=>'表示しない',
        self::show=>'表示する',
    ];

    /*
    * 対応した和名を返す
    */
    public static function getDescription($key): string
    {
        return self::enum[$key];
    }

    /*
    * key/valueの連想配列を返す
    */
    public static function getMembers()
    {
        return self::enum;
    }
}
