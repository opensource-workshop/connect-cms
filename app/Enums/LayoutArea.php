<?php

namespace App\Enums;

use App\Enums\EnumsBase;

/**
 * レイアウトエリアID
 */
final class LayoutArea extends EnumsBase
{
    // 定数メンバ
    const header = 0;
    const left = 1;
    const main = 2;
    const right = 3;
    const footer = 4;

    // key/valueの連想配列
    const enum = [
        self::header => 'ヘッダーエリア',
        self::left => '左エリア',
        self::main => 'メインエリア',
        self::right => '右エリア',
        self::footer => 'フッターエリア',
    ];
}
