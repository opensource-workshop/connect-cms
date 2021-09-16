<?php

namespace App\Enums;

/**
 * PDFサムネイルの大きさ
 */
final class WidthOfPdfThumbnail extends EnumsBase
{
    // 定数メンバ value=HTMLのwidth値
    const big = 1200;       // 左・中央エリア表示時、中央エリアで横並び1枚
    const middle = 800;     // 〃  横並び1枚
    const small = 310;      // 〃  横並び2枚
    const minimal = 200;    // 〃  横並び3枚
    const thumbnail = 150;  // 〃  横並び4枚

    // key/valueの連想配列
    const enum = [
        self::big => '大',
        self::middle => '中',
        self::small => '小',
        self::minimal => '極小',
        self::thumbnail => 'サムネイル',
    ];

    /**
     * Wysiwyg＞PDFプラグイン＞サムネイルの大きさの選択肢
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

    /**
     * 作成するサムネイルのpx値（PDFの縦横いずれか長い方の大きさ）取得
     */
    public static function getScale($key)
    {
        $scales = [
            self::big => self::big,
            self::middle => self::middle,
            self::small => 400,
            self::minimal => self::minimal,
            self::thumbnail => 200,
        ];

        return $scales[$key];
    }
}
