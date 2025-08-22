<?php

namespace App\Enums;

use App\Enums\EnumsBase;

/**
 * ブログの年月絞り込み機能
 *
 * @author 井上 雅人 <inoue@opensource-workshop.co.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category ブログ
 * @package Enums
 */
final class BlogNarrowingDownTypeForPostedMonth extends EnumsBase
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