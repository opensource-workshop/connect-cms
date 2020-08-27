<?php

namespace App\Enums;

use App\Enums\EnumsBase;

/**
 * 新着対象プラグイン
 */
final class WhatsnewsTargetPlugin extends EnumsBase
{
    // 定数メンバ
    const blogs = 'blogs';
    const databases = 'databases';

    // key/valueの連想配列
    const enum = [
        self::blogs => 'ブログ',
        self::databases => 'データベース',
    ];
}
