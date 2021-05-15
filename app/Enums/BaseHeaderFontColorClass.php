<?php

namespace App\Enums;

use App\Enums\EnumsBase;

/**
 * 基本ヘッダーnavの文字色クラス
 */
final class BaseHeaderFontColorClass extends EnumsBase
{
    // 定数メンバ
    const navbar_light = 'navbar-light';
    const navbar_dark = 'navbar-dark';

    // key/valueの連想配列
    const enum = [
        self::navbar_light => '暗めの文字色',
        self::navbar_dark => '明るめの文字色',
    ];
}
