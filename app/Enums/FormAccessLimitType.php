<?php

namespace App\Enums;

use App\Enums\EnumsBase;

/**
 * フォームの閲覧制限タイプ
 */
final class FormAccessLimitType extends EnumsBase
{
    // 定数メンバ
    const none = 0;
    const password = 1;

    // key/valueの連想配列
    const enum = [
        self::none => '制限しない',
        self::password => 'パスワードで閲覧制限する',
    ];

    /**
     * 初期値
     */
    public static function getDefault()
    {
        return self::none;
    }
}
