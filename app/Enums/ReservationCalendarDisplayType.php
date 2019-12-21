<?php

namespace App\Enums;

/**
 * 施設予約のカレンダー表示区分
 */
final class ReservationCalendarDisplayType
{
    // 定数メンバ
    const month = '1';
    const week = '2';

    // key/valueの連想配列
    const enum = [
        self::month=>'月',
        self::week=>'週',
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
