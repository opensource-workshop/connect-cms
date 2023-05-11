<?php

namespace App\Enums;

use App\Enums\EnumsBase;

/**
 * Webサイトタイプ
 */
final class WebsiteType extends EnumsBase
{
    // 定数メンバ
    const netcommons2 = 'netcommons2';
    const netcommons3 = 'netcommons3';
    const html = 'html';
    

    // key/valueの連想配列
    const enum = [
        self::netcommons2 => 'NetCommons2',
        self::netcommons3 => 'NetCommons3',
        self::html => 'HTML',
    ];
}
