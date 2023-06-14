<?php

namespace App\Enums;

use App\Enums\EnumsBase;

/**
 * ブログの絞り込み機能
 */
final class BlogNarrowingDownType extends EnumsBase
{
    // 定数メンバ
    const none = '';
    const dropdown = 'dropdown';

    // key/valueの連想配列
    const enum = [
        self::none => '表示しない',
        self::dropdown => 'ドロップダウン形式',
    ];

    /**
     * 初期値
     */
    public static function getDefault()
    {
        return self::none;
    }
}
