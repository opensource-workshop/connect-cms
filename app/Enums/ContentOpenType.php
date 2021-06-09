<?php

namespace App\Enums;

use App\Enums\EnumsBase;

/**
 * コンテンツ公開区分
 */
final class ContentOpenType extends EnumsBase
{
    // 定数メンバ
    const always_open = 1;
    const always_close = 2;
    const limited_open = 3;

    // key/valueの連想配列
    const enum = [
        self::always_open => '公開',
        self::always_close => '非公開',
        self::limited_open => '限定公開',
    ];
}
