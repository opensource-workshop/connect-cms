<?php

namespace App\Enums;

/**
 * 曜日
 */
final class DayOfWeek
{
    // 定数メンバ
    const sun = 0;
    const mon = 1;
    const tue = 2;
    const wed = 3;
    const thu = 4;
    const fri = 5;
    const sat = 6;

    // key/valueの連想配列
    const enum = [
        self::sun=>'日',
        self::mon=>'月',
        self::tue=>'火',
        self::wed=>'水',
        self::thu=>'木',
        self::fri=>'金',
        self::sat=>'土',
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
    public static function getMembers(){
        return self::enum;
    }
}
