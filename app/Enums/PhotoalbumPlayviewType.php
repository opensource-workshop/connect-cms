<?php

namespace App\Enums;

/**
 * 表示する・表示しない区分
 */
final class PhotoalbumPlayviewType extends EnumsBase
{
    // 定数メンバ
    const all = 'all';
    const part = 'part';

    // key/valueの連想配列
    const enum = [
        self::all => 0,
        self::part => 1,
    ];
}
