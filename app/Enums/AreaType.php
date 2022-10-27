<?php

namespace App\Enums;

/**
 * フレームの表示エリア形式
 */
final class AreaType extends EnumsBase
{
    // 定数メンバ
    const header = 0;
    const left = 1;
    const main = 2;
    const right = 3;
    const footer = 4;

    // key/valueの連想配列
    const enum = [
        self::header => 'ヘッダー',
        self::left => '左',
        self::main => '中央',
        self::right => '右',
        self::footer => 'フッター',
    ];
}
