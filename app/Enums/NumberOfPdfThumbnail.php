<?php

namespace App\Enums;

/**
 * PDFのサムネイル数
 */
final class NumberOfPdfThumbnail extends EnumsBase
{
    // 定数メンバ
    const one = 1;
    const two = 2;
    const three = 3;
    const four = 4;
    const all = 'all';

    // key/valueの連想配列
    const enum = [
        self::one => '1',
        self::two => '2',
        self::three => '3',
        self::four => '4',
        self::all => '全て',
    ];

    /**
     * Wysiwyg＞PDFプラグイン＞サムネイル数の選択肢
     */
    public static function getWysiwygListBoxItems()
    {
        // [
        //     { text: '1', value: '1' },
        //     { text: '2', value: '2' },
        //     { text: '3', value: '3' },
        //     { text: '4', value: '4' },
        //     { text: '全て', value: 'all' }
        //   ]

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
        return self::four;
    }
}
