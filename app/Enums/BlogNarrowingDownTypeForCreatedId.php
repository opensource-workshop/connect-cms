<?php

namespace App\Enums;

use App\Enums\EnumsBase;

/**
 * ブログの投稿者絞り込み機能
 *
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category ブログ
 * @package Enums
 */
final class BlogNarrowingDownTypeForCreatedId extends EnumsBase
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
