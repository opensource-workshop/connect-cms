<?php

namespace App\Enums;

use App\Enums\EnumsBase;

/**
 * キャビネットのフレーム設定項目
 */
final class CabinetFrameConfig extends EnumsBase
{
    // 定数メンバ
    const sort = 'sort';

    // key/valueの連想配列
    const enum = [
        self::sort => '並び順',
    ];
}
