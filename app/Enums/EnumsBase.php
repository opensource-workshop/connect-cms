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

    /**
     * 対応した和名を返す
     */
    public static function getDescription($key): string
    {
        $enum = static::getEnum();
        return $enum[$key];
    }

    /**
     * key/valueの連想配列を返す
     */
    public static function getMembers()
    {
        return static::getEnum();
    }

    /**
     * key配列を返す
     */
    public static function getMemberKeys()
    {
        return array_keys(static::getEnum());
    }

    /**
     * enumを返す
     */
    private static function getEnum(): array
    {
        $class_name = self::getOptionClass();
        if ($class_name) {
            return $class_name::enum;
        }

        return static::enum;
    }

    /**
     * オプションクラスを返す
     */
    protected static function getOptionClass(): ?string
    {
        // クラス名をnamespace 毎取得
        $instance_name = explode('\\', get_called_class());
        if (is_array($instance_name) && $instance_name[0] == 'App' && $instance_name[1] == 'Enums' && !empty($instance_name[2])) {
            $class_name = "App\EnumsOption\\" . $instance_name[2] . "Option";
            // オプションあり
            if (class_exists($class_name)) {
                return $class_name;
            }
        }
        return null;
    }
}
