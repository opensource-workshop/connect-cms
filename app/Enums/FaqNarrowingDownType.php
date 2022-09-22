<?php

namespace App\Enums;

use App\Enums\EnumsBase;

/**
 * FAQの絞り込み機能
 */
final class FaqNarrowingDownType extends EnumsBase
{
    // 定数メンバ
    const none = 'none';
    const dropdown = 'dropdown';
    const button = 'button';

    // key/valueの連想配列
    const enum = [
        self::none => '表示しない',
        self::dropdown => 'ドロップダウン形式',
        self::button => 'ボタン形式',
    ];
}
