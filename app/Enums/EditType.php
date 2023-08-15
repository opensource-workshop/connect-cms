<?php

namespace App\Enums;

use App\Enums\EnumsBase;

/**
 * 編集タイプ
 */
final class EditType extends EnumsBase
{
    // 定数メンバ
    const ng = 0;
    const ok = 1;

    // key/valueの連想配列
    const enum = [
        self::ng => '編集不可',
        self::ok => '編集可',
    ];
}
