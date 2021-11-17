<?php

namespace App\Enums;

/**
 * 表示する・表示しない区分
 */
final class NotShowType extends EnumsBase
{
    // 定数メンバ
    const not_show = 1;
    const show = 0;

    // key/valueの連想配列
    const enum = [
        self::not_show => '表示しない',
        self::show => '表示する',
    ];

    /**
     * 対応した和名を返す
     */
    public static function getDescriptionNullSupport($key = null): string
    {
        $key = $key ?? self::getDefault();
        return static::enum[$key];
    }

    /**
     * 初期値
     */
    public static function getDefault()
    {
        return self::show;
    }
}
