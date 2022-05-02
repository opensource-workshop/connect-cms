<?php

namespace App\Enums;

use App\Enums\EnumsBase;

/**
 * 施設表示
 */
final class FacilityDisplayType extends EnumsBase
{
    // 定数メンバ
    const all = 'all';
    const only = 'only';

    // key/valueの連想配列
    const enum = [
        self::all => '全ての施設を表示',
        self::only => '１つの施設を選んで表示',
    ];
}
