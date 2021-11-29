<?php

namespace App\Enums;

/**
 * 強さの選択肢
 */
final class Fineness extends EnumsBase
{
    // 定数メンバ（横px, 高さ:自動）
    const rough = 'rough';
    const medium = 'medium';
    const minute = 'minute';

    // key/valueの連想配列
    const enum = [
        self::rough => '粗',
        self::medium => '中',
        self::minute => '細',
    ];

    /**
     * 選択肢
     */
    public static function getListBoxItems()
    {
        $items = '[';
        foreach (self::enum as $key => $value) {
            $items .= "{ text: '{$value}', value: '{$key}' },";
        }
        $items .= ']';
        return $items;
    }

    /**
     * 初期値
     */
    public static function getDefault()
    {
        return self::medium;
    }


    /**
     * Wysiwyg 用の選択肢
     */
    public static function getWysiwygListBoxItems()
    {
        $items = '[';
        foreach (self::enum as $key => $value) {
            $items .= "{ text: '{$value}', value: '{$key}' },";
        }
        $items .= ']';
        return $items;
    }
}
