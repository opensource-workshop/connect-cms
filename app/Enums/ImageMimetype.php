<?php

namespace App\Enums;

/**
 * MIMEタイプ（画像）
 */
final class ImageMimetype extends EnumsBase
{
    // 定数メンバ
    const png = 'image/png';
    const jpeg = 'image/jpeg';
    const gif = 'image/gif';

    // key/valueの連想配列
    const enum = [
        self::png => 'image/png',
        self::jpeg => 'image/jpeg',
        self::gif => 'image/gif',
    ];
}
