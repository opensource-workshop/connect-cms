<?php

namespace App\Enums;

use App\Enums\EnumsBase;

/**
 * 画像リサイズ時のPHPメモリ数
 */
final class MemoryLimitForImageResize extends EnumsBase
{
    // 定数メンバ
    const limit_256m = '256M';
    const limit_512m = '512M';
    const limit_1g = '1G';

    // key/valueの連想配列
    const enum = [
        self::limit_256m => '256M',
        self::limit_512m => '512M',
        self::limit_1g => '1G',
    ];
}
