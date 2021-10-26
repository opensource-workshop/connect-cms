<?php

namespace App\Enums;

/**
 * コネクト用ロケール
 */
final class ConnectLocale extends EnumsBase
{
    // 定数メンバ
    const ja = 'ja';
    const en = 'en';

    // key/valueの連想配列
    const enum = [
        self::ja=>'日本語',
        self::en=>'英語',
    ];
}
