<?php

namespace App\Enums;

use Illuminate\Support\Facades\App;

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

    // key/valueの連想配列 日本語版
    const enum_ja = [
        self::sun=>'日',
        self::mon=>'月',
        self::tue=>'火',
        self::wed=>'水',
        self::thu=>'木',
        self::fri=>'金',
        self::sat=>'土',
    ];

    // key/valueの連想配列 英語版
    const enum_en = [
        self::sun=>'sun',
        self::mon=>'mon',
        self::tue=>'tue',
        self::wed=>'wed',
        self::thu=>'thu',
        self::fri=>'fri',
        self::sat=>'sat',
    ];

    /*
    * 対応した和名を返す
    */
    public static function getDescription($key): string
    {
        return App::getLocale() == \ConnectLocale::en ? self::enum_en[$key] : self::enum_ja[$key];
    }

    /*
    * key/valueの連想配列を返す
    */
    public static function getMembers()
    {
        return App::getLocale() == \ConnectLocale::en ? self::enum_en : self::enum_ja;
    }
}
