<?php

namespace App\Enums;

/**
 * Enums基底クラス
 *
 * 全てのEnumsの基底クラス
 *
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 基底クラス
 * @package Enums
 */
class EnumsBase
{
    // key/valueの連想配列
    const enum = [];

    /*
     * 対応した和名を返す
     */
    public static function getDescription($key): string
    {
        return static::enum[$key];
    }

    /*
     * key/valueの連想配列を返す
     */
    public static function getMembers()
    {
        return static::enum;
    }

    /**
     * key配列を返す
     */
    public static function getMemberKeys()
    {
        return array_keys(static::enum);
    }
}
