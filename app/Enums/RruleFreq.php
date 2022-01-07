<?php

namespace App\Enums;

use App\Enums\EnumsBase;

/**
 * 繰り返しパターン
 */
final class RruleFreq extends EnumsBase
{
    // 定数メンバ
    const DAILY = 'DAILY';
    const WEEKLY = 'WEEKLY';
    const MONTHLY = 'MONTHLY';
    const YEARLY = 'YEARLY';

    // key/valueの連想配列
    const enum = [
        self::DAILY => '毎日',
        self::WEEKLY => '毎週',
        self::MONTHLY => '毎月',
        self::YEARLY => '毎年',
    ];
}
