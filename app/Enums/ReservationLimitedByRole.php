<?php

namespace App\Enums;

use App\Enums\EnumsBase;

/**
 * 権限で予約制限
 */
final class ReservationLimitedByRole extends EnumsBase
{
    // 定数メンバ
    const not_limited = 0;
    const limited = 1;

    // key/valueの連想配列
    const enum = [
        self::not_limited => '権限で予約制限しない',
        self::limited => '権限で予約制限する',
    ];
}
