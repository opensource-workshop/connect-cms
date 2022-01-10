<?php

namespace App\Enums;

/**
 * 許可区分
 */
final class PermissionType extends EnumsBase
{
    // 定数メンバ
    const not_allowed = 0;
    const allowed = 1;

    // key/valueの連想配列
    const enum = [
        self::not_allowed=>'許可しない',
        self::allowed=>'許可する',
    ];
}
