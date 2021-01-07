<?php

namespace App\Enums;

use App\Enums\EnumsBase;

/**
 * 外部認証関係
 */
final class AuthMethodType extends EnumsBase
{
    // 定数メンバ
    const netcommons2 = 'netcommons2';
    const shibboleth = 'shibboleth';

    // key/valueの連想配列
    const enum = [
        self::netcommons2 => 'NetCommons2認証',
        self::shibboleth => 'Shibboleth認証',
    ];
}
