<?php

namespace App\Enums;

use App\Enums\EnumsBase;

/**
 * フォームのステータス関係
 */
final class FormStatusType extends EnumsBase
{
    // 定数メンバ
    const active = 0;
    const temporary = 1;
    const delete = 9;

    // key/valueの連想配列
    const enum = [
        self::active => '本登録',
        self::temporary => '仮登録',
        self::delete => '削除',
    ];
}
