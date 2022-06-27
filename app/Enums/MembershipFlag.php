<?php

namespace App\Enums;

use App\Enums\EnumsBase;

/**
 * メンバーシップフラグ
 */
final class MembershipFlag extends EnumsBase
{
    // 定数メンバ
    const public = 0;
    const membership = 1;
    const all_login_users = 2;

    // key/valueの連想配列
    const enum = [
        self::public => '公開',
        self::membership => 'メンバーシップページ',
        self::all_login_users => 'ログインユーザ全員参加',
    ];
}
