<?php

namespace App\Enums;

use App\Enums\EnumsBase;

/**
 * 使用する・使用しない区分
 */
final class UseType extends EnumsBase
{
    // 定数メンバ
    const not_use = 0;
    const use = 1;

    // key/valueの連想配列
    const enum = [
        self::not_use => '使用しない',
        self::use => '使用する',
    ];
}
