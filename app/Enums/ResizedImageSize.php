<?php

namespace App\Enums;

/**
 * リサイズ画像サイズ
 */
final class ResizedImageSize extends EnumsBase
{
    // 定数メンバ（横px, 高さ:自動）
    const asis = 'asis';
    const big = 1200;
    const middle = 800;
    const small = 400;
    const minimal = 200;

    // key/valueの連想配列
    const enum = [
        self::asis => '原寸(以下の幅、高さ)',
        self::big => '大(' . self::big . 'px)',
        self::middle => '中(' . self::middle . 'px)',
        self::small => '小(' . self::small . 'px)',
        self::minimal => '極小(' . self::minimal . 'px)',
    ];

    /**
     * Wysiwyg＞画像プラグイン＞画像サイズの選択肢
     */
    public static function getWysiwygListBoxItems($ommit_key = null)
    {
        $items = '[';
        foreach (self::enum as $key => $value) {
            if ($ommit_key == $key) {
                continue;
            }
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
        return self::asis;
    }

    /**
     * 画像アップロード時の変換メッセージ
     */
    public static function getImageUploadResizeMessage($size)
    {
        if ($size == self::asis) {
            return '原寸(変換しない)';
        }
        return self::enum[$size];
    }
}
