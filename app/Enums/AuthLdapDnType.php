<?php

namespace App\Enums;

use App\Enums\EnumsBase;

/**
 * LDAP認証DNタイプ
 */
final class AuthLdapDnType extends EnumsBase
{
    // 定数メンバ
    const dn = 'dn';
    const active_directory = 'active_directory';

    // key/valueの連想配列
    const enum = [
        self::dn => 'DN (<code>uid=ユーザID,DN</code>形式)',
        self::active_directory => 'Active Directory (<code>ユーザID@DN</code>形式)',
    ];
}
