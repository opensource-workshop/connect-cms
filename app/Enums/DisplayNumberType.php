<?php

namespace App\Enums;

use App\Enums\EnumsBase;

/**
 * 表示件数区分
 */
final class DisplayNumberType extends EnumsBase
{
    // 定数メンバ
    const num_20 = 20;
    const num_50 = 50;
    const num_100 = 100;
    const num_200 = 200;

    // key/valueの連想配列
    const enum = [
        self::num_20 => '20件',
        self::num_50 => '50件',
        self::num_100 => '100件',
        self::num_200 => '200件',
    ];
}
