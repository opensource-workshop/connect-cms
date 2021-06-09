<?php

namespace App\Enums;

/**
 * 使用する・使用しない区分
 */
final class UseType
{
    // 定数メンバ
    const not_use = 0;
    const use = 1;

    // key/valueの連想配列
    const enum = [
        self::not_use=>'使用しない',
        self::use=>'使用する',
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