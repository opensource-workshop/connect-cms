<?php

namespace App\Enums;

use App\Enums\EnumsBase;

/**
 * 検索対象プラグイン
 */
final class SearchsTargetPlugin extends EnumsBase
{
    // 定数メンバ
    const contents = 'contents';
    const blogs = 'blogs';
    const bbses = 'bbses';

    // key/valueの連想配列
    const enum = [
        self::contents => '固定記事',
        self::blogs => 'ブログ',
        // self::bbses => '掲示板',
    ];
}
