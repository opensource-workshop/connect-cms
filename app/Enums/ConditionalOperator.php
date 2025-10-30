<?php

namespace App\Enums;

/**
 * 条件演算子
 */
class ConditionalOperator extends EnumsBase
{
    // 定数メンバ
    const equals = 'equals';
    const not_equals = 'not_equals';
    const is_empty = 'is_empty';
    const is_not_empty = 'is_not_empty';

    // key/valueの連想配列
    const enum = [
        self::equals => '次の値と等しい',
        self::not_equals => '次の値と等しくない',
        self::is_empty => '空白である',
        self::is_not_empty => '空白でない',
    ];
}
