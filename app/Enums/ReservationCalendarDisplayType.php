<?php

namespace App\Enums;

/**
 * 施設予約のカレンダー表示区分
 */
final class ReservationCalendarDisplayType extends EnumsBase
{
    // 定数メンバ
    const month = '1';
    const week = '2';

    // key/valueの連想配列
    const enum = [
        self::month => '月',
        self::week => '週',
    ];
}
