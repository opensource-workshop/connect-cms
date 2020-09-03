<?php

namespace App\Enums;

use App\Enums\EnumsBase;

/**
 * CSVの文字コード種類
 */
final class CsvCharacterCode extends EnumsBase
{
    // 定数メンバ
    const auto = 'auto';
    const sjis_win = 'SJIS-win';
    const utf_8 = 'UTF-8';

    // key/valueの連想配列
    const enum = [
        self::auto => '自動検出',
        self::sjis_win => 'Shift-JIS',
        self::utf_8 => 'UTF-8 BOM付',
    ];
}
