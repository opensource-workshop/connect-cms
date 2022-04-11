<?php

namespace App\Enums;

use Illuminate\Support\Facades\App;

/**
 * rrule（繰り返し予定）の月
 */
final class RruleByMonth extends EnumsBase
{
    // 定数メンバ
    const january = 1;
    const february = 2;
    const march = 3;
    const april = 4;
    const may = 5;
    const june = 6;
    const july = 7;
    const august = 8;
    const september = 9;
    const october = 10;
    const november = 11;
    const december = 12;

    // key/valueの連想配列 日本語版
    const enum_ja = [
        self::january => '1月',
        self::february => '2月',
        self::march => '3月',
        self::april => '4月',
        self::may => '5月',
        self::june => '6月',
        self::july => '7月',
        self::august => '8月',
        self::september => '9月',
        self::october => '10月',
        self::november => '11月',
        self::december => '12月',
    ];

    // key/valueの連想配列 英語版
    const enum_en = [
        self::january => 'January',
        self::february => 'February',
        self::march => 'March',
        self::april => 'April',
        self::may => 'May',
        self::june => 'June',
        self::july => 'July',
        self::august => 'August',
        self::september => 'September',
        self::october => 'October',
        self::november => 'November',
        self::december => 'December',
    ];

    /*
     * 対応した和名を返す
     */
    public static function getDescription($key): string
    {
        return App::getLocale() == ConnectLocale::en ? self::enum_en[$key] : self::enum_ja[$key];
    }

    /*
     * key/valueの連想配列を返す
     */
    public static function getMembers()
    {
        return App::getLocale() == ConnectLocale::en ? self::enum_en : self::enum_ja;
    }

    /*
     * 日本語のkey/valueの連想配列を返す
     */
    public static function getMembersJa()
    {
        return self::enum_ja;
    }
}
