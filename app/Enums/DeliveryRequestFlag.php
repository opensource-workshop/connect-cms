<?php

namespace App\Enums;

/**
 * 配送希望フラグ
 */
final class DeliveryRequestFlag extends EnumsBase
{
    // 定数メンバ
    const no = 0;
    const yes = 1;

    // key/valueの連想配列
    const enum = [
        self::no => '希望なし',
        self::yes => '希望あり',
    ];
}
