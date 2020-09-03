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

    /**
     * CSVインポート画面のセレクトボックスで使う、key/valueの連想配列を返す
     * セレクトボックスのみ 'UTF-8 BOM付'=>'UTF-8'に文言変更
     */
    public static function getSelectMembers()
    {
        $enum = static::enum;
        $enum[self::utf_8] = 'UTF-8';
        return $enum;
    }
}
