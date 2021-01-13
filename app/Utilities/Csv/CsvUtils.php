<?php

namespace App\Utilities\Csv;

use App\Utilities\Csv\SjisToUtf8EncodingFilter;

class CsvUtils
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
            // UTF-8 BOMありの場合、先頭にBOMコードが邪魔して、両端のダブルクォーテーションが fgetcsv() で外れないため、ここで外す
            $header_columns[0] = trim($header_columns[0], '"');
        }
        return $header_columns;
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
        // \Log::debug(var_export($character_code, true));

        return $character_code;
    }

    /**
     * ストリームフィルタ内で、Shift-JIS -> UTF-8変換
     */
    public static function setStreamFilterRegisterSjisToUtf8($fp)
    {
        // ストリームフィルタとして登録.
        // 5C問題対応：https://qiita.com/suin/items/3edfb9cb15e26bffba11
        // 5C問題 詳細：https://qiita.com/Kohei-Sato-1221/items/c050bb23436f35666165
        stream_filter_register(
            'sjis_to_utf8_encoding_filter',
            SjisToUtf8EncodingFilter::class
        );

        // ファイル読み込み時に使うストリームフィルタを指定.
        // ストリームフィルタ内で、Shift-JIS -> UTF-8変換してる。UTF-8変換で5C問題対応になる
        stream_filter_append($fp, 'sjis_to_utf8_encoding_filter');

        return $fp;
    }
}
