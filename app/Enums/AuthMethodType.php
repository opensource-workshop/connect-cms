<?php

namespace App\Enums;

use App\Enums\EnumsBase;

/**
 * 外部認証関係
 */
final class AuthMethodType extends EnumsBase
{
    // 定数メンバ
    const ldap = 'ldap';
    const shibboleth = 'shibboleth';
    const netcommons2 = 'netcommons2';

    // key/valueの連想配列
    const enum = [
        self::ldap => 'LDAP認証',
        self::shibboleth => 'Shibboleth認証',
        self::netcommons2 => 'NetCommons2認証',
    ];
}
