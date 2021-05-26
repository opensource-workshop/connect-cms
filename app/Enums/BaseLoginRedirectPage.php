<?php

namespace App\Enums;

use App\Enums\EnumsBase;

/**
 * ログイン後に移動するページ
 */
final class BaseLoginRedirectPage extends EnumsBase
{
    // 定数メンバ
    const top_page = 0;
    const previous_page = 1;
    const specified_page = 2;

    // key/valueの連想配列
    const enum = [
        self::top_page => 'トップページ',
        self::previous_page => '元いたページ',
        self::specified_page => '指定したページ'
    ];
}
