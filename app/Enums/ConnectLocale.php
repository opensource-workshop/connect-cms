<?php

namespace App\Enums;

/**
 * コネクト用ロケール
 */
final class ConnectLocale
{
    // 定数メンバ
    const ja = 'ja';
    const en = 'en';

    // key/valueの連想配列
    const enum = [
        self::ja=>'日本語',
        self::en=>'英語',
    ];

    /*
    * 対応した和名を返す
    */
    public static function getDescription($key): string
    {
        return self::enum[$key];
    }

    /*
    * key/valueの連想配列を返す
    */
    public static function getMembers()
    {
        return self::enum;
    }
}
