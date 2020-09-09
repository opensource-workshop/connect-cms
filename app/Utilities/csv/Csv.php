<?php

namespace App\Utilities\csv;

use Illuminate\Support\Facades\Log;

/**
 * Shift-JIS から UTF-8 変換するストリームフィルタ
 * CSV読み込み時に使用して、5C問題対応
 */
class Csv
{
    /**
     * UTF-8のBOMコードを追加する(UTF-8 BOM付きにするとExcelで文字化けしない)
     */
    public static function addUtf8Bom($csv_data)
    {
        //「UTF-8」の「BOM」であるコード「0xEF」「0xBB」「0xBF」をカンマ区切りにされた文字列の先頭に連結
        $csv_data = pack('C*', 0xEF, 0xBB, 0xBF) . $csv_data;
        return $csv_data;
    }

    /**
     * UTF-8のBOMコードを取り除く
     */
    public static function removeUtf8Bom($header_columns)
    {
        if (isset($header_columns[0])) {
            // UTF-8 BOMありなしに関わらず、先頭3バイトのBOMコードを置換して取り除く
            // BOMなしは、置換対象がないのでそのまま値が返る
            $header_columns[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header_columns[0]);
        }
        return $header_columns;
    }

    /**
     * 空文字をnullに変換
     * Laravel公式のリクエストを自動トリムする処理と同じ処理
     * copy by Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::transform()
     *
     * @param  mixed  $value
     * @return mixed
     */
    public static function convertEmptyStringsToNull($value)
    {
        return is_string($value) && $value === '' ? null : $value;
    }

    /**
     * 文字コードの自動検出(文字エンコーディングをsjis-win, UTF-8の順番で自動検出. 対象文字コード外の場合、false戻る)
     */
    public static function getCharacterCodeAuto($csv_full_path)
    {
        // 全体ではなく0～1024までを取得
        $contents = file_get_contents($csv_full_path, null, null, 0, 1024);

        // 文字エンコーディングをsjis-win, UTF-8の順番で自動検出. 対象文字コード外の場合、false戻る
        $character_code = mb_detect_encoding($contents, \CsvCharacterCode::sjis_win.", ".\CsvCharacterCode::utf_8);
        // Log::debug(var_export($character_code, true));

        return $character_code;
    }
}
