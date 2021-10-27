<?php

namespace App\Enums;

use App\Enums\EnumsBase;

/**
 * 新着情報のフレーム設定項目
 */
final class WhatsnewFrameConfig extends EnumsBase
{
    // 定数メンバ
    const thumbnail = 'thumbnail';
    const thumbnail_width = 'thumbnail_width';

    // key/valueの連想配列
    const enum = [
        self::thumbnail => 'サムネイル画像',
        self::thumbnail_width => '画像サイズ（横）',
    ];
}
