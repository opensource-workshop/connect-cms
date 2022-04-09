<?php

namespace App\Enums;

use App\Enums\EnumsBase;

/**
 * ブログフレームの表示条件
 */
final class BlogFrameScope extends EnumsBase
{
    // 定数メンバ
    const all = '';
    const year = 'year';
    const fiscal = 'fiscal';

    // key/valueの連想配列
    const enum = [
        self::all => '全て',
        self::year => '年',
        self::fiscal => '年度',
    ];
}
