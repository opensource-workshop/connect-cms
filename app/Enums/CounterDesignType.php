<?php

namespace App\Enums;

/**
 * カウンター表示形式
 */
final class CounterDesignType extends EnumsBase
{
    // 定数メンバ
    const numeric = 'numeric';
    const numeric_comma = 'numeric_comma';

    // key/valueの連想配列
    const enum = [
        self::numeric => '数字（カンマなし）',
        self::numeric_comma => '数字（カンマあり）',
    ];
}
