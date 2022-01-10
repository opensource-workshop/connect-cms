<?php

namespace App\Enums;

/**
 * リンクリスト表示形式
 */
final class LinklistType extends EnumsBase
{
    // 定数メンバ
    const none = 0;
    const black_circle = 1;
    const white_circle = 2;
    const black_square = 3;
    const numeric = 4;
    const english_lowercase = 5;
    const english_uppercase = 6;
    const roman_number_lowercase = 7;
    const roman_number_uppercase = 8;

    // key/valueの連想配列
    const enum = [
        self::none => 'マークなし',
        self::black_circle => '黒丸',
        self::white_circle => '白丸',
        self::black_square => '黒四角',
        self::numeric => '1, 2, 3,...',
        self::english_lowercase => 'a, b, c,...',
        self::english_uppercase => 'A, B, C,...',
        self::roman_number_lowercase => 'ⅰ,ⅱ,ⅲ,...',
        self::roman_number_uppercase => 'Ⅰ,Ⅱ,Ⅲ,...',
    ];
}
