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
    const cp932 = 'CP932';
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

    /**
     * セレクトボックスに対応した和名を返す
     */
    public static function getSelectMembersDescription($key): string
    {
        $enum = self::getSelectMembers();
        return $enum[$key];
    }

    /**
     * Shift-JISか
     */
    public static function isShiftJis($character_code): bool
    {
        if ($character_code == self::sjis_win || $character_code == self::cp932) {
            return true;
        }
        return false;
    }
}
