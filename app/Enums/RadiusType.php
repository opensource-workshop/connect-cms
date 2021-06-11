<?php

namespace App\Enums;

/**
 * ボタンの形（BootStrap4版のBorder-radiusの一部を使用）
 */
final class RadiusType
{
    // 定数メンバ
    const rounded = 'rounded';
    const circle = 'rounded-circle';
    const pill = 'rounded-pill';

    // key/valueの連想配列
    const enum = [
        self::rounded=>'四角形',
        self::circle=>'円形',
        self::pill=>'楕円形',
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
