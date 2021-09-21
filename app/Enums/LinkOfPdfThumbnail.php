<?php

namespace App\Enums;

/**
 * PDFサムネイルのリンク
 */
final class LinkOfPdfThumbnail extends EnumsBase
{
    // 定数メンバ
    const pdf = 'pdf';
    const image = 'image';

    // key/valueの連想配列
    const enum = [
        self::pdf => 'PDFを開く',
        self::image => '画像を開く',
    ];

    /**
     * 初期値
     */
    public static function getDefault()
    {
        return self::pdf;
    }
}
