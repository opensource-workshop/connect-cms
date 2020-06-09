<?php

namespace App\Enums;

/**
 * 必須入力区分
 */
final class Required
{
    // 定数メンバ
    const off = 0;
    const on = 1;

    // key/valueの連想配列
    const enum = [
        self::off=>'必須ではない',
        self::on=>'必須である',
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
