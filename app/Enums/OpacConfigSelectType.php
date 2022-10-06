<?php

namespace App\Enums;

use App\Enums\EnumsBase;

/**
 * OPAC選択肢設定 区分
 */
final class OpacConfigSelectType extends EnumsBase
{
    // 定数メンバ
    const delivery_request_time = 'delivery_request_time';

    // key/valueの連想配列
    const enum = [
        self::delivery_request_time => '配送希望時間',
    ];
}
