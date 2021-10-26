<?php

namespace App\Enums;

/**
 * 必須入力区分
 */
final class Required extends EnumsBase
{
    // 定数メンバ
    const off = 0;
    const on = 1;

    // key/valueの連想配列
    const enum = [
        self::off=>'必須ではない',
        self::on=>'必須である',
    ];
}
